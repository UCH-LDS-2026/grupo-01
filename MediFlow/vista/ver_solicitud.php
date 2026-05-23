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

$archivoAdjunto = $datosSolicitud['ruta_archivo'] ?? '';
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
        
        .card { background: #fff; border-radius: 10px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: #0c4a6e; color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { margin: 0; font-size: 22px; font-weight: 500; }
        
        .badge { text-transform: capitalize; font-weight: 600; padding: 6px 14px; border-radius: 20px; font-size: 13px; }
        .badge-pendiente { background: #fef08a; color: #854d0e; }
        .badge-observada { background: #fed7aa; color: #9a3412; }
        .badge-aprobada { background: #bbf7d0; color: #166534; }
        .badge-rechazada { background: #fecaca; color: #991b1b; }

        .card-body { padding: 30px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px; }
        .info-group { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-label { font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 8px; }
        .info-value { font-size: 16px; color: #0f172a; font-weight: 500; word-break: break-word; }
        
        .full-width { grid-column: 1 / -1; }
        
        .archivo-box { text-align: center; padding: 20px; border: 2px dashed #cbd5e1; border-radius: 8px; background: #fafafa; margin-top: 10px; }
        .btn-descargar { background: #0ea5e9; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block; margin-top: 10px; transition: background 0.2s; }
        .btn-descargar:hover { background: #0284c7; }
        
        .btn-cerrar { display: block; width: 100%; text-align: center; padding: 15px; background: #e2e8f0; color: #475569; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        .btn-cerrar:hover { background: #cbd5e1; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Solicitud #<?php echo $datosSolicitud['id_solicitud']; ?></h2>
                <span class="badge <?php echo $claseBadge; ?>"><?php echo htmlspecialchars($datosSolicitud['estado']); ?></span>
            </div>
            
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-group">
                        <div class="info-label">Fecha de Carga</div>
                        <div class="info-value">📅 <?php echo date("d/m/Y", strtotime($datosSolicitud['fecha'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Prioridad Médica</div>
                        <div class="info-value">
                            <?php 
                                $prio = strtolower($datosSolicitud['prioridad']);
                                echo ($prio == 'alta' ? '🔴 ' : ($prio == 'media' ? '🟡 ' : '🟢 ')) . ucfirst($prio); 
                            ?>
                        </div>
                    </div>

                    <div class="info-group full-width">
                        <div class="info-label">Afiliado / Paciente</div>
                        <div class="info-value">👤 <?php echo htmlspecialchars($datosSolicitud['apellido_paciente'] . ', ' . $datosSolicitud['nombre_paciente']); ?> (DNI: <?php echo htmlspecialchars($datosSolicitud['dni'] ?? '---'); ?>)</div>
                    </div>

                    <div class="info-group full-width">
                        <div class="info-label">Práctica Solicitada</div>
                        <div class="info-value">🩺 <?php echo htmlspecialchars($datosSolicitud['nombre_practica'] ?? 'No especificada'); ?></div>
                    </div>

                    <div class="info-group full-width">
                        <div class="info-label">Diagnóstico / Justificación (Completo)</div>
                        <div class="info-value" style="font-style: italic; color: #334155;">
                            "<?php echo nl2br(htmlspecialchars($datosSolicitud['diagnostico'])); ?>"
                        </div>
                    </div>

                    <div class="info-group full-width">
                        <div class="info-label">Documentación Adjunta</div>
                        <div class="archivo-box">
                            <?php if (!empty($archivoAdjunto)): ?>
                                <p style="margin: 0 0 10px 0; color: #047857;"><strong>✅ Archivo Cargado</strong></p>
                                <a href="../uploads/<?php echo htmlspecialchars($archivoAdjunto); ?>" target="_blank" class="btn-descargar">📄 Abrir Documento Adjunto</a>
                            <?php else: ?>
                                <p style="margin: 0; color: #64748b;">No se adjuntaron estudios ni recetas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="#" onclick="window.close();" class="btn-cerrar">Cerrar Pestaña</a>
        </div>
    </div>
</body>
</html>