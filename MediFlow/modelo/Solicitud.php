<?php
class Solicitud {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // REQUERIMIENTO 1: Crear la solicitud
    public function crear($id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico) {
        $sql = "INSERT INTO solicitud (id_paciente, id_medico, id_practica, fecha, estado, prioridad, diagnostico) 
                VALUES (?, ?, ?, ?, 'Pendiente', ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("iiisss", $id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico);
        return $stmt->execute();
    }

    // REQUERIMIENTO 3: Ver solicitud (Listar para todos los roles)
    public function listar() {
        $sql = "SELECT s.*, p.nombre AS nombre_paciente, p.apellido AS apellido_paciente, p.dni 
                FROM solicitud s
                LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
                ORDER BY s.id_solicitud DESC";
        $resultado = $this->conexion->query($sql);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // REQUERIMIENTO 2: Utilizar solicitud (El Auditor cambia el estado)
    public function actualizarEstado($id_solicitud, $nuevo_estado) {
        $sql = "UPDATE solicitud SET estado = ? WHERE id_solicitud = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $id_solicitud);
        return $stmt->execute();
    }
}
?>