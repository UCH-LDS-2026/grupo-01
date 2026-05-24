<?php
session_start();
if (!isset($_SESSION['usuario'])) { exit("Acceso denegado"); }

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$id_solicitud = $_GET['id'] ?? 0;
$solicitudModelo = new Solicitud($conexion);

// Usamos el listado general filtrando por ID para tener todos los JOINs de nombres
$sql = "SELECT s.*, p.nombre as nombre_paciente, p.apellido as apellido_paciente, p.dni, p.nro_afiliado, p.plan, pr.nombre as nombre_practica, pr.descripcion as desc_practica
        FROM solicitud s
        LEFT JOIN paciente p ON s.id_paciente = p.id_paciente
        LEFT JOIN practica pr ON s.id_practica = pr.id_practica
        WHERE s.id_solicitud = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_solicitud);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) { exit("Solicitud no encontrada."); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden_Medica_<?php echo $data['id_solicitud']; ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; padding: 40px; color: #1e293b; background: white; }
        .voucher { border: 2px dashed #0c4a6e; padding: 30px; border-radius: 8px; max-width: 700px; margin: 0 auto; position: relative; }
        .header-voucher { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0c4a6e; padding-bottom: 15px; margin-bottom: 20px; }
        .header-voucher h1 { margin: 0; color: #0c4a6e; font-size: 24px; }
        .header-voucher .num { font-size: 18px; font-weight: bold; color: #e11d48; }
        .grid-data { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; font-size: 14px; }
        .section-title { font-weight: bold; color: #0c4a6e; text-transform: uppercase; margin-bottom: 5px; grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin-top: 10px; }
        .full-width { grid-column: span 2; }
        .footer-voucher { text-align: center; margin-top: 40px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 15px; }
        .stamp { position: absolute; bottom: 60px; right: 40px; border: 3px double #166534; color: #166534; transform: rotate(-10deg); padding: 8px 15px; font-weight: bold; font-size: 14px; text-transform: uppercase; border-radius: 4px; background: rgba(255,255,255,0.9); }
        
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
        <div class="stamp">Autorizado<br>Auditoría Médica</div>
        
        <div class="header-voucher">
            <div>
                <h1>MediFlow</h1>
                <small>Sistema de Auditoría y Cobertura Médica</small>
            </div>
            <div class="num">ORDEN DE AUTORIZACIÓN<br>#<?php echo $data['id_solicitud']; ?></div>
        </div>

        <div class="grid-data">
            <div class="section-title">Datos del Afiliado</div>
            <div><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($data['apellido_paciente'] . ', ' . $data['nombre_paciente']); ?></div>
            <div><strong>DNI:</strong> <?php echo htmlspecialchars($data['dni']); ?></div>
            <div><strong>N° Afiliado:</strong> <?php echo htmlspecialchars($data['nro_afiliado'] ?? '---'); ?></div>
            <div><strong>Plan Soportado:</strong> <?php echo htmlspecialchars($data['plan'] ?? '---'); ?></div>

            <div class="section-title">Detalle de la Prestación Autorizada</div>
            <div class="full-width"><strong>Práctica Requerida:</strong> <?php echo htmlspecialchars($data['nombre_practica']); ?></div>
            <?php if(!empty($data['desc_practica'])): ?>
                <div class="full-width"><strong>Descripción:</strong> <?php echo htmlspecialchars($data['desc_practica']); ?></div>
            <?php endif; ?>
            <div class="full-width"><strong>Diagnóstico de Base:</strong> <?php echo htmlspecialchars($data['diagnostico']); ?></div>
            
            <div class="section-title">Vigencia y Emisión</div>
            <div><strong>Fecha Emisión:</strong> <?php echo date("d/m/Y", strtotime($data['fecha'])); ?></div>
            <div><strong>Válido Hasta:</strong> <?php echo date("d/m/Y", strtotime($data['fecha'] . "+ 30 days")); ?> (30 días)</div>
        </div>

        <div class="footer-voucher">
            Documento digital válido como autorización de prestación médica. Presentar junto a la credencial física en el centro médico prestador.
        </div>
    </div>

    <script>
        // Disparar la impresión automáticamente al cargar la pestaña
        window.onload = function() { window.print(); }
    </script>
</body>
</html>