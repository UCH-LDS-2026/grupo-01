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

// Filtros
$filtros = [
    'estado' => $_GET['estado'] ?? '',
    'fecha_desde' => $_GET['fecha_desde'] ?? '',
    'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
    'id_practica' => $_GET['id_practica'] ?? '',
    'paciente' => $_GET['paciente'] ?? ''
];

$stats = $solicitudModelo->obtenerEstadisticas();
$pendientes = $solicitudModelo->obtenerPendientesFiltradas($filtros);
$practicas = $conexion->query("SELECT * FROM practica ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

$totalNoLeidas = $solicitudModelo->contarPendientes();
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
        
        /* Sistema de Notificaciones */
        .notif-container { position: relative; display: inline-block; cursor:pointer; }
        .notif-dot { position: absolute; top: -5px; right: -8px; background: #ef4444; color: white; border-radius: 50%; padding: 2px 6px; font-size: 11px; font-weight: bold; border: 2px solid #0b5687; }
        
        /* Filtros */
        .filter-bar { background: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .filter-group { display: flex; flex-direction: column; flex: 1; min-width: 150px; }
        .filter-group label { font-size: 12px; font-weight: bold; color: #64748b; margin-bottom: 5px; }
        .filter-group input, .filter-group select { padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; }
        .btn-filtrar { background: #0c4a6e; color: white; border: none; padding: 9px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-filtrar:hover { background: #075985; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Dashboard Analítico</span></h1>
        
        <div style="display:flex; align-items:center; gap:20px;">
            <?php if ($rol == 'auditor'): ?>
            <div class="notif-container" title="Solicitudes Pendientes">
                <span style="font-size:24px;">🔔</span>
                <?php if ($totalNoLeidas > 0): ?>
                    <span class="notif-dot"><?php echo $totalNoLeidas; ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <a href="../index.php" class="btn-back" style="margin-left: 15px;">⬅ Volver al Inicio</a>
        </div>
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

        <form class="filter-bar" method="GET" action="">
            <div class="filter-group">
                <label>Paciente (DNI o Apellido)</label>
                <input type="text" id="filterPaciente" name="paciente" placeholder="Ej: Perez o DNI..." value="<?php echo htmlspecialchars($filtros['paciente']); ?>">
            </div>
            <div class="filter-group">
                <label>Desde Fecha</label>
                <input type="date" name="fecha_desde" value="<?php echo htmlspecialchars($filtros['fecha_desde']); ?>">
            </div>
            <div class="filter-group">
                <label>Hasta Fecha</label>
                <input type="date" name="fecha_hasta" value="<?php echo htmlspecialchars($filtros['fecha_hasta']); ?>">
            </div>
            <div class="filter-group">
                <label>Estado</label>
                <select name="estado" id="filterEstado">
                    <option value="">Todos los Estados</option>
                    <option value="pendiente" <?php if($filtros['estado'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                    <option value="aprobada" <?php if($filtros['estado'] == 'aprobada') echo 'selected'; ?>>Aprobada</option>
                    <option value="rechazada" <?php if($filtros['estado'] == 'rechazada') echo 'selected'; ?>>Rechazada</option>
                    <option value="observada" <?php if($filtros['estado'] == 'observada') echo 'selected'; ?>>Observada</option>
                </select>
            </div>
            <button type="submit" class="btn-filtrar">🔍 Aplicar Filtros</button>
            <a href="dashboard.php" style="color:#ef4444; font-size:13px; text-decoration:none; font-weight:bold;">X Limpiar</a>
        </form>

        <div class="card">
            <h2>📊 Listado Analítico</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Paciente</th>
                            <th>DNI</th> <th>Práctica</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaDashboard">
                        <?php if(empty($pendientes)): ?>
                            <tr><td colspan="8" style="text-align:center; padding: 20px;">No hay resultados con los filtros actuales.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pendientes as $p): ?>
                                <tr class="fila-dashboard">
                                    <td><strong>#<?php echo $p['id_solicitud']; ?></strong></td>
                                    <td class="col-paciente"><?php echo htmlspecialchars($p['apellido_paciente'] . ', ' . $p['nombre_paciente']); ?></td>
                                    <td class="col-dni"><?php echo htmlspecialchars($p['dni'] ?? '---'); ?></td> <td><?php echo htmlspecialchars($p['nombre_practica'] ?? '---'); ?></td>
                                    <td style="color: <?php echo (strtolower($p['prioridad']) == 'alta') ? 'red' : 'black'; ?>">
                                        <strong><?php echo ucfirst(htmlspecialchars($p['prioridad'])); ?></strong>
                                    </td>
                                    <td class="col-estado">
                                        <span style="font-weight:bold; color: <?php echo (strtolower($p['estado']) == 'pendiente') ? '#0284c7' : '#166534'; ?>">
                                            <?php echo ucfirst(htmlspecialchars($p['estado'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("d/m/Y", strtotime($p['fecha'])); ?></td>
                                    <td><a href="ver_solicitud.php?id=<?php echo $p['id_solicitud']; ?>" target="_blank" style="background:#e0f2fe; color:#0c4a6e; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold;">Ver Info</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputPaciente = document.getElementById('filterPaciente');
            const selectEstado = document.getElementById('filterEstado');
            const filas = document.querySelectorAll('.fila-dashboard');

            function filtrarTablaDinamica() {
                const txtPaciente = inputPaciente.value.toLowerCase().trim();
                const txtEstado = selectEstado.value.toLowerCase().trim();

                filas.forEach(fila => {
                    const nombrePaciente = fila.querySelector('.col-paciente').textContent.toLowerCase();
                    const dniPaciente = fila.querySelector('.col-dni').textContent.toLowerCase(); // NUEVO
                    const estado = fila.querySelector('.col-estado').textContent.trim().toLowerCase();

                    // CORRECCIÓN: Evalúa si coincide el texto con el nombre O con el DNI
                    let coincidePaciente = (txtPaciente === '') || nombrePaciente.includes(txtPaciente) || dniPaciente.includes(txtPaciente);
                    let coincideEstado = (txtEstado === '') || (estado === txtEstado);

                    if (coincidePaciente && coincideEstado) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            }

            if (inputPaciente) inputPaciente.addEventListener('input', filtrarTablaDinamica);
            if (selectEstado) selectEstado.addEventListener('change', filtrarTablaDinamica);
        });
    </script>
</body>
</html>