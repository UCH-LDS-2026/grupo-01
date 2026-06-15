<?php
session_start();
if (!isset($_SESSION['usuario'])) { exit("Acceso denegado"); }

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$id_solicitud = $_GET['id'] ?? 0;
$solicitudModelo = new Solicitud($conexion);

// Consulta idéntica a la original para traer todos los datos
$sql = "SELECT s.*, 
        p.nombre as nombre_paciente, p.apellido as apellido_paciente, p.dni, p.nro_afiliado, p.plan, p.email as email_paciente, p.telefono as telefono_paciente, p.fecha_nacimiento,
        pr.nombre as nombre_practica, pr.descripcion as desc_practica,
        u_med.nombre as nombre_medico, u_med.apellido as apellido_medico, m.matricula, m.especialidad,
        u_aud.nombre as nombre_auditor, u_aud.apellido as apellido_auditor, aud.sector
        FROM solicitud s
        LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
        LEFT JOIN practica pr ON s.id_practica = pr.id_practica
        LEFT JOIN medico m ON s.id_medico = m.id_usuario
        LEFT JOIN usuario u_med ON m.id_usuario = u_med.id_usuario
        LEFT JOIN evaluacion ev ON ev.id_solicitud = s.id_solicitud
        LEFT JOIN auditor aud ON ev.id_auditor = aud.id_usuario
        LEFT JOIN usuario u_aud ON aud.id_usuario = u_aud.id_usuario
        WHERE s.id_solicitud = ?
        ORDER BY ev.id_evaluacion DESC LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_solicitud);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) { 
    $sql_fallback = "SELECT s.*, 
        p.nombre as nombre_paciente, p.apellido as apellido_paciente, p.dni, p.nro_afiliado, p.plan, p.email as email_paciente, p.telefono as telefono_paciente, p.fecha_nacimiento,
        pr.nombre as nombre_practica, pr.descripcion as desc_practica,
        u_med.nombre as nombre_medico, u_med.apellido as apellido_medico, m.matricula, m.especialidad
        FROM solicitud s
        LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
        LEFT JOIN practica pr ON s.id_practica = pr.id_practica
        LEFT JOIN medico m ON s.id_medico = m.id_usuario
        LEFT JOIN usuario u_med ON m.id_usuario = u_med.id_usuario
        WHERE s.id_solicitud = ?";
    $stmt_fallback = $conexion->prepare($sql_fallback);
    $stmt_fallback->bind_param("i", $id_solicitud);
    $stmt_fallback->execute();
    $data = $stmt_fallback->get_result()->fetch_assoc();
    
    if(!$data) { exit("Solicitud no encontrada."); }
}

// ==========================================
// GENERACIÓN DEL CÓDIGO QR EN MEMORIA
// ==========================================
require_once __DIR__ . '/../../src/phpqrcode/qrlib.php';

$url_validacion = "http://192.168.0.127/mediflow/grupo-01/MediFlow/validar.php?id=" . $data['id_solicitud'];

ob_start();
QRcode::png($url_validacion, false, QR_ECLEVEL_L, 4, 2);
$datos_imagen = ob_get_clean();

// SOLUCIÓN AL ERROR: Forzamos al navegador a renderizar HTML puro, anulando el header de la librería
header('Content-Type: text/html; charset=UTF-8');

$base64_qr = 'data:image/png;base64,' . base64_encode($datos_imagen);
// ==========================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden_Medica_<?php echo $data['id_solicitud']; ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; padding: 40px; color: #1e293b; background: white; }
        .voucher { border: 2px dashed #0c4a6e; padding: 30px; border-radius: 8px; max-width: 700px; margin: 0 auto; position: relative; }
        .header-voucher { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0c4a6e; padding-bottom: 15px; margin-bottom: 20px; }
        .header-voucher h1 { margin: 0; color: #0c4a6e; font-size: 24px; }
        .header-voucher .num { font-size: 18px; font-weight: bold; color: #e11d48; text-align: right;}
        .estado-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-top: 5px; background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;}
        
        .grid-data { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; font-size: 13px; }
        .section-title { font-weight: bold; color: #0c4a6e; text-transform: uppercase; margin-bottom: 5px; grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin-top: 10px; }
        .full-width { grid-column: span 2; }
        
        .footer-voucher { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 15px; position: relative; min-height: 100px;}
        .footer-disclaimer { text-align: center; font-size: 11px; color: #64748b; margin-top: 20px;}
        
        .auditor-info { font-size: 12px; color: #475569; position: absolute; left: 0; top: 15px; }
        
        /* Ubicación perfecta para el QR abajo a la derecha */
        .qr-code { position: absolute; bottom: 40px; right: 20px; text-align: center; }
        .qr-code img { width: 95px; height: 95px; border: 1px solid #cbd5e1; padding: 4px; border-radius: 4px; background: white; }
        
        /* Ocultar botones al mandar a la impresora */
        @media print { .no-print { display: none; } body { padding: 0; } .voucher { border: 2px solid #000; } }
        .btn-print-now { background: #0c4a6e; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div style="text-align: center;" class="no-print">
        <button class="btn-print-now" onclick="window.print()">🖨️ Confirmar Impresión / Guardar PDF</button>
    </div>

    <div class="voucher">
        
        <div class="header-voucher">
            <div>
                <h1>MediFlow</h1>
                <small>Sistema de Auditoría y Cobertura Médica</small>
            </div>
            <div class="num">
                ORDEN DE PRESTACIÓN<br>#<?php echo $data['id_solicitud']; ?>
                <br>
                <span class="estado-badge">Estado: <?php echo strtoupper($data['estado']); ?></span>
            </div>
        </div>

        <div class="grid-data">
            <div class="section-title">Datos del Afiliado</div>
            <div><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($data['apellido_paciente'] . ', ' . $data['nombre_paciente']); ?></div>
            <div><strong>DNI:</strong> <?php echo htmlspecialchars($data['dni']); ?></div>
            <div><strong>N° Afiliado:</strong> <?php echo htmlspecialchars($data['nro_afiliado'] ?? '---'); ?></div>
            <div><strong>Plan Cobertura:</strong> <?php echo htmlspecialchars($data['plan'] ?? '---'); ?></div>
            <div><strong>Fecha Nacimiento:</strong> <?php echo date("d/m/Y", strtotime($data['fecha_nacimiento'])); ?></div>
            <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($data['telefono_paciente'] ?? '---'); ?></div>
            <div class="full-width"><strong>Email:</strong> <?php echo htmlspecialchars($data['email_paciente'] ?? '---'); ?></div>

            <div class="section-title">Médico Solicitante</div>
            <div><strong>Profesional:</strong> Dr/a. <?php echo htmlspecialchars($data['apellido_medico'] . ', ' . $data['nombre_medico']); ?></div>
            <div><strong>Matrícula:</strong> <?php echo htmlspecialchars($data['matricula'] ?? '---'); ?></div>
            <div class="full-width"><strong>Especialidad:</strong> <?php echo htmlspecialchars($data['especialidad'] ?? '---'); ?></div>

            <div class="section-title">Detalle de la Prestación Requerida</div>
            <div><strong>Práctica Requerida:</strong> <?php echo htmlspecialchars($data['nombre_practica']); ?></div>
            <div><strong>Prioridad Clínica:</strong> <?php echo strtoupper($data['prioridad']); ?></div>
            <?php if(!empty($data['desc_practica'])): ?>
                <div class="full-width"><strong>Descripción:</strong> <?php echo htmlspecialchars($data['desc_practica']); ?></div>
            <?php endif; ?>
            <div class="full-width"><strong>Diagnóstico de Base:</strong> <?php echo htmlspecialchars($data['diagnostico']); ?></div>
            
            <div class="section-title">Vigencia y Emisión</div>
            <div><strong>Fecha Emisión:</strong> <?php echo date("d/m/Y", strtotime($data['fecha'])); ?></div>
            <div><strong>Válido Hasta:</strong> <?php echo date("d/m/Y", strtotime($data['fecha'] . "+ 30 days")); ?> (30 días)</div>
        </div>

        <div class="footer-voucher">
            <?php if ($data['estado'] == 'aprobada'): ?>
                <div class="qr-code">
                    <img src="<?php echo $base64_qr; ?>" alt="QR Validación">
                </div>
                
                <div class="auditor-info">
                    <strong>Aprobado por:</strong> <?php echo htmlspecialchars(($data['apellido_auditor'] ?? '') . ', ' . ($data['nombre_auditor'] ?? 'Sistema Central')); ?><br>
                    <strong>Sector:</strong> <?php echo htmlspecialchars($data['sector'] ?? 'Auditoría General'); ?>
                </div>
            <?php endif; ?>

            <div class="footer-disclaimer" style="clear:both; padding-top:40px;">
                Documento digital válido como autorización de prestación médica. Presentar junto a la credencial física en el centro médico prestador. Escanee el código QR para verificar autenticidad.
            </div>
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>