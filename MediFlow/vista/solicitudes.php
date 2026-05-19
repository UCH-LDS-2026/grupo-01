<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Validamos sesión e identificamos el ROL
if (!isset($_SESSION['usuario'])) {
    header("Location: /mediflow/grupo-01/MediFlow/vista/login.php");
    exit;
}

$rolUsuarioOriginal = $_SESSION['usuario']['rol'] ?? 'paciente'; 
$rolNormalizado = strtolower(trim($rolUsuarioOriginal)); 

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Solicitud.php';
require_once __DIR__ . '/../modelo/Paciente.php';

/** @var mysqli $conexion */
$solicitudModelo = new Solicitud($conexion);
$pacienteModelo = new Paciente($conexion);

$listaSolicitudes = $solicitudModelo->listar();
$listaPacientes = $pacienteModelo->listar(); 

$medicosDemo = [['id' => 1, 'nombre' => 'Dr. Roberto Fernández'], ['id' => 2, 'nombre' => 'Dra. Laura Gómez']];
$practicasDemo = [['id' => 1, 'nombre' => 'Laboratorio Clínico'], ['id' => 2, 'nombre' => 'Radiografía Tórax'], ['id' => 3, 'nombre' => 'Resonancia Magnética']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediFlow - Solicitudes Médicas</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .container-ancho { max-width: 95%; margin: 30px auto; padding: 0 20px; display: flex; flex-direction: column; gap: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; align-items: end; }
        .badge { padding: 4px 8px; border-radius: 12px; font-weight: bold; font-size: 12px; text-transform: capitalize; }
        .badge-pendiente { background: #fff3cd; color: #856404; }
        .badge-aceptada { background: #d4edda; color: #155724; }
        .badge-rechazada { background: #f8d7da; color: #721c24; }
        .badge-observada { background: #e0f2fe; color: #0369a1; }
        .btn-aprobar { background-color: #28a745; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 12px; margin-right: 2px; display: inline-block; }
        .btn-rechazar { background-color: #dc3545; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 12px; margin-right: 2px; display: inline-block; }
        .btn-observar { background-color: #0284c7; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 12px; display: inline-block; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>MediFlow <span>• Módulo Solicitudes (Rol: <?php echo htmlspecialchars($rolUsuarioOriginal); ?>)</span></h1>
        <a href="../index.php" class="btn-back"> Volver al Inicio</a>
    </div>

    <div class="container-ancho">
        
        <?php if ($rolNormalizado == 'medico' || $rolNormalizado == 'admin'): ?>
        <div class="card">
            <h2> Cargar Nueva Solicitud Médica</h2>
            <form action="../controlador/solicitudControlador.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Afiliado (Paciente)</label>
                        <select name="id_paciente" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            <option value="">-- Seleccione un Paciente --</option>
                            <?php foreach ($listaPacientes as $p): ?>
                                <option value="<?php echo $p['id_paciente']; ?>">
                                    <?php echo htmlspecialchars($p['apellido'] . ', ' . $p['nombre'] . ' (DNI: ' . $p['dni'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Médico Solicitante</label>
                        <select name="id_medico" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            <option value="">-- Seleccione un Médico --</option>
                            <?php foreach ($medicosDemo as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo $m['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Práctica Requerida</label>
                        <select name="id_practica" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            <option value="">-- Seleccione Práctica --</option>
                            <?php foreach ($practicasDemo as $prac): ?>
                                <option value="<?php echo $prac['id']; ?>"><?php echo $prac['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Solicitud</label>
                        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                    </div>
                    <div class="form-group">
                        <label>Prioridad</label>
                        <select name="prioridad" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            <option value="Baja">Baja / Rutina</option>
                            <option value="Media" selected>Media</option>
                            <option value="Alta">Alta / Urgente</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Diagnóstico (Justificación Clínica)</label>
                        <input type="text" name="diagnostico" placeholder="Ej: Justificación clínica para la auditoría..." required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-submit" style="height: 40px;">Cargar Solicitud</button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2> Padrón y Consulta de Solicitudes</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>N° Solicitud</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>DNI</th>
                            <th>Práctica</th>
                            <th>Diagnóstico</th>
                            <th>Prioridad</th>
                            <th style="text-align: center;">Estado</th>
                            <?php if ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin'): ?>
                                <th style="text-align: center;">Acción Auditoría</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaSolicitudes)): ?>
                            <tr>
                                <td colspan="<?php echo ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin') ? '9' : '8'; ?>" class="no-data">No hay solicitudes médicas registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaSolicitudes as $s): 
                          $estadoNormalizado = strtolower(trim($s['estado']));
    
                          $claseBadge = 'badge-pendiente';
                          // Validamos si es aprobada o aceptada
                          if($estadoNormalizado == 'aprobada' || $estadoNormalizado == 'aceptada') $claseBadge = 'badge-aceptada';
                          if($estadoNormalizado == 'rechazada') $claseBadge = 'badge-rechazada';
                          if($estadoNormalizado == 'observada') $claseBadge = 'badge-observada';
                            ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($s['id_solicitud']); ?></strong></td>
                                    <td><?php echo date("d/m/Y", strtotime($s['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars(($s['apellido_paciente'] ?? 'N/A') . ', ' . ($s['nombre_paciente'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($s['dni'] ?? 'N/A'); ?></td>
                                    <td>Práctica #<?php echo htmlspecialchars($s['id_practica']); ?></td>
                                    <td><?php echo htmlspecialchars($s['diagnostico']); ?></td>
                                    <td style="text-transform: capitalize;"><?php echo htmlspecialchars($s['prioridad']); ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge <?php echo $claseBadge; ?>"><?php echo htmlspecialchars($s['estado']); ?></span>
                                    </td>
                                    
                                    <?php if ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin'): ?>
                                        <td style="text-align: center; white-space: nowrap;">
                                            <?php if ($estadoNormalizado == 'pendiente' || $estadoNormalizado == 'observada'): ?>
                                            <a href="../controlador/solicitudControlador.php?accion=auditar&id=<?php echo $s['id_solicitud']; ?>&estado=aprobada" class="btn-aprobar" onclick="return confirm('¿Aprobar solicitud?');">Aceptar</a>
    
                                            <a href="../controlador/solicitudControlador.php?accion=auditar&id=<?php echo $s['id_solicitud']; ?>&estado=rechazada" class="btn-rechazar" onclick="return confirm('¿Rechazar solicitud?');">Rechazar</a>
    
                                            <a href="../controlador/solicitudControlador.php?accion=auditar&id=<?php echo $s['id_solicitud']; ?>&estado=observada" class="btn-observar" onclick="return confirm('¿Marcar como observada para corregir datos?');">Observar</a>
                                            <?php else: ?>
                                                <span style="color:#aaa; font-size:12px;">Finalizada</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>