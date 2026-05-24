<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }

$rol = $_SESSION['usuario']['rol'] ?? '';
$email_usuario = $_SESSION['usuario']['email'] ?? '';
$rolNormalizado = strtolower(trim($rol));

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);

if ($rolNormalizado == 'paciente') {
    $solicitudes = $solicitudModelo->listarPorEmailPaciente($email_usuario);
} else {
    $solicitudes = $solicitudModelo->listar();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Padrón de Solicitudes - MediFlow</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; color: #333; }
        .container-ancho { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .panel-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .panel-header { font-size: 20px; font-weight: 600; color: #0c4a6e; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .search-container { position: relative; width: 400px; }
        .search-input { width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 20px; outline: none; }
        .table-responsive table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 14px; }
        .table-responsive th { padding: 15px 12px; background-color: #f1f5f9; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .table-responsive td { padding: 15px 12px; border-bottom: 1px solid #e2e8f0; }
        .badge { font-weight: 600; padding: 5px 10px; border-radius: 20px; font-size: 12px; text-transform: capitalize; }
        .badge-pendiente { background: #fef08a; color: #854d0e; }
        .badge-observada { background: #fed7aa; color: #9a3412; }
        .badge-aprobada { background: #bbf7d0; color: #166534; }
        .badge-rechazada { background: #fecaca; color: #991b1b; }
        .btn-icon { background: #f1f5f9; color: #0f172a; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-print { background-color: #22c55e; color: white; margin-left: 5px;}
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• <?php echo ($rolNormalizado == 'paciente') ? 'Mis Solicitudes' : 'Padrón General'; ?></span></h1>
        <a href="../index.php" class="btn-back">Volver al Dashboard</a>
    </div>

    <div class="container-ancho">
        <div class="panel-box">
            <div class="panel-header">
                Listado de Registros
                <div class="search-container"><input type="text" id="buscadorGlobal" class="search-input" placeholder="Buscar..."></div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>N°</th><th>Fecha</th><th>Paciente</th><th>DNI</th><th>Práctica</th><th>Prioridad</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php if (empty($solicitudes)): ?>
                            <tr><td colspan="8" style="text-align:center; padding: 30px;">No hay registros.</td></tr>
                        <?php else: foreach ($solicitudes as $s): 
                            $estado = strtolower(trim($s['estado']));
                            $clase = 'badge-pendiente';
                            if($estado == 'observada') $clase = 'badge-observada';
                            if($estado == 'aprobada') $clase = 'badge-aprobada';
                            if($estado == 'rechazada') $clase = 'badge-rechazada';
                        ?>
                            <tr class="fila-dato">
                                <td class="col-num">#<?php echo $s['id_solicitud']; ?></td>
                                <td><?php echo date("d/m/Y", strtotime($s['fecha'])); ?></td>
                                <td class="col-pac"><?php echo htmlspecialchars($s['apellido_paciente'] . ', ' . $s['nombre_paciente']); ?></td>
                                <td class="col-dni"><?php echo htmlspecialchars($s['dni'] ?? '---'); ?></td>
                                <td><?php echo htmlspecialchars($s['nombre_practica'] ?? '---'); ?></td>
                                <td><?php echo ucfirst($s['prioridad']); ?></td>
                                <td><span class="badge <?php echo $clase; ?>"><?php echo htmlspecialchars($s['estado']); ?></span></td>
                                <td>
                                    <a href="ver_solicitud.php?id=<?php echo $s['id_solicitud']; ?>" target="_blank" class="btn-icon">👁️ Ver</a>
                                    <?php if ($estado == 'aprobada'): ?>
                                        <a href="imprimir_solicitud.php?id=<?php echo $s['id_solicitud']; ?>" target="_blank" class="btn-icon btn-print">🖨️ Orden</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>