<?php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Evaluacion.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$evaluacionModelo = new Evaluacion($conexion);
$solicitudModelo = new Solicitud($conexion);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_solicitud = $_POST['id_solicitud'] ?? null;
    // TOMAMOS EL ID DEL AUDITOR DIRECTO DE LA SESIÓN DE TU COMPAÑERO
    $id_auditor = $_SESSION['usuario']['id_usuario'] ?? null; 
    $estado_nuevo = $_POST['estado_nuevo'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    if ($id_solicitud && $id_auditor && $estado_nuevo) {
        // 1. Crear registro en evaluacion
        $evaluacionModelo->crear($id_solicitud, $id_auditor, $estado_nuevo, $observaciones);
        // 2. Actualizar estado en solicitud
        $solicitudModelo->actualizarEstado($id_solicitud, $estado_nuevo);
        
        header("Location: ../vista/solicitudes.php");
        exit;
    } else {
        echo "❌ Error: Faltan datos o tu sesión expiró.";
    }
}
?>