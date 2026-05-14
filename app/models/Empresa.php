<?php
if (class_exists('Empresa')) return;

class Empresa {
    private $conn;
    private $table = "configuracion_empresa";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getConfig() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data) {
        $existe = $this->getConfig();
        
        if ($existe) {
            $sql = "UPDATE " . $this->table . " SET 
                    ruc = :ruc,
                    razon_social = :razon_social,
                    direccion = :direccion,
                    telefono = :telefono,
                    email = :email,
                    usuario_sol = :usuario_sol,
                    clave_sol = :clave_sol
                    WHERE id = 1";
        } else {
            $sql = "INSERT INTO " . $this->table . " 
                    (ruc, razon_social, direccion, telefono, email, usuario_sol, clave_sol) 
                    VALUES 
                    (:ruc, :razon_social, :direccion, :telefono, :email, :usuario_sol, :clave_sol)";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':ruc', $data['ruc'] ?? '');
        $stmt->bindValue(':razon_social', $data['razon_social'] ?? '');
        $stmt->bindValue(':direccion', $data['direccion'] ?? '');
        $stmt->bindValue(':telefono', $data['telefono'] ?? '');
        $stmt->bindValue(':email', $data['email'] ?? '');
        $stmt->bindValue(':usuario_sol', $data['sol_usuario'] ?? '');
        $stmt->bindValue(':clave_sol', $data['sol_clave'] ?? '');
        
        return $stmt->execute();
    }

    public function guardarCertificado($path) {
        $sql = "UPDATE " . $this->table . " SET certificado_path = :path WHERE id = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':path', $path);
        return $stmt->execute();
    }
}
?>