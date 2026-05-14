<?php
if (class_exists('Venta')) return;

class Venta {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

        public function buscarProducto($termino) {
        $query = "SELECT v.id, p.nombre, v.stock, p.precio_venta, v.talla, v.color, p.codigo, c.nombre as categoria
                  FROM variantes v
                  JOIN productos p ON v.producto_id = p.id
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE (p.nombre LIKE :term OR v.talla LIKE :term OR v.color LIKE :term OR p.codigo LIKE :term) 
                  AND v.stock > 0
                  AND p.estado = 1
                  ORDER BY p.nombre ASC, v.talla ASC
                  LIMIT 50";
        $stmt = $this->conn->prepare($query);
        $term = "%$termino%";
        $stmt->bindParam(':term', $term);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NUEVA LÓGICA SUNAT ---
    public function registrarVenta($id_usuario, $tipo_comprobante, $cliente_tipo_doc, $cliente_num_doc, $cliente_nombre, $total_carrito, $carrito, $metodo_pago) {
        try {
            $this->conn->beginTransaction();

            // 1. Obtener caja activa
            $cajaQuery = "SELECT id FROM cajas WHERE usuario_id = :uid AND estado = 1 LIMIT 1";
            $stmtCaja  = $this->conn->prepare($cajaQuery);
            $stmtCaja->bindParam(':uid', $id_usuario);
            $stmtCaja->execute();
            $caja    = $stmtCaja->fetch(PDO::FETCH_ASSOC);
            $caja_id = $caja ? $caja['id'] : null;

            // 2. Cálculos matemáticos SUNAT (IGV 18%)
            $total_final = floatval($total_carrito);
            $op_gravadas = round($total_final / 1.18, 2);
            $igv_total   = round($total_final - $op_gravadas, 2);

            // 3. Obtener Serie y Correlativo
            $stmtSerie = $this->conn->prepare("SELECT serie, correlativo FROM series_comprobantes WHERE tipo_comprobante = :tc AND estado = 1 LIMIT 1");
            $stmtSerie->bindParam(':tc', $tipo_comprobante);
            $stmtSerie->execute();
            $serieData = $stmtSerie->fetch(PDO::FETCH_ASSOC);

            if (!$serieData) {
                throw new Exception("No hay serie configurada para el tipo de comprobante seleccionado.");
            }

            $serie = $serieData['serie'];
            $correlativo_num = $serieData['correlativo'];
            $correlativo_str = str_pad($correlativo_num, 6, "0", STR_PAD_LEFT); // Ej: 000001

            // 4. Insertar Cabecera de Venta
            $queryVenta = "INSERT INTO ventas 
                           (caja_id, usuario_id, tipo_comprobante, serie, correlativo, cliente_tipo_doc, cliente_num_doc, cliente_nombre, op_gravadas, igv, total, metodo_pago, estado, estado_sunat, fecha) 
                           VALUES 
                           (:caja_id, :usr, :tc, :ser, :corr, :ctd, :cnd, :cnom, :opg, :igv, :tot, :met, 1, 'REGISTRADO', datetime('now','localtime'))";
            
            $stmt = $this->conn->prepare($queryVenta);
            $stmt->bindParam(':caja_id',  $caja_id);
            $stmt->bindParam(':usr',      $id_usuario);
            $stmt->bindParam(':tc',       $tipo_comprobante);
            $stmt->bindParam(':ser',      $serie);
            $stmt->bindParam(':corr',     $correlativo_str);
            $stmt->bindParam(':ctd',      $cliente_tipo_doc);
            $stmt->bindParam(':cnd',      $cliente_num_doc);
            $stmt->bindParam(':cnom',     $cliente_nombre);
            $stmt->bindParam(':opg',      $op_gravadas);
            $stmt->bindParam(':igv',      $igv_total);
            $stmt->bindParam(':tot',      $total_final);
            $stmt->bindParam(':met',      $metodo_pago);
            $stmt->execute();
            
            $id_venta = $this->conn->lastInsertId();

            // 5. Insertar Detalles de Venta (Con IGV desglosado por item)
            foreach ($carrito as $item) {
                // Verificar stock disponible
                $stmtStock = $this->conn->prepare("SELECT stock, producto_id FROM variantes WHERE id = :vid");
                $stmtStock->bindParam(':vid', $item['id']);
                $stmtStock->execute();
                $variante = $stmtStock->fetch(PDO::FETCH_ASSOC);

                if (!$variante || $variante['stock'] < $item['cantidad']) {
                    $this->conn->rollBack();
                    return ['ok' => false, 'msg' => "Stock insuficiente para: {$item['nombre']}"];
                }

                $producto_id = $variante['producto_id'];
                
                // Cálculos por item
                $precio_unitario = floatval($item['precio']); 
                $valor_unitario  = round($precio_unitario / 1.18, 2); // Precio sin IGV
                $subtotal_item   = $precio_unitario * $item['cantidad'];
                $igv_item        = round($subtotal_item - ($valor_unitario * $item['cantidad']), 2);

                $queryDetalle = "INSERT INTO detalle_ventas 
                                 (venta_id, producto_id, variante_id, cantidad, valor_unitario, precio_unitario, igv, total) 
                                 VALUES (:vid, :prod_id, :var_id, :cant, :vu, :pu, :igv_item, :sub)";
                
                $stmtDet = $this->conn->prepare($queryDetalle);
                $stmtDet->bindParam(':vid',      $id_venta);
                $stmtDet->bindParam(':prod_id',  $producto_id);
                $stmtDet->bindParam(':var_id',   $item['id']);
                $stmtDet->bindParam(':cant',     $item['cantidad']);
                $stmtDet->bindParam(':vu',       $valor_unitario);
                $stmtDet->bindParam(':pu',       $precio_unitario);
                $stmtDet->bindParam(':igv_item', $igv_item);
                $stmtDet->bindParam(':sub',      $subtotal_item);
                $stmtDet->execute();

                // Descontar stock variante
                $stmtUpd = $this->conn->prepare("UPDATE variantes SET stock = stock - :cant WHERE id = :id");
                $stmtUpd->bindParam(':cant', $item['cantidad']);
                $stmtUpd->bindParam(':id',   $item['id']);
                $stmtUpd->execute();

                // Actualizar stock_total del producto
                $stmtProd = $this->conn->prepare("UPDATE productos SET stock_total = (SELECT COALESCE(SUM(stock),0) FROM variantes WHERE producto_id = :pid) WHERE id = :pid");
                $stmtProd->bindParam(':pid', $producto_id);
                $stmtProd->execute();
            }

            // 6. Actualizar Correlativo (+1)
            $stmtUpdSerie = $this->conn->prepare("UPDATE series_comprobantes SET correlativo = correlativo + 1 WHERE tipo_comprobante = :tc");
            $stmtUpdSerie->bindParam(':tc', $tipo_comprobante);
            $stmtUpdSerie->execute();

            $this->conn->commit();
            return ['ok' => true, 'id' => $id_venta];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    public function listarVentas($filtro = []) {
        $where = "1=1";
        $params = [];

        if (!empty($filtro['fecha_inicio'])) {
            $where .= " AND date(v.fecha) >= :f_ini";
            $params[':f_ini'] = $filtro['fecha_inicio'];
        }
        if (!empty($filtro['fecha_fin'])) {
            $where .= " AND date(v.fecha) <= :f_fin";
            $params[':f_fin'] = $filtro['fecha_fin'];
        }
        if (isset($filtro['estado']) && $filtro['estado'] !== '') {
            $where .= " AND v.estado = :est";
            $params[':est'] = $filtro['estado'];
        }

        $query = "SELECT v.*, u.nombre as vendedor 
                  FROM ventas v
                  JOIN usuarios u ON v.usuario_id = u.id_usuario
                  WHERE $where
                  ORDER BY v.fecha DESC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentaById($id) {
        $query = "SELECT v.*, u.nombre as vendedor 
                  FROM ventas v
                  JOIN usuarios u ON v.usuario_id = u.id_usuario
                  WHERE v.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDetalleVenta($id) {
        $query = "SELECT d.*, p.nombre, v.talla, v.color
                  FROM detalle_ventas d
                  JOIN variantes v ON d.variante_id = v.id
                  JOIN productos p ON v.producto_id = p.id
                  WHERE d.venta_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
    $stmt = $this->conn->prepare("SELECT * FROM ventas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function anularVenta($id_venta) {
        try {
            $this->conn->beginTransaction();
            $detalles = $this->getDetalleVenta($id_venta);
            foreach ($detalles as $item) {
                $stmt = $this->conn->prepare("UPDATE variantes SET stock = stock + :cant WHERE id = :vid");
                $stmt->bindParam(':cant', $item['cantidad']);
                $stmt->bindParam(':vid',  $item['variante_id']);
                $stmt->execute();

                // Recalcular stock_total
                $stmtProd = $this->conn->prepare("UPDATE productos SET stock_total = (SELECT COALESCE(SUM(stock),0) FROM variantes WHERE producto_id = :pid) WHERE id = :pid");
                $venta_item = $this->conn->prepare("SELECT producto_id FROM detalle_ventas WHERE id = :did");
                $venta_item->bindParam(':did', $item['id']);
                $venta_item->execute();
                $pid = $venta_item->fetch()['producto_id'] ?? null;
                if ($pid) { $stmtProd->bindParam(':pid', $pid); $stmtProd->execute(); }
            }
            // Cambiar estado a 0 y estado_sunat a ANULADO
            $stmt = $this->conn->prepare("UPDATE ventas SET estado = 0, estado_sunat = 'ANULADO' WHERE id = :id");
            $stmt->bindParam(':id', $id_venta);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
        public function listarProductosDisponibles($limite = 20) {
        $query = "SELECT v.id, p.nombre, v.stock, p.precio_venta, v.talla, v.color, p.codigo, c.nombre as categoria
                  FROM variantes v
                  JOIN productos p ON v.producto_id = p.id
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE v.stock > 0 AND p.estado = 1
                  ORDER BY p.nombre ASC, v.talla ASC
                  LIMIT :limite";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>