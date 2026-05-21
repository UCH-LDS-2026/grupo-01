<?php
class Evaluacion {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crear($id_solicitud, $id_auditor, $estado_nuevo, $observaciones) {
        $sql = "INSERT INTO evaluacion (id_solicitud, id_auditor, estado_nuevo, observaciones) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("iiss", $id_solicitud, $id_auditor, $estado_nuevo, $observaciones);
        return $stmt->execute();
    }
}
?>