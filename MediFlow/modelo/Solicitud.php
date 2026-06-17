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
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id; // Devolvemos el ID generado para vincular los archivos
        }
        return false;
    }

    // NUEVO: Guarda en la tabla 'archivo'
    public function guardarArchivo($id_solicitud, $nombre, $tipo, $ruta) {
        $sql = "INSERT INTO archivo (id_solicitud, nombre, tipo, ruta) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("isss", $id_solicitud, $nombre, $tipo, $ruta);
        return $stmt->execute();
    }

    // NUEVO: Recupera de la tabla 'archivo'
    public function obtenerArchivos($id_solicitud) {
        $sql = "SELECT * FROM archivo WHERE id_solicitud = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_solicitud);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // NUEVO: Auditor evalúa la solicitud
    public function evaluar($id_solicitud, $id_auditor, $estado_nuevo, $observaciones) {
        $this->conexion->begin_transaction();
        try {
            // 1. Actualiza el estado principal
            $sql1 = "UPDATE solicitud SET estado = ? WHERE id_solicitud = ?";
            $stmt1 = $this->conexion->prepare($sql1);
            $stmt1->bind_param("si", $estado_nuevo, $id_solicitud);
            $stmt1->execute();

            // 2. Guarda el registro en la tabla evaluacion
            $sql2 = "INSERT INTO evaluacion (id_solicitud, id_auditor, estado_nuevo, observaciones) VALUES (?, ?, ?, ?)";
            $stmt2 = $this->conexion->prepare($sql2);
            $stmt2->bind_param("iiss", $id_solicitud, $id_auditor, $estado_nuevo, $observaciones);
            $stmt2->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            return false;
        }
    }

    public function listar() {
        $sql = "SELECT s.*, 
                       p.nombre as nombre_paciente, p.apellido as apellido_paciente, p.dni, p.nro_afiliado, p.plan,
                       pr.nombre as nombre_practica, pr.descripcion as desc_practica,
                       u_medico.nombre as nombre_medico, u_medico.apellido as apellido_medico, 
                       m.especialidad as especialidad_medico, m.matricula as matricula_medico,
                       (SELECT observaciones FROM evaluacion e WHERE e.id_solicitud = s.id_solicitud ORDER BY e.id_evaluacion DESC LIMIT 1) as motivo_correccion
                FROM solicitud s
                LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
                LEFT JOIN practica pr ON s.id_practica = pr.id_practica
                LEFT JOIN usuario u_medico ON s.id_medico = u_medico.id_usuario 
                LEFT JOIN medico m ON u_medico.id_usuario = m.id_usuario
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
    
    public function obtenerEstadisticas() {
        $sql = "SELECT 
                  SUM(CASE WHEN LOWER(TRIM(estado)) IN ('aprobada', 'aceptada') THEN 1 ELSE 0 END) as aprobadas,
                  SUM(CASE WHEN LOWER(TRIM(estado)) = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                  SUM(CASE WHEN LOWER(TRIM(estado)) = 'pendiente' THEN 1 ELSE 0 END) as pendientes
                FROM solicitud";
        $resultado = $this->conexion->query($sql);
        return $resultado->fetch_assoc();
    }

   public function obtenerPendientesFiltradas($filtros = []) {
        $sql = "SELECT s.*, p.nombre as nombre_paciente, p.apellido as apellido_paciente, p.dni,
                       u_medico.nombre as nombre_medico, u_medico.apellido as apellido_medico,
                       pr.nombre as nombre_practica,
                       (SELECT observaciones FROM evaluacion e WHERE e.id_solicitud = s.id_solicitud ORDER BY e.id_evaluacion DESC LIMIT 1) as motivo_correccion
                FROM solicitud s
                LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
                LEFT JOIN usuario u_medico ON s.id_medico = u_medico.id_usuario
                LEFT JOIN practica pr ON s.id_practica = pr.id_practica
                WHERE 1=1 ";
        
        $params = [];
        $types = "";

        if (!empty($filtros['estado'])) {
            $sql .= " AND LOWER(TRIM(s.estado)) = ? ";
            $types .= "s";
            $params[] = strtolower(trim($filtros['estado']));
        }
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(s.fecha) >= ? ";
            $types .= "s";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(s.fecha) <= ? ";
            $types .= "s";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['id_practica'])) {
            $sql .= " AND s.id_practica = ? ";
            $types .= "i";
            $params[] = $filtros['id_practica'];
        }
        if (!empty($filtros['paciente'])) {
            $sql .= " AND (p.nombre LIKE ? OR p.apellido LIKE ? OR p.dni LIKE ?) ";
            $types .= "sss";
            $like = "%" . $filtros['paciente'] . "%";
            $params[] = $like; $params[] = $like; $params[] = $like;
        }

        $sql .= " ORDER BY FIELD(s.prioridad, 'alta', 'media', 'baja'), s.fecha ASC LIMIT 100";

        $stmt = $this->conexion->prepare($sql);
        if (!empty($params)) { $stmt->bind_param($types, ...$params); }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function contarPendientes() {
        $sql = "SELECT COUNT(*) as total FROM solicitud WHERE estado = 'pendiente'";
        $resultado = $this->conexion->query($sql);
        return $resultado->fetch_assoc()['total'] ?? 0;
    }

    // --- FUNCIONES PARA EL PORTAL DEL PACIENTE ---
    public function listarPorEmailPaciente($email) {
        $sql = "SELECT s.*, 
                       p.nombre as nombre_paciente, p.apellido as apellido_paciente, p.dni, p.nro_afiliado, p.plan,
                       pr.nombre as nombre_practica, pr.descripcion as desc_practica,
                       u_medico.nombre as nombre_medico, u_medico.apellido as apellido_medico, 
                       m.especialidad as especialidad_medico, m.matricula as matricula_medico,
                       (SELECT observaciones FROM evaluacion e WHERE e.id_solicitud = s.id_solicitud ORDER BY e.id_evaluacion DESC LIMIT 1) as motivo_correccion
                FROM solicitud s
                INNER JOIN paciente p ON s.id_paciente = p.id_paciente
                LEFT JOIN practica pr ON s.id_practica = pr.id_practica
                LEFT JOIN usuario u_medico ON s.id_medico = u_medico.id_usuario
                LEFT JOIN medico m ON u_medico.id_usuario = m.id_usuario
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