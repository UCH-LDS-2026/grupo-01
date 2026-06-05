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

        if ($id_paciente && $id_medico && $id_practica && $diagnostico) {
            // Creamos la solicitud y recuperamos su ID
            $id_solicitud = $solicitudModelo->crear($id_medico, $id_paciente, $id_practica, $fecha, $diagnostico, $prioridad, null);
            
            if ($id_solicitud) {
                // Procesamiento seguro a la tabla ARCHIVO
                if (isset($_FILES['adjuntos'])) {
                    $totalArchivos = count($_FILES['adjuntos']['name']);
                    $carpetaDestino = __DIR__ . '/../uploads/';
                    if (!file_exists($carpetaDestino)) { mkdir($carpetaDestino, 0777, true); }

                    for ($i = 0; $i < $totalArchivos; $i++) {
                        if ($_FILES['adjuntos']['error'][$i] === UPLOAD_ERR_OK) {
                            $nombreOriginal = basename($_FILES['adjuntos']['name'][$i]);
                            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                            $nombreUnico = uniqid('estudio_') . '_' . $i . '.' . $extension;
                            
                            if (move_uploaded_file($_FILES['adjuntos']['tmp_name'][$i], $carpetaDestino . $nombreUnico)) {
                                // Impacta directo en la DB
                                $solicitudModelo->guardarArchivo($id_solicitud, $nombreOriginal, $extension, $nombreUnico);
                            }
                        }
                    }
                }
            header("Location: ../index.php?exito=carga_ok");                exit;
            }
        } else {
            echo "❌ Error: Faltan completar campos obligatorios.";
            exit;
        }
    }

    // =======================================================
    // ACCIÓN 2: CORREGIR SOLICITUD REBOTADA
    // =======================================================
    if ($_POST['accion'] == 'corregir') {
        $id_solicitud = !empty($_POST['id_solicitud']) ? intval($_POST['id_solicitud']) : null;
        $id_paciente  = !empty($_POST['id_paciente']) ? intval($_POST['id_paciente']) : null;
        $id_medico    = !empty($_POST['id_medico']) ? intval($_POST['id_medico']) : null;
        $id_practica  = !empty($_POST['id_practica']) ? intval($_POST['id_practica']) : null;
        $fecha        = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $diagnostico  = trim($_POST['diagnostico'] ?? '');
        $prioridad    = strtolower(trim($_POST['prioridad'] ?? 'media'));
        
        if ($id_solicitud && $id_paciente && $id_medico && $id_practica && $diagnostico) {
            $solicitudModelo->corregir($id_solicitud, $id_paciente, $id_medico, $id_practica, $fecha, $prioridad, $diagnostico, null);
            
            // Subimos archivos nuevos si los hay
            if (isset($_FILES['adjuntos'])) {
                $totalArchivos = count($_FILES['adjuntos']['name']);
                $carpetaDestino = __DIR__ . '/../uploads/';
                if (!file_exists($carpetaDestino)) { mkdir($carpetaDestino, 0777, true); }

                for ($i = 0; $i < $totalArchivos; $i++) {
                    if ($_FILES['adjuntos']['error'][$i] === UPLOAD_ERR_OK) {
                        $nombreOriginal = basename($_FILES['adjuntos']['name'][$i]);
                        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                        $nombreUnico = uniqid('corregido_') . '_' . $i . '.' . $extension;
                        
                        if (move_uploaded_file($_FILES['adjuntos']['tmp_name'][$i], $carpetaDestino . $nombreUnico)) {
                            $solicitudModelo->guardarArchivo($id_solicitud, $nombreOriginal, $extension, $nombreUnico);
                        }
                    }
                }
            }
            header("Location: ../vista/notificaciones.php?exito=1"); 
            exit;
        } else {
            echo "❌ Error: Faltan completar datos esenciales para procesar la corrección.";
            exit;
        }
    }

    // =======================================================
    // ACCIÓN 3: AUDITOR EVALÚA SOLICITUD
    // =======================================================
    if ($_POST['accion'] == 'evaluar') {
        $id_solicitud = intval($_POST['id_solicitud']);
        $estado_nuevo = strtolower(trim($_POST['estado_nuevo']));
        $observaciones = trim($_POST['observaciones'] ?? '');
        
        // Buscar el id_auditor del usuario logueado
        $id_usuario = $_SESSION['usuario']['id_usuario'];
        $stmtAud = $conexion->prepare("SELECT id_auditor FROM auditor WHERE id_usuario = ?");
        $stmtAud->bind_param("i", $id_usuario);
        $stmtAud->execute();
        $resAud = $stmtAud->get_result()->fetch_assoc();
        
        if (!$resAud) {
            die("Error: Tu usuario no está registrado como auditor en la base de datos.");
        }
        $id_auditor = $resAud['id_auditor'];

        // Si la rechaza u observa, obligamos a que escriba una justificación
        if (in_array($estado_nuevo, ['rechazada', 'observada']) && empty($observaciones)) {
            die("❌ Error: Es obligatorio detallar el motivo al rechazar u observar una solicitud.");
        }

        if ($solicitudModelo->evaluar($id_solicitud, $id_auditor, $estado_nuevo, $observaciones)) {
            header("Location: ../vista/padron_solicitudes.php?evaluado=1");
            exit;
        } else {
            die("❌ Error al procesar la auditoría.");
        }
    }

} else {
    header("Location: ../index.php");
    exit;
}
?>