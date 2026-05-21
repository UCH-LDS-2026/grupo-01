<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../../src/config.php';
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
        header("Location: ../vista/login.php?error=1");
        exit;
    }
}
?>