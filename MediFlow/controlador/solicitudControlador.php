<?php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    
    // =======================================================
    // ACCIÓN 1: CREAR NUEVA SOLICITUD
    // =======================================================
    if ($_POST['accion'] == 'crear') {
        
        $id_paciente = !empty($_POST['id_paciente']) ? intval($_POST['id_paciente']) : null;
        $id_medico   = !empty($_POST['id_medico']) ? intval($_POST['id_medico']) : null;
        $id_practica = !empty($_POST['id_practica']) ? intval($_POST['id_practica']) : null;
        $fecha       = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $diagnostico = trim($_POST['diagnostico'] ?? '');
        $prioridad   = strtolower(trim($_POST['prioridad'] ?? 'media'));
        
        $rutas_archivos = []; // Array para guardar múltiples nombres

        // Procesamiento seguro de MULTIPLES archivos adjuntos
        if (isset($_FILES['adjuntos'])) {
            $totalArchivos = count($_FILES['adjuntos']['name']);
            $carpetaDestino = __DIR__ . '/../uploads/';
            
            if (!file_exists($carpetaDestino)) { 
                mkdir($carpetaDestino, 0777, true); 
            }

            for ($i = 0; $i < $totalArchivos; $i++) {
                if ($_FILES['adjuntos']['error'][$i] === UPLOAD_ERR_OK) {
                    $nombreOriginal = basename($_FILES['adjuntos']['name'][$i]);
                    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                    $nombreUnico = uniqid('receta_') . '_' . $i . '.' . $extension;
                    
                    if (move_uploaded_file($_FILES['adjuntos']['tmp_name'][$i], $carpetaDestino . $nombreUnico)) {
                        $rutas_archivos[] = $nombreUnico;
                    }
                }
            }
        }

        // Unimos todos los nombres con una coma, o dejamos null si no hay ninguno
        $ruta_archivo_final = !empty($rutas_archivos) ? implode(',', $rutas_archivos) : null;

        if ($id_paciente && $id_medico && $id_practica && $diagnostico) {
            $solicitudModelo->crear($id_medico, $id_paciente, $id_practica, $fecha, $diagnostico, $prioridad, $ruta_archivo_final);
            header("Location: ../vista/solicitudes.php"); 
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
        
        $id_solicitud = !empty($_POST['id_solicitud']) ? intval($_POST['id_solicitud']) : null;
        $id_paciente  = !empty($_POST['id_paciente']) ? intval($_POST['id_paciente']) : null;
        $id_medico    = !empty($_POST['id_medico']) ? intval($_POST['id_medico']) : null;
        $id_practica  = !empty($_POST['id_practica']) ? intval($_POST['id_practica']) : null;
        $fecha        = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $diagnostico  = trim($_POST['diagnostico'] ?? '');
        $prioridad    = strtolower(trim($_POST['prioridad'] ?? 'media'));
        
        $rutas_archivos = []; 

        if (isset($_FILES['adjuntos'])) {
            $totalArchivos = count($_FILES['adjuntos']['name']);
            $carpetaDestino = __DIR__ . '/../uploads/';
            
            if (!file_exists($carpetaDestino)) { 
                mkdir($carpetaDestino, 0777, true); 
            }

            for ($i = 0; $i < $totalArchivos; $i++) {
                if ($_FILES['adjuntos']['error'][$i] === UPLOAD_ERR_OK) {
                    $nombreOriginal = basename($_FILES['adjuntos']['name'][$i]);
                    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                    $nombreUnico = uniqid('receta_corregida_') . '_' . $i . '.' . $extension;
                    
                    if (move_uploaded_file($_FILES['adjuntos']['tmp_name'][$i], $carpetaDestino . $nombreUnico)) {
                        $rutas_archivos[] = $nombreUnico;
                    }
                }
            }
        }

        $ruta_archivo_final = !empty($rutas_archivos) ? implode(',', $rutas_archivos) : null;

        if ($id_solicitud && $id_paciente && $id_medico && $id_practica && $diagnostico) {
            $solicitudModelo->corregir($id_solicitud, $id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, $ruta_archivo_final);
            header("Location: ../vista/notificaciones.php?exito=1"); 
            exit;
        } else {
            echo "❌ Error: Faltan completar datos esenciales para procesar la corrección.";
            exit;
        }
    }

} else {
    header("Location: ../index.php");
    exit;
}
?>