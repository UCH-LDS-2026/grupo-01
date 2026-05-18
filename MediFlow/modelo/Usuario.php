<?php
class Usuario {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function iniciarSesion($email, $password) {
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($usuario = $resultado->fetch_assoc()) {
            if ($usuario['contrasena'] == $password) {
                return $usuario;
            }
        }
        return false;
    }
}
?>