<?php
session_start();
$rolActual = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));
if (!$rolActual) { header("Location: login.php"); exit; }

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$id_solicitud = $_GET['id'] ?? null;
if (!$id_solicitud) { die("ID de solicitud no proporcionado."); }


$solicitudModelo = new Solicitud($conexion);
$solicitudes = $solicitudModelo->listar();

$datosSolicitud = null;
foreach($solicitudes as $s) {
    if($s['id_solicitud'] == $id_solicitud) {
        $datosSolicitud = $s;
        break;
    }
}

if (!$datosSolicitud) { die("La solicitud solicitada no existe o fue eliminada."); }

$estadoNormalizado = strtolower(trim($datosSolicitud['estado']));
$claseBadge = 'badge-pendiente';
if($estadoNormalizado == 'observada') $claseBadge = 'badge-observada';
if($estadoNormalizado == 'aprobada') $claseBadge = 'badge-aprobada';
if($estadoNormalizado == 'rechazada') $claseBadge = 'badge-rechazada';

// Recuperamos de la tabla de archivos reales
$archivosAdjuntos = $solicitudModelo->obtenerArchivos($id_solicitud);

// Si es médico y está observada, traemos las prácticas y el motivo de rechazo para armar el form de corrección
$practicas = [];
$motivoCorreccion = '';
if ($rolActual == 'medico' && $estadoNormalizado == 'observada') {
    $practicas = $conexion->query("SELECT * FROM practica")->fetch_all(MYSQLI_ASSOC);
    
    $stmtEval = $conexion->prepare("SELECT observaciones FROM evaluacion WHERE id_solicitud = ? ORDER BY id_evaluacion DESC LIMIT 1");
    $stmtEval->bind_param("i", $id_solicitud);
    $stmtEval->execute();
    $motivoCorreccion = $stmtEval->get_result()->fetch_assoc()['observaciones'] ?? 'Sin motivo especificado por auditoría.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Solicitud #<?php echo $datosSolicitud['id_solicitud']; ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        
        body { background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .container { max-width: 900px; margin: 40px auto; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 20px;}
        
        .card-header { background: #ffffff; border-bottom: 2px solid #e0f2fe; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { margin: 0; font-size: 22px; font-weight: 600; color: #0c4a6e; display: flex; align-items: center; gap: 10px; }
        .id-badge { background: #0ea5e9; color: white; padding: 4px 12px; border-radius: 6px; font-size: 18px; font-weight: bold; }
        
        .badge { text-transform: capitalize; font-weight: 600; padding: 6px 14px; border-radius: 20px; font-size: 13px; }
        .badge-pendiente { background: #fef08a; color: #854d0e; }
        .badge-observada { background: #fed7aa; color: #9a3412; }
        .badge-aprobada { background: #bbf7d0; color: #166534; }
        .badge-rechazada { background: #fecaca; color: #991b1b; }

        .card-body { padding: 30px; }
        .section-title { font-size: 14px; font-weight: 700; color: #0284c7; text-transform: uppercase; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 5px; margin-top: 25px; }
        .section-title:first-child { margin-top: 0; }
        
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .info-group { background: #f8fafc; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
        .info-value { font-size: 14px; color: #0f172a; font-weight: 500; word-break: break-word; }
        .full-width { grid-column: 1 / -1; }
        
        .archivos-container { display: flex; flex-wrap: wrap; gap: 10px; padding: 15px; border: 2px dashed #cbd5e1; border-radius: 8px; background: #fafafa; }
        .btn-descargar { background: #f1f5f9; color: #0c4a6e; padding: 10px 15px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #cbd5e1; transition: all 0.2s; }
        .btn-descargar:hover { background: #e0f2fe; border-color: #0ea5e9; color: #0284c7; }
        
        .btn-cerrar { display: block; width: 100%; text-align: center; padding: 15px; background: #f1f5f9; color: #475569; text-decoration: none; font-weight: bold; transition: background 0.2s; border-top: 1px solid #e2e8f0; }
        .btn-cerrar:hover { background: #e2e8f0; color: #0f172a; }

        /* Panel Auditor */
        .panel-auditor { background: #fff8f1; border: 2px solid #fdba74; }
        .panel-auditor .card-header { background: #ffedd5; border-bottom: 1px solid #fdba74; }
        .btn-accion { padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; color: white; margin-right: 10px; transition: 0.2s;}
        .btn-aprobar { background: #16a34a; } .btn-aprobar:hover { background: #15803d; }
        .btn-rechazar { background: #dc2626; } .btn-rechazar:hover { background: #b91c1c; }
        .btn-observar { background: #f59e0b; } .btn-observar:hover { background: #d97706; }
        textarea.obs { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; margin-bottom: 15px; resize: vertical; min-height: 80px;}

        /* Panel Medico (Corrección) */
        .panel-medico { border: 2px solid #0ea5e9; background: #f0f9ff; }
        .panel-medico .card-header { background: #e0f2fe; border-bottom: 1px solid #0ea5e9; }
        .panel-medico input, .panel-medico select, .panel-medico textarea { padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; width: 100%; box-sizing: border-box; }
        .panel-medico input:focus, .panel-medico select:focus, .panel-medico textarea:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Solicitud <span class="id-badge">#<?php echo $datosSolicitud['id_solicitud']; ?></span></h2>
                <span class="badge <?php echo $claseBadge; ?>"><?php echo htmlspecialchars($datosSolicitud['estado']); ?></span>
            </div>
            
            <div class="card-body">
                <div class="section-title">👤 Datos del Afiliado</div>
                <div class="info-grid">
                    <div class="info-group">
                        <div class="info-label">Nombre Completo</div>
                        <div class="info-value"><?php echo htmlspecialchars(($datosSolicitud['apellido_paciente'] ?? '') . ', ' . ($datosSolicitud['nombre_paciente'] ?? '')); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">DNI</div>
                        <div class="info-value"><?php echo htmlspecialchars($datosSolicitud['dni'] ?? '---'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Nro. de Afiliado</div>
                        <div class="info-value"><?php echo htmlspecialchars($datosSolicitud['nro_afiliado'] ?? '---'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Plan / Cobertura</div>
                        <div class="info-value"><?php echo htmlspecialchars($datosSolicitud['plan'] ?? '---'); ?></div>
                    </div>
                </div>

                <div class="section-title">👨‍⚕️ Médico Solicitante</div>
                <div class="info-grid">
                    <div class="info-group">
                        <div class="info-label">Profesional</div>
                        <div class="info-value">Dr/a. <?php echo htmlspecialchars(($datosSolicitud['apellido_medico'] ?? '') . ', ' . ($datosSolicitud['nombre_medico'] ?? '---')); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Especialidad</div>
                        <div class="info-value"><?php echo htmlspecialchars($datosSolicitud['especialidad_medico'] ?? '---'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Matrícula</div>
                        <div class="info-value"><?php echo htmlspecialchars($datosSolicitud['matricula_medico'] ?? '---'); ?></div>
                    </div>
                </div>

                <div class="section-title">🔬 Detalles Clínicos</div>
                <div class="info-grid">
                    <div class="info-group">
                        <div class="info-label">Práctica Solicitada</div>
                        <div class="info-value"><?php echo htmlspecialchars($datosSolicitud['nombre_practica'] ?? 'No especificada'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Prioridad</div>
                        <div class="info-value">
                            <?php 
                                $prio = strtolower($datosSolicitud['prioridad'] ?? '');
                                echo ($prio == 'alta' ? '🔴 ' : ($prio == 'media' ? '🟡 ' : '🟢 ')) . ucfirst($prio); 
                            ?>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Fecha de Carga</div>
                        <div class="info-value">📅 <?php echo date("d/m/Y", strtotime($datosSolicitud['fecha'])); ?></div>
                    </div>
                    <div class="info-group full-width">
                        <div class="info-label">Diagnóstico / Justificación</div>
                        <div class="info-value" style="font-style: italic; color: #334155; line-height: 1.5;">
                            "<?php echo nl2br(htmlspecialchars($datosSolicitud['diagnostico'])); ?>"
                        </div>
                    </div>
                </div>

                <div class="section-title">📁 Documentación Adjunta</div>
                <div class="archivos-container">
                    <?php if (!empty($archivosAdjuntos) && count($archivosAdjuntos) > 0): ?>
                        <?php foreach($archivosAdjuntos as $archivo): ?>
                            <a href="../uploads/<?php echo htmlspecialchars($archivo['ruta']); ?>" target="_blank" class="btn-descargar">
                                📄 <?php echo htmlspecialchars($archivo['nombre']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="margin: 0; color: #64748b; font-size: 13px; width: 100%; text-align: center;">No se adjuntaron estudios ni recetas en esta solicitud.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <a href="#" onclick="window.close();" class="btn-cerrar">Cerrar Pestaña</a>
        </div>

        <?php if ($rolActual == 'medico' && $estadoNormalizado == 'observada'): ?>
        <div class="card panel-medico">
            <div class="card-header">
                <h2 style="color: #0284c7;">✏️ Corregir Solicitud Observada</h2>
            </div>
            <div class="card-body">
                <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <strong style="color: #b91c1c;">Motivo de la observación (Auditoría):</strong><br>
                    <span style="color: #991b1b; font-style: italic;">"<?php echo htmlspecialchars($motivoCorreccion); ?>"</span>
                </div>

                <form action="../controlador/solicitudControlador.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="corregir">
                    <input type="hidden" name="id_solicitud" value="<?php echo $datosSolicitud['id_solicitud']; ?>">
                    <input type="hidden" name="id_paciente" value="<?php echo $datosSolicitud['id_paciente']; ?>">
                    <input type="hidden" name="id_medico" value="<?php echo $datosSolicitud['id_medico']; ?>">
                    <input type="hidden" name="fecha" value="<?php echo date('Y-m-d'); ?>">

                    <div class="info-grid" style="margin-bottom: 0;">
                        <div class="info-group full-width" style="background: none; border: none; padding: 0;">
                            <label class="info-label" style="display:block; margin-bottom:5px;">Práctica Requerida</label>
                            <select name="id_practica" required>
                                <?php foreach($practicas as $pr): ?>
                                    <option value="<?php echo $pr['id_practica']; ?>" <?php echo ($pr['id_practica'] == $datosSolicitud['id_practica']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pr['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="info-group full-width" style="background: none; border: none; padding: 0;">
                            <label class="info-label" style="display:block; margin-bottom:5px;">Prioridad</label>
                            <select name="prioridad" required>
                                <option value="baja" <?php echo (strtolower($datosSolicitud['prioridad']) == 'baja') ? 'selected' : ''; ?>>🟢 Baja</option>
                                <option value="media" <?php echo (strtolower($datosSolicitud['prioridad']) == 'media') ? 'selected' : ''; ?>>🟡 Media</option>
                                <option value="alta" <?php echo (strtolower($datosSolicitud['prioridad']) == 'alta') ? 'selected' : ''; ?>>🔴 Alta / Urgente</option>
                            </select>
                        </div>

                        <div class="info-group full-width" style="background: none; border: none; padding: 0;">
                            <label class="info-label" style="display:block; margin-bottom:5px;">Diagnóstico / Justificación Clínica</label>
                            <textarea name="diagnostico" required style="min-height: 100px;"><?php echo htmlspecialchars($datosSolicitud['diagnostico']); ?></textarea>
                        </div>

                        <div class="info-group full-width" style="background: none; border: none; padding: 0; margin-bottom:20px;">
                            <label class="info-label" style="display:block; margin-bottom:5px;">Añadir Nuevos Estudios / Recetas (Opcional)</label>
                            <input type="file" name="adjuntos[]" accept=".jpg,.jpeg,.png,.pdf" multiple style="background: #fff; border: 1px dashed #0ea5e9;">
                            <small style="color: #64748b; display: block; margin-top: 5px;">* Los archivos anteriores se conservarán. Suba archivos solo si necesita agregar documentación extra para el auditor.</small>
                        </div>
                    </div>

                    <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 12px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; font-size: 16px; transition: 0.2s;">
                        📤 Enviar Corrección a Auditoría
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($rolActual == 'auditor' && in_array($estadoNormalizado, ['pendiente', 'observada'])): ?>
        <div class="card panel-auditor">
            <div class="card-header">
                <h2 style="color: #c2410c;">⚖️ Dictamen de Auditoría Médica</h2>
            </div>
            <div class="card-body">
                <form action="../controlador/solicitudControlador.php" method="POST">
                    <input type="hidden" name="accion" value="evaluar">
                    <input type="hidden" name="id_solicitud" value="<?php echo $datosSolicitud['id_solicitud']; ?>">
                    
                    <div class="info-label">Observaciones / Justificación (Obligatorio para rechazar u observar)</div>
                    <textarea name="observaciones" class="obs" placeholder="Escriba aquí los motivos de su dictamen..."></textarea>
                    
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="submit" name="estado_nuevo" value="aprobada" class="btn-accion btn-aprobar">✅ Aprobar Prestación</button>
                        <button type="submit" name="estado_nuevo" value="observada" class="btn-accion btn-observar">⚠️ Observar (Devolver a Médico)</button>
                        <button type="submit" name="estado_nuevo" value="rechazada" class="btn-accion btn-rechazar">❌ Rechazar Prestación</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>