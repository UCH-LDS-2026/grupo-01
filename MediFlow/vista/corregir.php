<?php
session_start();
if (strtolower(trim($_SESSION['usuario']['rol'] ?? '')) != 'medico') {
    header("Location: ../index.php");
    exit;
}
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);
$id_solicitud = $_GET['id'] ?? null;
$solicitud = $solicitudModelo->obtenerPorId($id_solicitud);

if (!$solicitud) {
    echo "❌ Solicitud no encontrada.";
    exit;
}

// Consultas para rellenar los selectores e identificar lo cargado previamente
$pacientes = $conexion->query("SELECT id_paciente, nombre, apellido FROM paciente")->fetch_all(MYSQLI_ASSOC);
$medicos = $conexion->query("SELECT id_usuario, nombre, apellido FROM usuario WHERE LOWER(rol) IN ('medico', 'médico')")->fetch_all(MYSQLI_ASSOC);
$practicas = $conexion->query("SELECT id_practica, nombre FROM practica")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corregir Solicitud Médica</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f4f6f9; }
        .panel-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px; margin: 30px auto; max-width: 1100px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .panel-header { font-size: 18px; font-weight: bold; color: #b45309; margin-bottom: 15px; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; }
        .horizontal-form { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; }
        .form-col { flex: 1; min-width: 150px; display: flex; flex-direction: column; }
        .form-col label { font-size: 11px; color: #555; margin-bottom: 5px; font-weight: bold; }
        .form-col input, .form-col select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; width: 100%; box-sizing: border-box; }
        .btn-corregir { background-color: #f59e0b; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; width: 100%; text-transform: uppercase; }
        .btn-corregir:hover { background-color: #d97706; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Panel de Corrección de Trámites</span></h1>
        <a href="notificaciones.php" class="btn-back">⬅ Volver a Notificaciones</a>
    </div>

    <div class="container-ancho">
        <div class="panel-box" style="border-top: 4px solid #f59e0b;">
            <div class="panel-header">Modificar y Reenviar Solicitud #<?php echo htmlspecialchars($id_solicitud); ?></div>
            
            <form action="../controlador/solicitudControlador.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="corregir">
                <input type="hidden" name="id_solicitud" value="<?php echo htmlspecialchars($id_solicitud); ?>">
                
                <div class="horizontal-form">
                    <div class="form-col">
                        <label>Afiliado (Paciente)</label>
                        <select name="id_paciente" required>
                            <?php foreach($pacientes as $p): ?>
                                <option value="<?php echo $p['id_paciente']; ?>" <?php if($p['id_paciente'] == $solicitud['id_paciente']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($p['apellido'] . ', ' . $p['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-col">
                        <label>Médico Solicitante</label>
                        <select name="id_medico" required>
                            <?php foreach($medicos as $m): ?>
                                <option value="<?php echo $m['id_usuario']; ?>" <?php if($m['id_usuario'] == $solicitud['id_medico']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($m['apellido'] . ', ' . $m['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-col">
                        <label>Práctica Requerida</label>
                        <select name="id_practica" required>
                            <?php foreach($practicas as $pr): ?>
                                <option value="<?php echo $pr['id_practica']; ?>" <?php if($pr['id_practica'] == $solicitud['id_practica']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($pr['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-col">
                        <label>Fecha de Solicitud</label>
                        <input type="date" name="fecha" value="<?php echo htmlspecialchars($solicitud['fecha']); ?>" required>
                    </div>

                    <div class="form-col">
                        <label>Prioridad</label>
                        <select name="prioridad" required>
                            <option value="baja" <?php if(strtolower($solicitud['prioridad']) == 'baja') echo 'selected'; ?>>Baja</option>
                            <option value="media" <?php if(strtolower($solicitud['prioridad']) == 'media') echo 'selected'; ?>>Media</option>
                            <option value="alta" <?php if(strtolower($solicitud['prioridad']) == 'alta') echo 'selected'; ?>>Alta</option>
                        </select>
                    </div>

                    <div class="form-col">
                        <label>Estudio / Receta Adjunta (Opcional - Reemplaza anterior)</label>
                        <input type="file" name="adjunto" accept=".jpg,.jpeg,.png,.pdf" style="padding: 5px;">
                    </div>
                </div>

                <div class="form-col" style="margin-bottom: 20px;">
                    <label>Diagnóstico (Justificación Clínica a corregir)</label>
                    <input type="text" name="diagnostico" value="<?php echo htmlspecialchars($solicitud['diagnostico']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <button type="submit" class="btn-corregir">Reenviar a Auditoría Médica</button>
            </form>
        </div>
    </div>
</body>
</html>