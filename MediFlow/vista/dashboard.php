<?php
session_start();
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));
if ($rol != 'admin' && $rol != 'auditor') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);
$stats = $solicitudModelo->obtenerEstadisticas();
$pendientes = $solicitudModelo->obtenerPendientesDashboard();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MediFlow - Dashboard</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid #ccc; }
        .stat-card .numero { font-size: 36px; font-weight: bold; margin: 10px 0 0 0; color: #333; }
        .border-blue { border-bottom-color: #0284c7; }
        .border-green { border-bottom-color: #16a34a; }
        .border-red { border-bottom-color: #dc2626; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Dashboard Analítico</span></h1>
        <a href="../index.php" class="btn-back">⬅ Volver al Inicio</a>
    </div>

    <div class="container-ancho">
        <div class="dashboard-grid">
            <div class="stat-card border-blue">
                <h3>Pendientes</h3>
                <p class="numero"><?php echo $stats['pendientes'] ?? 0; ?></p>
            </div>
            <div class="stat-card border-green">
                <h3>Aprobadas</h3>
                <p class="numero"><?php echo $stats['aprobadas'] ?? 0; ?></p>
            </div>
            <div class="stat-card border-red">
                <h3>Rechazadas</h3>
                <p class="numero"><?php echo $stats['rechazadas'] ?? 0; ?></p>
            </div>
        </div>

        <div class="card">
            <h2> Pendientes por Prioridad</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Paciente</th>
                            <th>Prioridad</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendientes as $p): ?>
                            <tr>
                                <td><strong>#<?php echo $p['id_solicitud']; ?></strong></td>
                                <td><?php echo htmlspecialchars($p['apellido_paciente'] . ', ' . $p['nombre_paciente']); ?></td>
                                <td style="color: <?php echo ($p['prioridad'] == 'Alta') ? 'red' : 'black'; ?>">
                                    <strong><?php echo htmlspecialchars($p['prioridad']); ?></strong>
                                </td>
                                <td><?php echo date("d/m/Y", strtotime($p['fecha'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>