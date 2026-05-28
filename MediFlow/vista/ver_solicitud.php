<?php
session_start();
if (!isset($_SESSION['usuario']['rol'])) { header("Location: login.php"); exit; }

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$id_solicitud = $_GET['id'] ?? null;
if (!$id_solicitud) { die("ID de solicitud no proporcionado."); }

// Usamos el listado que ya arma tu modelo y filtramos la correcta (forma segura y compatible)
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

// Puede contener uno o varios archivos separados por coma
$archivosAdjuntos = !empty($datosSolicitud['ruta_archivo']) ? explode(',', $datosSolicitud['ruta_archivo']) : [];
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
        
        .card { background: #fff; border-radius: 10px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); overflow: hidden; }
        
        /* Encabezado mejorado para mejor contraste */
        .card-header { background: #ffffff; border-bottom: 2px solid #e0f2fe; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { margin: 0; font-size: 22px; font-weight: 600; color: #0c4a6e; display: flex; align-items: center; gap: 10px; }
        .id-badge { background: #0ea5e9; color: white; padding: 4px 12px; border-radius: 6px; font-size: 18px; font-weight: bold; }
        
        .badge { text-transform: capitalize; font-weight: 600; padding: 6px 14px; border-radius: 20px; font-size: 13px; }
        .badge-pendiente { background: #fef08a; color: #854d0e; }
        .badge-observada { background: #fed7aa; color: #9a3412; }
        .badge-aprobada { background: #bbf7d0; color: #166534; }
        .badge-rechazada { background: #fecaca; color: #991b1b; }

        .card-body { padding: 30px; }
        
        /* Estilos de las Fichas (Cards) internas */
        .section-title { font-size: 14px; font-weight: 700; color: #0284c7; text-transform: uppercase; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 5px; margin-top: 25px; }
        .section-title:first-child { margin-top: 0; }
        
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .info-group { background: #f8fafc; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
        .info-value { font-size: 14px; color: #0f172a; font-weight: 500; word-break: break-word; }
        
        .full-width { grid-column: 1 / -1; }
        
        /* Estilos para múltiples adjuntos */
        .archivos-container { display: flex; flex-wrap: wrap; gap: 10px; padding: 15px; border: 2px dashed #cbd5e1; border-radius: 8px; background: #fafafa; }
        .btn-descargar { background: #f1f5f9; color: #0c4a6e; padding: 10px 15px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #cbd5e1; transition: all 0.2s; }
        .btn-descargar:hover { background: #e0f2fe; border-color: #0ea5e9; color: #0284c7; }
        
        .btn-cerrar { display: block; width: 100%; text-align: center; padding: 15px; background: #f1f5f9; color: #475569; text-decoration: none; font-weight: bold; transition: background 0.2s; border-top: 1px solid #e2e8f0; }
        .btn-cerrar:hover { background: #e2e8f0; color: #0f172a; }
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
                    <?php if (count($archivosAdjuntos) > 0): ?>
                        <?php foreach($archivosAdjuntos as $archivo): ?>
                            <a href="../uploads/<?php echo htmlspecialchars(trim($archivo)); ?>" target="_blank" class="btn-descargar">
                                📄 <?php echo htmlspecialchars(trim($archivo)); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="margin: 0; color: #64748b; font-size: 13px; width: 100%; text-align: center;">No se adjuntaron estudios ni recetas en esta solicitud.</p>
                    <?php endif; ?>
                </div>

            </div>
            
            <a href="#" onclick="window.close();" class="btn-cerrar">Cerrar Pestaña</a>
        </div>
    </div>
</body>
</html>