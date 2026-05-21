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
require_once __DIR__ . '/../modelo/Usuario.php';   
require_once __DIR__ . '/../modelo/Practica.php';  

/** @var mysqli $conexion */
$solicitudModelo = new Solicitud($conexion);
$pacienteModelo = new Paciente($conexion);
$usuarioModelo = new Usuario($conexion);
$practicaModelo = new Practica($conexion);

$listaSolicitudes = $solicitudModelo->listar();
$listaPacientes = $pacienteModelo->listar(); 
$listaMedicos = $usuarioModelo->listarMedicos(); 
$listaPracticas = $practicaModelo->listar();   
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
        .btn-archivo { background-color: #0f766e; color: white; padding: 3px 6px; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: bold; display: inline-block; }
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
            <form action="../controlador/solicitudControlador.php" method="POST" enctype="multipart/form-data">
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
                            <?php foreach ($listaMedicos as $m): ?>
                                <option value="<?php echo $m['id_usuario']; ?>">
                                    <?php echo htmlspecialchars($m['apellido'] . ', ' . $m['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Práctica Requerida</label>
                        <select name="id_practica" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            <option value="">-- Seleccione Práctica --</option>
                            <?php foreach ($listaPracticas as $prac): ?>
                                <option value="<?php echo $prac['id_practica']; ?>">
                                    <?php echo htmlspecialchars($prac['nombre']); ?>
                                </option>
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

                    <div class="form-group">
                        <label>Estudio / Receta Adjunta (PDF, Imagen)</label>
                        <input type="file" name="adjunto" accept=".pdf,.png,.jpg,.jpeg" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; background:white;">
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
                            <th>N°</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>DNI</th>
                            <th>Práctica</th>
                            <th>Diagnóstico</th>
                            <th style="text-align: center;">Documento</th>
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
                                <td colspan="<?php echo ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin') ? '10' : '9'; ?>" class="no-data">No hay solicitudes médicas registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaSolicitudes as $s): 
                                $estadoNormalizado = strtolower(trim($s['estado']));
                                $claseBadge = 'badge-pendiente';
                                if($estadoNormalizado == 'aprobada' || $estadoNormalizado == 'aceptada') $claseBadge = 'badge-aceptada';
                                if($estadoNormalizado == 'rechazada') $claseBadge = 'badge-rechazada';
                                if($estadoNormalizado == 'observada') $claseBadge = 'badge-observada';
                                
                                $archivoAdjunto = $s['ruta_archivo'] ?? '';
                            ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($s['id_solicitud']); ?></strong></td>
                                    <td><?php echo date("d/m/Y", strtotime($s['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars(($s['apellido_paciente'] ?? 'N/A') . ', ' . ($s['nombre_paciente'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($s['dni'] ?? 'N/A'); ?></td>
                                    <td>Práctica #<?php echo htmlspecialchars($s['id_practica']); ?></td>
                                    <td><?php echo htmlspecialchars($s['diagnostico']); ?></td>
                                    
                                    <td style="text-align: center;">
                                        <?php if (!empty($archivoAdjunto)): ?>
                                            <a href="../uploads/<?php echo htmlspecialchars($archivoAdjunto); ?>" target="_blank" class="btn-archivo">📎 Ver Adjunto</a>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 11px; font-style: italic;">Sin archivo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td style="text-transform: capitalize;"><?php echo htmlspecialchars($s['prioridad']); ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge <?php echo $claseBadge; ?>"><?php echo htmlspecialchars($s['estado']); ?></span>
                                    </td>
                                    
                                   <?php if ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin'): ?>
    <td style="text-align: center;">
        <?php if ($estadoNormalizado == 'pendiente' || $estadoNormalizado == 'observada'): ?>
            <a href="evaluar.php?id=<?php echo $s['id_solicitud']; ?>&archivo=<?php echo urlencode($archivoAdjunto); ?>" class="btn-observar" style="background-color: #0284c7; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold;">Evaluar Solicitud</a>
        <?php else: ?>
            <span style="color:#aaa; font-size:12px; font-weight: bold;">Auditada</span>
        <?php endif; ?>
    </td>
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
</body>
</html>