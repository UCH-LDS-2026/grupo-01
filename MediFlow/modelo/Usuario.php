<?php
class Usuario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // --------------------------------------------------------
    // FUNCIÓN DE TU COMPAÑERO (Para que el Login siga andando)
    // --------------------------------------------------------
    public function iniciarSesion($email, $password) {
        $email = trim($email);
        if ($email === '' || $password === '') {
            return false;
        }

        $sql = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($usuario = $resultado->fetch_assoc()) {
            if (password_verify($password, $usuario['contrasena'])) {
                return $usuario;
            }
        }
        return false;
    }

    // --------------------------------------------------------
    // NUEVAS FUNCIONES ACTUALIZADAS
    // --------------------------------------------------------

    public function crear($nombre, $apellido, $email, $password, $rol, $dni, $extras = []) {
        $nombre = trim($nombre);
        $apellido = trim($apellido);
        $email = trim($email);
        $rol = trim($rol);
        $dni = trim($dni);

        if ($nombre === '' || $apellido === '' || $email === '' || $password === '' || $rol === '' || $dni === '') {
            return false;
        }

        if (!$this->validarEmail($email) || !$this->validarDNI($dni)) {
            return false;
        }

        if ($rol === 'paciente') {
            if (empty($extras['fecha_nacimiento']) || empty($extras['telefono']) || empty($extras['plan']) || empty($extras['nro_afiliado'])) {
                return false;
            }
            if (!$this->validarTelefono($extras['telefono'])) {
                return false;
            }
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $this->conexion->begin_transaction();

        try {
            // 1. Guardar en la tabla padre 'usuario' (Ahora incluye DNI y Activo)
            $sql = "INSERT INTO usuario (nombre, apellido, email, contrasena, rol, activo, dni) VALUES (?, ?, ?, ?, ?, 1, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ssssss", $nombre, $apellido, $email, $passwordHash, $rol, $dni);
            $stmt->execute();
            
            // Obtenemos el ID del usuario recién creado
            $id_usuario = $this->conexion->insert_id;

            // 2. Guardar en la tabla específica según el rol
            if ($rol == 'medico') {
                $sql_med = "INSERT INTO medico (id_usuario, matricula, especialidad) VALUES (?, ?, ?)";
                $stmt_med = $this->conexion->prepare($sql_med);
                $stmt_med->bind_param("iss", $id_usuario, $extras['matricula'], $extras['especialidad']);
                $stmt_med->execute();

            } elseif ($rol == 'auditor') {
                $sql_aud = "INSERT INTO auditor (id_usuario, sector) VALUES (?, ?)";
                $stmt_aud = $this->conexion->prepare($sql_aud);
                $stmt_aud->bind_param("is", $id_usuario, $extras['sector']);
                $stmt_aud->execute();

            } elseif ($rol == 'paciente') {
                $sql_pac = "INSERT INTO paciente (nombre, apellido, dni, fecha_nacimiento, email, telefono, plan, nro_afiliado, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt_pac = $this->conexion->prepare($sql_pac);
                $stmt_pac->bind_param("ssssssss", $nombre, $apellido, $dni, $extras['fecha_nacimiento'], $email, $extras['telefono'], $extras['plan'], $extras['nro_afiliado']);
                $stmt_pac->execute();
            }

            // Si todo salió bien, confirmamos los cambios
            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            // Si hubo algún error (ej. email o DNI duplicado), revertimos todo
            $this->conexion->rollback();
            return false;
        }
    }

    private function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validarDNI($dni) {
        return ctype_digit($dni) && $dni !== '';
    }

    private function validarTelefono($telefono) {
        return ctype_digit($telefono) && $telefono !== '';
    }

    public function listar() {
        $sql = "SELECT id_usuario, nombre, apellido, email, rol FROM usuario ORDER BY id_usuario DESC";
        $resultado = $this->conexion->query($sql);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function eliminar($id_usuario) {
        $sql = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        return $stmt->execute();
    }

    public function listarMedicos() {
        $sql = "SELECT id_usuario, nombre, apellido FROM usuario WHERE LOWER(TRIM(rol)) = 'medico' ORDER BY apellido ASC";
        $resultado = $this->conexion->query($sql);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }    
}
?>