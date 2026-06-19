<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Usuario.php';

/** @var mysqli $conexion */
$usuarioModelo = new Usuario($conexion);

// Dar de baja (Soft Delete)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id_usuario = $_GET['id'] ?? null;
    
    if ($id_usuario) {
        if ($usuarioModelo->eliminar($id_usuario)) {
            header("Location: ../vista/usuarios.php");
            exit;
        } else {
            echo "Error al dar de baja el usuario.";
        }
    }
}

// Crear usuario y guarda en la bd post captura la info ingresada en los campos del formulario de la pagina y /
//null ??'' es para que si un campo llega vacio o no se envia se inicialice un texto vacio en lugar de que se rompa el script con el warning/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? ''; 
    $rol = $_POST['rol'] ?? '';
    $dni = $_POST['dni'] ?? ''; 
//como la bd tiene los campos separados o especificos por cada usuario el controlador discrimina el rol selecccionado /
// y toma estos campos dinamicos dentro de $extras para dejar limpio los parametros/
    if ($rol == 'medico') {
        $extras['matricula'] = $_POST['matricula'] ?? '';
        $extras['especialidad'] = $_POST['especialidad'] ?? '';
    } elseif ($rol == 'auditor') {
        $extras['sector'] = $_POST['sector'] ?? '';
    } elseif ($rol == 'paciente') {
        $extras['fecha_nacimiento'] = $_POST['fecha_nacimiento'] ?? '';
        $extras['telefono'] = $_POST['telefono'] ?? '';
        $extras['plan'] = $_POST['plan'] ?? '';
    }

    if ($usuarioModelo->crear($nombre, $apellido, $email, $password, $rol, $dni, $extras)) {
        header("Location: ../vista/usuarios.php");
        exit;
    } else {
        echo "Error al crear el usuario. Verifique que el correo, DNI, o Matrícula no estén duplicados.";
    }
}
?>