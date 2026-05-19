<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Usuario.php';

/** @var mysqli $conexion */
$usuarioModelo = new Usuario($conexion);

// Eliminar usuario
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id_usuario = $_GET['id'] ?? null;
    if ($id_usuario && $usuarioModelo->eliminar($id_usuario)) {
        header("Location: ../vista/usuarios.php");
        exit;
    } else {
        echo " Error al eliminar el usuario.";
    }
}

// Crear usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? ''; 
    $rol = $_POST['rol'] ?? '';

    if ($usuarioModelo->crear($nombre, $apellido, $email, $password, $rol)) {
        header("Location: ../vista/usuarios.php");
        exit;
    } else {
        echo " Error al crear el usuario. Es posible que el email ya exista.";
    }
}
?>