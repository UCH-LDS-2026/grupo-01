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

// Crear solicitud
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $id_paciente = $_POST['id_paciente'] ?? null;
    $id_medico = $_POST['id_medico'] ?? null;
    $id_practica = $_POST['id_practica'] ?? null;
    $fecha = $_POST['fecha'] ?? '';
    $prioridad = $_POST['prioridad'] ?? 'Media';
    $diagnostico = $_POST['diagnostico'] ?? '';

    // --- MAGIA DE ARCHIVOS ---
    $ruta_archivo = null; 
    
    // Verificamos si se subió un archivo y si no hubo errores
    if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
        
        $nombreOriginal = basename($_FILES['adjunto']['name']);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        // Generamos un nombre único: ej. receta_64f1b2c.pdf
        $nombreUnico = uniqid('receta_') . '.' . $extension;
        
        // Definimos la carpeta donde se van a guardar (fuera del controlador)
        $carpetaDestino = __DIR__ . '/../uploads/';
        
        // Si la carpeta "uploads" no existe, le decimos a PHP que la cree automáticamente
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        // Ruta final donde se moverá el archivo
        $rutaFinal = $carpetaDestino . $nombreUnico;

        // Movemos el archivo de la memoria temporal a nuestra carpeta
        if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $rutaFinal)) {
            // Si se movió con éxito, guardamos el nombre único para la base de datos
            $ruta_archivo = $nombreUnico;
        }
    }
    // Le pasamos la nueva variable $ruta_archivo a la función crear
    if ($solicitudModelo->crear($id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, $ruta_archivo)) {
        header("Location: ../vista/solicitudes.php");
        exit;
    } else {
        echo " Error al crear la solicitud.";
    }
}
?>
