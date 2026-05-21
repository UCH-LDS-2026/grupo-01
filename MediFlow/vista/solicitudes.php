<?php
session_start();
$rol = $_SESSION['usuario']['rol'] ?? '';
$rolNormalizado = strtolower(trim($rol));

if (!$rol) { header("Location: login.php"); exit; }

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);
$solicitudes = $solicitudModelo->listar();

// Obtener datos para desplegables
$pacientes = $conexion->query("SELECT * FROM paciente")->fetch_all(MYSQLI_ASSOC);
$medicos = $conexion->query("SELECT * FROM usuario WHERE LOWER(rol) IN ('medico', 'médico', 'admin', 'administrador')")->fetch_all(MYSQLI_ASSOC);
$practicas = $conexion->query("SELECT * FROM practica")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Solicitudes</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f4f6f9; }
        .panel-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .panel-header { font-size: 18px; font-weight: bold; color: #0c4a6e; margin-bottom: 15px; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; }
        .horizontal-form { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; }
        .form-col { flex: 1; min-width: 150px; display: flex; flex-direction: column; }
        .form-col label { font-size: 11px; color: #555; margin-bottom: 5px; font-weight: bold; }
        .form-col input, .form-col select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; width: 100%; box-sizing: border-box; }
        .btn-cargar { background-color: #0c4a6e; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 5px; font-size: 14px; }
        .btn-ver { background-color: #0f766e; color: white; padding: 4px 10px; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: bold; }
        .table-responsive table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table-responsive th, .table-responsive td { padding: 12px 10px; border-bottom: 1px solid #eee; text-align: left; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Módulo Solicitudes</span></h1>
        <a href="../index.php" class="btn-back">Volver al Inicio</a>
    </div>

    <div class="container-ancho">
        <?php if ($rolNormalizado == 'medico' || $rolNormalizado == 'admin' || $rolNormalizado == 'administrador'): ?>
        <div class="panel-box">
            <div class="panel-header">Cargar Nueva Solicitud Médica</div>
            <form action="../controlador/solicitudControlador.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">
                <div class="horizontal-form">
                    <div class="form-col">
                        <label>Afiliado (Paciente)</label>
                        <select name="id_paciente" required>
                            <option value="">-- Seleccione un Paciente --</option>
                            <?php foreach($pacientes as $p): ?>
                                <option value="<?php echo $p['id_paciente']; ?>"><?php echo htmlspecialchars($p['apellido'] . ', ' . $p['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label>Médico Solicitante</label>
                        <select name="id_medico" required>
                            <option value="">-- Seleccione un Médico --</option>
                            <?php foreach($medicos as $m): ?>
                                <option value="<?php echo $m['id_usuario']; ?>"><?php echo htmlspecialchars($m['apellido'] . ', ' . $m['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label>Práctica Requerida</label>
                        <select name="id_practica" required>
                            <option value="">-- Seleccione Práctica --</option>
                            <?php foreach($practicas as $pr): ?>
                                <option value="<?php echo $pr['id_practica']; ?>"><?php echo htmlspecialchars($pr['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label>Fecha de Solicitud</label>
                        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-col">
                        <label>Prioridad</label>
                        <select name="prioridad" required>
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label>Estudio / Receta Adjunta</label>
                        <input type="file" name="adjunto" accept=".jpg,.jpeg,.png,.pdf" style="padding: 5px;">
                    </div>
                </div>
                <div class="form-col" style="margin-bottom: 15px;">
                    <label>Diagnóstico (Justificación Clínica)</label>
                    <input type="text" name="diagnostico" required placeholder="Ej: Justificación clínica para la auditoría..." style="width: 100%;">
                </div>
                <button type="submit" class="btn-cargar">Cargar Solicitud</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="panel-box">
            <div class="panel-header">Padrón y Consulta de Solicitudes</div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>N°</th><th>Fecha</th><th>Paciente</th><th>DNI</th><th>Práctica</th><th>Diagnóstico</th><th>Documento</th><th>Prioridad</th><th>Estado</th>
                            <?php if ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin' || $rolNormalizado == 'administrador'): ?>
                                <th style="text-align: center;">Acción Auditoría</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($solicitudes)): ?>
                            <tr><td colspan="10" style="text-align:center; padding: 20px;">No hay solicitudes registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach ($solicitudes as $s): 
                                $estadoNormalizado = strtolower(trim($s['estado']));
                                $archivoAdjunto = $s['ruta_archivo'] ?? '';
                            ?>
                            <tr>
                                <td><strong>#<?php echo $s['id_solicitud']; ?></strong></td>
                                <td><?php echo isset($s['fecha']) ? date("d/m/Y", strtotime($s['fecha'])) : date("d/m/Y"); ?></td>
                                <td><?php echo htmlspecialchars($s['apellido_paciente'] . ', ' . $s['nombre_paciente']); ?></td>
                                <td><?php echo htmlspecialchars($s['dni'] ?? '---'); ?></td>
                                <td><?php echo htmlspecialchars($s['nombre_practica'] ?? '---'); ?></td>
                                <td><?php echo htmlspecialchars($s['diagnostico']); ?></td>
                                <td>
                                    <?php if (!empty($archivoAdjunto)): ?>
                                        <a href="../uploads/<?php echo htmlspecialchars($archivoAdjunto); ?>" target="_blank" class="btn-ver">👁 Ver Adjunto</a>
                                    <?php else: ?>
                                        <span style="color:#999; font-size: 11px;">Sin archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo ucfirst($s['prioridad']); ?></strong></td>
                                <td>
                                    <span style="text-transform: capitalize; font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 11px;
                                        <?php if ($estadoNormalizado == 'observada') echo 'background: #fef3c7; color: #92400e;'; else echo 'background: #fef08a; color: #854d0e;'; ?>">
                                        <?php echo htmlspecialchars($s['estado']); ?>
                                    </span>
                                </td>
                                <?php if ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin' || $rolNormalizado == 'administrador'): ?>
                                    <td style="text-align: center;">
                                        <?php if ($estadoNormalizado == 'pendiente' || $estadoNormalizado == 'observada'): ?>
                                            <a href="evaluar.php?id=<?php echo $s['id_solicitud']; ?>" style="background-color: #0284c7; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 11px;">Evaluar</a>
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