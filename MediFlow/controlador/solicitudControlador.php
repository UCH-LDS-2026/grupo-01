<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Solicitud.php';

/** @var mysqli $conexion */
$solicitudModelo = new Solicitud($conexion);

// REQUERIMIENTO 2: El Auditor utiliza la solicitud (Viene por GET)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'auditar') {
    $id_solicitud = $_GET['id'] ?? null;
    $estado = $_GET['estado'] ?? null;

    if ($id_solicitud && $estado) {
        if ($solicitudModelo->actualizarEstado($id_solicitud, $estado)) {
            header("Location: ../vista/solicitudes.php");
            exit;
        } else {
            echo " Error al procesar la auditoría de la solicitud.";
        }
    }
}

// REQUERIMIENTO 1: Crear la solicitud (Viene por POST del formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_paciente = $_POST['id_paciente'] ?? null;
    $id_medico = $_POST['id_medico'] ?? null;
    $id_practica = $_POST['id_practica'] ?? null;
    $fecha = $_POST['fecha'] ?? '';
    $prioridad = $_POST['prioridad'] ?? 'Normal';
    $diagnostico = $_POST['diagnostico'] ?? '';

    if (isset($_POST['accion']) && $_POST['accion'] == 'crear') {
        if ($solicitudModelo->crear($id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico)) {
            header("Location: ../vista/solicitudes.php");
            exit;
        } else {
            echo " Error al crear la solicitud médica.";
        }
    }
}
?>