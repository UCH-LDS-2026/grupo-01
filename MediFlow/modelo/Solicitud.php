<?php
class Solicitud {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crear($id_medico, $id_paciente, $id_practica, $fecha, $diagnostico, $prioridad, $ruta_archivo = null) {
        $sql = "INSERT INTO solicitud (id_medico, id_paciente, id_practica, fecha, diagnostico, prioridad, ruta_archivo, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("iiissss", $id_medico, $id_paciente, $id_practica, $fecha, $diagnostico, $prioridad, $ruta_archivo);
        return $stmt->execute();
    }

    public function listar() {
        $sql = "SELECT s.*, p.nombre as nombre_paciente, p.apellido as apellido_paciente, p.dni, pr.nombre as nombre_practica, pr.descripcion as desc_practica 
                FROM solicitud s
                LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
                LEFT JOIN practica pr ON s.id_practica = pr.id_practica
                ORDER BY s.id_solicitud DESC";
        $resultado = $this->conexion->query($sql);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function actualizarEstado($id_solicitud, $nuevo_estado) {
        $nuevo_estado_limpio = strtolower(trim($nuevo_estado));
        $sql = "UPDATE solicitud SET estado = ? WHERE id_solicitud = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado_limpio, $id_solicitud);
        return $stmt->execute();
    }

    public function obtenerObservadasPorMedico($id_medico) {
        $sql = "SELECT s.*, p.nombre as nombre_paciente, p.apellido as apellido_paciente,
                       (SELECT observaciones FROM evaluacion e WHERE e.id_solicitud = s.id_solicitud ORDER BY e.id_evaluacion DESC LIMIT 1) as motivo_correccion
                FROM solicitud s
                JOIN paciente p ON s.id_paciente = p.id_paciente
                WHERE s.id_medico = ? AND LOWER(TRIM(s.estado)) = 'observada'";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_medico);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId($id_solicitud) {
        $sql = "SELECT * FROM solicitud WHERE id_solicitud = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_solicitud);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

   public function corregir($id_solicitud, $id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, $ruta_archivo = null) {
        if ($ruta_archivo) {
            $sql = "UPDATE solicitud SET id_paciente = ?, id_medico = ?, id_practica = ?, fecha = ?, prioridad = ?, diagnostico = ?, ruta_archivo = ?, estado = 'pendiente' WHERE id_solicitud = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("iiissssi", $id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, $ruta_archivo, $id_solicitud);
        } else {
            $sql = "UPDATE solicitud SET id_paciente = ?, id_medico = ?, id_practica = ?, fecha = ?, prioridad = ?, diagnostico = ?, estado = 'pendiente' WHERE id_solicitud = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("iiisssi", $id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, $id_solicitud);
        }
        return $stmt->execute();
    }

    // --- FUNCIONES RESTAURADAS QUE SE HABÍAN BORRADO ---
    
    public function obtenerEstadisticas() {
        $sql = "SELECT 
                  SUM(CASE WHEN LOWER(TRIM(estado)) IN ('aprobada', 'aceptada') THEN 1 ELSE 0 END) as aprobadas,
                  SUM(CASE WHEN LOWER(TRIM(estado)) = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                  SUM(CASE WHEN LOWER(TRIM(estado)) = 'pendiente' THEN 1 ELSE 0 END) as pendientes
                FROM solicitud";
        $resultado = $this->conexion->query($sql);
        return $resultado->fetch_assoc();
    }

    public function obtenerPendientesDashboard() {
        $sql = "SELECT s.*, p.nombre as nombre_paciente, p.apellido as apellido_paciente 
                FROM solicitud s
                LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
                WHERE LOWER(TRIM(s.estado)) = 'pendiente'
                ORDER BY FIELD(s.prioridad, 'alta', 'media', 'baja'), s.fecha ASC LIMIT 10";
        $resultado = $this->conexion->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    // --- NUEVAS FUNCIONES PARA EL PORTAL DEL PACIENTE ---

    public function listarPorEmailPaciente($email) {
        $sql = "SELECT s.*, p.nombre as nombre_paciente, p.apellido as apellido_paciente, p.dni, pr.nombre as nombre_practica, pr.descripcion as desc_practica 
                FROM solicitud s
                INNER JOIN paciente p ON s.id_paciente = p.id_paciente
                LEFT JOIN practica pr ON s.id_practica = pr.id_practica
                WHERE p.email = ?
                ORDER BY s.id_solicitud DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerEstadisticasPorPaciente($email) {
        $sql = "SELECT 
                  SUM(CASE WHEN LOWER(TRIM(s.estado)) IN ('aprobada', 'aceptada') THEN 1 ELSE 0 END) as aprobadas,
                  SUM(CASE WHEN LOWER(TRIM(s.estado)) = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                  SUM(CASE WHEN LOWER(TRIM(s.estado)) = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                  SUM(CASE WHEN LOWER(TRIM(s.estado)) = 'observada' THEN 1 ELSE 0 END) as observadas
                FROM solicitud s
                INNER JOIN paciente p ON s.id_paciente = p.id_paciente
                WHERE p.email = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerDatosPacientePorEmail($email) {
        $sql = "SELECT * FROM paciente WHERE email = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>