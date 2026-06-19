<?php
class Practica {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crear($nombre) {
        $sql = "INSERT INTO practica (nombre) VALUES (?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $nombre);
        return $stmt->execute();
    }

    public function listar() {
        $sql = "SELECT * FROM practica ORDER BY nombre ASC";
        $resultado = $this->conexion->query($sql);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function eliminar($id_practica) {
        $sql = "DELETE FROM practica WHERE id_practica = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_practica);
        return $stmt->execute();
    }
}
?>