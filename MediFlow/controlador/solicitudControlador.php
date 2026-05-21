<?php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);

// Verificamos si se envió un formulario por el método POST y si tiene una acción definida
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    
    // =======================================================
    // ACCIÓN 1: CREAR NUEVA SOLICITUD
    // =======================================================
    if ($_POST['accion'] == 'crear') {
        
        // 1. Capturamos y sanitizamos los datos que llegan del formulario horizontal
        $id_paciente = !empty($_POST['id_paciente']) ? intval($_POST['id_paciente']) : null;
        $id_medico   = !empty($_POST['id_medico']) ? intval($_POST['id_medico']) : null;
        $id_practica = !empty($_POST['id_practica']) ? intval($_POST['id_practica']) : null;
        $fecha       = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $diagnostico = trim($_POST['diagnostico'] ?? '');
        
        // Forzamos la prioridad a minúsculas (alta, media, baja) para que coincida con la Base de Datos
        $prioridad   = strtolower(trim($_POST['prioridad'] ?? 'media'));
        
        $ruta_archivo = null;

        // 2. Procesamiento seguro de archivo adjunto (Recetas, órdenes, imágenes)
        if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
            $nombreOriginal = basename($_FILES['adjunto']['name']);
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $nombreUnico = uniqid('receta_') . '.' . $extension;
            $carpetaDestino = __DIR__ . '/../uploads/';
            
            // Si la carpeta de subidas no existe, la crea con los permisos correctos
            if (!file_exists($carpetaDestino)) { 
                mkdir($carpetaDestino, 0777, true); 
            }
            
            if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $carpetaDestino . $nombreUnico)) {
                $ruta_archivo = $nombreUnico;
            }
        }

        // 3. Validación final: Si están todos los datos esenciales, mandamos a guardar
        if ($id_paciente && $id_medico && $id_practica && $diagnostico) {
            $solicitudModelo->crear($id_medico, $id_paciente, $id_practica, $fecha, $diagnostico, $prioridad, $ruta_archivo);
            header("Location: ../vista/solicitudes.php"); // Vuelve a la pantalla principal
            exit;
        } else {
            echo "❌ Error: Faltan completar campos obligatorios en el formulario de creación.";
            exit;
        }
    }

    // =======================================================
    // ACCIÓN 2: CORREGIR SOLICITUD REBOTADA (OBSERVADA)
    // =======================================================
    if ($_POST['accion'] == 'corregir') {
        
        // 1. Capturamos los mismos datos, pero se suma el ID de la solicitud a modificar
        $id_solicitud = !empty($_POST['id_solicitud']) ? intval($_POST['id_solicitud']) : null;
        $id_paciente  = !empty($_POST['id_paciente']) ? intval($_POST['id_paciente']) : null;
        $id_medico    = !empty($_POST['id_medico']) ? intval($_POST['id_medico']) : null;
        $id_practica  = !empty($_POST['id_practica']) ? intval($_POST['id_practica']) : null;
        $fecha        = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $diagnostico  = trim($_POST['diagnostico'] ?? '');
        $prioridad    = strtolower(trim($_POST['prioridad'] ?? 'media'));
        
        $ruta_archivo = null; 

        // 2. Procesamiento de nuevo archivo (por si el médico subió una receta más legible)
        if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
            $nombreOriginal = basename($_FILES['adjunto']['name']);
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $nombreUnico = uniqid('receta_corregida_') . '.' . $extension;
            $carpetaDestino = __DIR__ . '/../uploads/';
            
            if (!file_exists($carpetaDestino)) { 
                mkdir($carpetaDestino, 0777, true); 
            }
            
            if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $carpetaDestino . $nombreUnico)) {
                $ruta_archivo = $nombreUnico;
            }
        }

        // 3. Validación final y ejecución de la corrección
        if ($id_solicitud && $id_paciente && $id_medico && $id_practica && $diagnostico) {
            // Mandamos los datos al Modelo para que haga el UPDATE y vuelva el estado a 'pendiente'
            $solicitudModelo->corregir($id_solicitud, $id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, $ruta_archivo);
            header("Location: ../vista/notificaciones.php?exito=1"); // Devuelve al médico a su bandeja
            exit;
        } else {
            echo "❌ Error: Faltan completar datos esenciales para procesar la corrección.";
            exit;
        }
    }

} else {
    // Si alguien intenta entrar a este archivo por la URL directa (sin enviar un formulario)
    header("Location: ../index.php");
    exit;
}
?>