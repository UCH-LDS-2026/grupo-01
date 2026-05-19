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
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($usuario = $resultado->fetch_assoc()) {
            // Nota: acá respetamos el nombre de tu columna "contrasena"
            if ($usuario['contrasena'] == $password) {
                return $usuario;
            }
        }
        return false;
    }

    // --------------------------------------------------------
    // NUEVAS FUNCIONES (Para el ABM de la rama feature/usuarios)
    // --------------------------------------------------------
    
    public function crear($nombre, $apellido, $email, $password, $rol) {
        // Fijate que acá también usé "contrasena" para que coincida con tu BD
        $sql = "INSERT INTO usuario (nombre, apellido, email, contrasena, rol) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("sssss", $nombre, $apellido, $email, $password, $rol);
        return $stmt->execute();
    }

    public function listar() {
        // Traemos todos los usuarios menos las contraseñas (por seguridad)
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
    // NUEVA FUNCIÓN: Trae solo a los usuarios que tienen rol médico
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