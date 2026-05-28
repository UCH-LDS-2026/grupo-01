<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$rol = strtolower($_SESSION['usuario']['rol'] ?? '');

if ($_POST['accion'] == 'crear' && !in_array($rol, ['admin','administrador'])) {
    die("Sin permisos");
}

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Paciente.php';

/** @var mysqli $conexion */
$pacienteModelo = new Paciente($conexion);

// =================================================================
// 1. PETICIONES GET (Ejemplo: Hacer clic en un enlace como Eliminar)
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    // Ejecuta la función del modelo y, si sale bien, vuelve a la vista
    if ($pacienteModelo->eliminar($id)) {
        header("Location: ../vista/pacientes.php");
        exit;
    } else {
        echo "❌ Error al intentar dar de baja al afiliado.";
    }
}

// =================================================================
// 2. PETICIONES POST (Ejemplo: Enviar un formulario para Crear/Editar)
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $dni = $_POST['dni'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $plan = $_POST['plan'] ?? '';
    $nro_afiliado = $_POST['nro_afiliado'] ?? '';

    // ACCIÓN: CREAR
    if (isset($_POST['accion']) && $_POST['accion'] == 'crear') {
        if ($pacienteModelo->crear($nombre, $apellido, $dni, $fecha_nacimiento, $email, $telefono, $plan, $nro_afiliado)) {
            header("Location: ../vista/pacientes.php");
            exit;
        } else {
            echo " Error al registrar en la Base de Datos.";
        }
    }
    
    // ACCIÓN: EDITAR
    if (isset($_POST['accion']) && $_POST['accion'] == 'editar') {
        $id = $_POST['id'] ?? null;
        if ($id && $pacienteModelo->editar($id, $nombre, $apellido, $dni, $fecha_nacimiento, $email, $telefono, $plan, $nro_afiliado)) {
            header("Location: ../vista/pacientes.php");
            exit;
        } else {
            echo "te Error al actualizar los datos.";
        }
    }
}
?>