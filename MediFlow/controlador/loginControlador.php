<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// CAMBIA ESTA LÍNEA: Subimos dos niveles para ir a la carpeta 'src' externa
require_once __DIR__ . '/../../src/config.php';
// Esta queda igual porque el modelo sí está dentro de MediFlow
require_once __DIR__ . '/../modelo/Usuario.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $usuarioModelo = new Usuario($conexion);
    $usuario = $usuarioModelo->iniciarSesion($email, $password);

    if ($usuario) {
        $_SESSION['usuario'] = $usuario;

        header("Location: /MediFlow/grupo-01/MediFlow/index.php");
        exit;

    } else {
        echo "❌ Usuario o contraseña incorrectos";
    }
}
?>