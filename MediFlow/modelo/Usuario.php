<?php
class Usuario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function iniciarSesion($email, $password) {
        $email = trim($email);
        
        $sql = "SELECT id_usuario, nombre, apellido, email, dni, contrasena, rol FROM usuario WHERE email = ? AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($usuario = $resultado->fetch_assoc()) {
            $hashAlmacenado = $usuario['contrasena'];

            if (password_verify($password, $hashAlmacenado) || $password === $hashAlmacenado) {
                return $usuario; 
            }
        }
        return false;
    }

    public function crear($nombre, $apellido, $email, $password, $rol, $dni = '', $extras = []) {
        
        if (is_array($dni)) {
            $extras = $dni;
            $dni = '12345678'; 
        }

        $nombre = trim($nombre);
        $apellido = trim($apellido);
        $email = trim($email);
        $rol = trim($rol);
        $dni = trim($dni);

        if ($nombre === '' || $apellido === '' || $email === '' || $password === '' || $rol === '' || $dni === '') {
            return false;
        }

        if (!$this->validarEmail($email) || (!$this->validarDNI($dni) && $dni !== '12345678')) {
            return false;
        }

        if ($rol === 'paciente') {
            if (empty($extras['fecha_nacimiento']) || empty($extras['telefono']) || empty($extras['plan'])) {
                return false;
            }
            if (!$this->validarTelefono($extras['telefono'])) {
                return false;
            }
        }
//ESTA LINEA ES LA QUE HACE EL HASH DE LA CONTRASEÑA, SI NO SE HACE EL HASH, NO SE PUEDE INICIAR SESION//
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $this->conexion->begin_transaction();
//variable paswordHash es la que se utiliza para almacenar la contraseña encriptada en la base de datos, //
//mientras que la variable password contiene la contraseña original proporcionada por el usuario//
//y la envia a la bd usando $stmt->bind_param(..lin67..)
        try {
            $sql = "INSERT INTO usuario (nombre, apellido, email, contrasena, rol, activo, dni) VALUES (?, ?, ?, ?, ?, 1, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ssssss", $nombre, $apellido, $email, $passwordHash, $rol, $dni);
            $stmt->execute();
            
            $id_usuario = $this->conexion->insert_id;

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
                $query_max = "SELECT MAX(id_paciente) as max_id FROM paciente";
                $res_max = $this->conexion->query($query_max);
                $row_max = $res_max->fetch_assoc();
                $next_id = ($row_max['max_id'] ?? 0) + 1;
                
                $nro_afiliado_generado = 'F-' . str_pad($next_id, 5, '0', STR_PAD_LEFT) . '-01';

                $sql_pac = "INSERT INTO paciente (nombre, apellido, dni, fecha_nacimiento, email, telefono, plan, nro_afiliado, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt_pac = $this->conexion->prepare($sql_pac);
                $stmt_pac->bind_param("ssssssss", $nombre, $apellido, $dni, $extras['fecha_nacimiento'], $email, $extras['telefono'], $extras['plan'], $nro_afiliado_generado);
                $stmt_pac->execute();
            }

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
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
        $sql = "SELECT id_usuario, nombre, apellido, email, dni, rol, activo, fecha_alta, fecha_baja FROM usuario ORDER BY id_usuario DESC";
        $resultado = $this->conexion->query($sql);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function eliminar($id_usuario) {
        $sql = "UPDATE usuario SET activo = 0, fecha_baja = NOW() WHERE id_usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        return $stmt->execute();
    }

    public function listarMedicos() {
        $sql = "SELECT id_usuario, nombre, apellido FROM usuario WHERE LOWER(TRIM(rol)) = 'medico' AND activo = 1 ORDER BY apellido ASC";
        $resultado = $this->conexion->query($sql);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }    
}
?>