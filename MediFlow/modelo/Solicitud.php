<?php
class Solicitud {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // REQUERIMIENTO 1: Crear la solicitud
   public function crear($id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, $ruta_archivo = null) {
        // Fijate que agregamos ruta_archivo al final de la consulta SQL
        $sql = "INSERT INTO solicitud (id_paciente, id_medico, id_practica, fecha, prioridad, diagnostico, estado, ruta_archivo) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?)";
        
        $stmt = $this->conexion->prepare($sql);
        // Agregamos una "s" más en el bind_param para el archivo
        $stmt->bind_param("iiissss", $id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, $ruta_archivo);
        
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
    public function obtenerEstadisticas() {
        $sql = "SELECT 
                  SUM(CASE WHEN LOWER(estado) IN ('aprobada', 'aceptada') THEN 1 ELSE 0 END) as aprobadas,
                  SUM(CASE WHEN LOWER(estado) = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                  SUM(CASE WHEN LOWER(estado) = 'pendiente' THEN 1 ELSE 0 END) as pendientes
                FROM solicitud";
        $resultado = $this->conexion->query($sql);
        return $resultado->fetch_assoc();
    }

    public function obtenerPendientesDashboard() {
        $sql = "SELECT s.*, p.nombre as nombre_paciente, p.apellido as apellido_paciente 
                FROM solicitud s
                LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
                WHERE LOWER(s.estado) = 'pendiente'
                ORDER BY FIELD(s.prioridad, 'Alta', 'Media', 'Baja'), s.fecha ASC LIMIT 10";
        $resultado = $this->conexion->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>