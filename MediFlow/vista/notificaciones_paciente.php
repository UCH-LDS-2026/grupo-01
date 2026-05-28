<?php
session_start();
if (!isset($_SESSION['usuario']) || strtolower(trim($_SESSION['usuario']['rol'])) != 'paciente') { header("Location: login.php"); exit; }

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);
$solicitudes = $solicitudModelo->listarPorEmailPaciente($_SESSION['usuario']['email']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - MediFlow</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; padding: 40px; }
        .notif-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .notif-item { padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 6px solid #ccc; background: #f8fafc; }
        .notif-title { font-weight: bold; font-size: 16px; margin-bottom: 5px; }
        .notif-desc { color: #475569; font-size: 14px; margin: 0; }
        .notif-date { font-size: 12px; color: #94a3b8; display: block; margin-top: 10px; }
        
        .n-aprobada { border-left-color: #22c55e; background: #f0fdf4; }
        .n-aprobada .notif-title { color: #166534; }
        
        .n-observada { border-left-color: #f97316; background: #fff7ed; }
        .n-observada .notif-title { color: #9a3412; }
        
        .n-rechazada { border-left-color: #ef4444; background: #fef2f2; }
        .n-rechazada .notif-title { color: #991b1b; }

        .n-pendiente { border-left-color: #eab308; background: #fefce8; }
        .n-pendiente .notif-title { color: #854d0e; }
    </style>
</head>
<body>
    <div class="notif-container">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 25px;">
            <h2 style="margin:0; color:#0c4a6e;">🔔 Centro de Notificaciones</h2>
            <a href="../index.php" style="color:#0c4a6e; text-decoration: none; font-weight: bold;">Volver al Dashboard</a>
        </div>

        <?php 
        $count = 0;
        foreach ($solicitudes as $s): 
            $estado = strtolower(trim($s['estado']));
            $fechaStr = date("d/m/Y", strtotime($s['fecha']));
            $practica = htmlspecialchars($s['nombre_practica']);
            
            if ($estado == 'aprobada'): ?>
                <div class="notif-item n-aprobada">
                    <div class="notif-title">✅ Solicitud Autorizada</div>
                    <p class="notif-desc">Tu solicitud #<?php echo $s['id_solicitud']; ?> para <strong><?php echo $practica; ?></strong> ha sido aprobada. Ya puedes imprimir tu orden desde el padrón.</p>
                    <span class="notif-date">Ingresada el: <?php echo $fechaStr; ?></span>
                </div>
            <?php $count++; elseif ($estado == 'observada'): ?>
                <div class="notif-item n-observada">
                    <div class="notif-title">⚠️ Solicitud con Observaciones</div>
                    <p class="notif-desc">Tu solicitud #<?php echo $s['id_solicitud']; ?> para <strong><?php echo $practica; ?></strong> requiere revisión. Tu médico ya fue notificado para corregirla.</p>
                    <span class="notif-date">Ingresada el: <?php echo $fechaStr; ?></span>
                </div>
            <?php $count++; elseif ($estado == 'rechazada'): ?>
                <div class="notif-item n-rechazada">
                    <div class="notif-title">❌ Solicitud Rechazada</div>
                    <p class="notif-desc">Tu solicitud #<?php echo $s['id_solicitud']; ?> para <strong><?php echo $practica; ?></strong> no fue autorizada por auditoría.</p>
                    <span class="notif-date">Ingresada el: <?php echo $fechaStr; ?></span>
                </div>
            <?php $count++; elseif ($estado == 'pendiente'): ?>
                <div class="notif-item n-pendiente">
                    <div class="notif-title">⏳ Solicitud en Evaluación</div>
                    <p class="notif-desc">Tu solicitud #<?php echo $s['id_solicitud']; ?> para <strong><?php echo $practica; ?></strong> se encuentra pendiente de revisión por el equipo de auditoría.</p>
                    <span class="notif-date">Ingresada el: <?php echo $fechaStr; ?></span>
                </div>
            <?php $count++; endif; 
        endforeach; 
        
        if($count == 0) {
            echo "<p style='color:#64748b; text-align:center;'>No tienes notificaciones en este momento.</p>";
        }
        ?>
    </div>
</body>
</html>