<?php
session_start();
$rol = $_SESSION['usuario']['rol'];
$id_usuario = $_SESSION['usuario']['id']; // O como tengas guardado el ID del paciente

if ($rol == 'paciente') {
    // El paciente solo ve las suyas
    $sql = "SELECT * FROM solicitudes WHERE id_paciente = :id_usuario";
} else {
    // Médicos y Admins ven todas
    $sql = "SELECT * FROM solicitudes";
}

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);
$solicitudes = $solicitudModelo->listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Padrón de Solicitudes</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .container-ancho { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .panel-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .panel-header { font-size: 20px; font-weight: 600; color: #0c4a6e; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        
        /* Buscador Expandido */
        .search-container { position: relative; width: 400px; }
        .search-input { width: 100%; padding: 10px 15px 10px 35px; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 14px; outline: none; transition: all 0.2s; box-sizing: border-box; }
        .search-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        .table-responsive { overflow-x: auto; }
        .table-responsive table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 14px; }
        .table-responsive th { padding: 15px 12px; text-align: left; background-color: #f1f5f9; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        .table-responsive td { padding: 15px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; }
        .table-responsive tbody tr:hover { background-color: #f0f9ff; transition: background 0.2s; }
        
        .badge { text-transform: capitalize; font-weight: 600; padding: 5px 10px; border-radius: 20px; font-size: 12px; display: inline-block; text-align: center; }
        .badge-pendiente { background: #fef08a; color: #854d0e; }
        .badge-observada { background: #fed7aa; color: #9a3412; }
        .badge-aprobada { background: #bbf7d0; color: #166534; }
        .badge-rechazada { background: #fecaca; color: #991b1b; }

        /* Botones de acción */
        .btn-icon { background: #f1f5f9; color: #0f172a; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 14px; margin-right: 5px; transition: background 0.2s; display: inline-flex; align-items: center; justify-content: center;}
        .btn-icon:hover { background: #cbd5e1; }
        .btn-evaluar { background-color: #0ea5e9; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; }
        .btn-evaluar:hover { background-color: #0284c7; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Padrón General de Solicitudes</span></h1>
        <a href="../index.php" class="btn-back">Volver al Dashboard</a>
    </div>

    <div class="container-ancho">
        <div class="panel-box">
            <div class="panel-header">
                Listado de Registros
                <div class="search-container">
                    <span class="search-icon">🔍</span>
                    <!-- El buscador filtra ahora por DNI, Nombre o N° de Solicitud -->
                    <input type="text" id="buscadorGlobal" class="search-input" placeholder="Buscar por N° Solicitud, DNI o Paciente...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>DNI</th>
                            <th>Práctica Principal</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($solicitudes)): ?>
                            <tr><td colspan="8" style="text-align:center; padding: 30px; color: #64748b;">No hay solicitudes registradas en el sistema.</td></tr>
                        <?php else: ?>
                            <?php foreach ($solicitudes as $s): 
                                $estadoNormalizado = strtolower(trim($s['estado']));
                                
                                $claseBadge = 'badge-pendiente';
                                if($estadoNormalizado == 'observada') $claseBadge = 'badge-observada';
                                if($estadoNormalizado == 'aprobada') $claseBadge = 'badge-aprobada';
                                if($estadoNormalizado == 'rechazada') $claseBadge = 'badge-rechazada';
                            ?>
                            <tr class="fila-dato">
                                <td class="col-num"><strong>#<?php echo $s['id_solicitud']; ?></strong></td>
                                <td><?php echo isset($s['fecha']) ? date("d/m/Y", strtotime($s['fecha'])) : date("d/m/Y"); ?></td>
                                <td class="col-pac"><strong><?php echo htmlspecialchars($s['apellido_paciente'] . ', ' . $s['nombre_paciente']); ?></strong></td>
                                <td class="col-dni"><?php echo htmlspecialchars($s['dni'] ?? '---'); ?></td>
                                <td><?php echo htmlspecialchars($s['nombre_practica'] ?? '---'); ?></td>
                                <td><?php echo ucfirst($s['prioridad']); ?></td>
                                <td>
                                    <span class="badge <?php echo $claseBadge; ?>">
                                        <?php echo htmlspecialchars($s['estado']); ?>
                                    </span>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <!-- Botón OJITO: Abre en nueva pestaña el detalle completo -->
                                    <a href="ver_solicitud.php?id=<?php echo $s['id_solicitud']; ?>" target="_blank" class="btn-icon" title="Ver Detalles Completos">👁️ Ver</a>
                                    
                                    <?php if ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin' || $rolNormalizado == 'administrador'): ?>
                                        <?php if ($estadoNormalizado == 'pendiente' || $estadoNormalizado == 'observada'): ?>
                                            <a href="evaluar.php?id=<?php echo $s['id_solicitud']; ?>" class="btn-evaluar">Evaluar</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Buscador Global por N° Solicitud, DNI o Nombre de paciente
        document.getElementById('buscadorGlobal').addEventListener('input', function() {
            let filtro = this.value.toLowerCase().trim();
            let filas = document.querySelectorAll('.fila-dato');
            
            filas.forEach(fila => {
                let num = fila.querySelector('.col-num').textContent.toLowerCase();
                let dni = fila.querySelector('.col-dni').textContent.toLowerCase();
                let pac = fila.querySelector('.col-pac').textContent.toLowerCase();
                
                if (num.includes(filtro) || dni.includes(filtro) || pac.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>