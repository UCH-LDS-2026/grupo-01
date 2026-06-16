<?php
class Plan {
    private $conexion;
    public function __construct($conexion) { $this->conexion = $conexion; }

    public function listar() {
        // Aseguramos que el nombre de la tabla sea 'planes'
        $res = $this->conexion->query("SELECT * FROM planes ORDER BY nombre ASC");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function crear($nombre) {
        // Como el ID es autoincremental, solo insertamos el nombre
        $stmt = $this->conexion->prepare("INSERT INTO planes (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->conexion->prepare("DELETE FROM planes WHERE id_plan = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}