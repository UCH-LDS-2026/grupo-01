<?php
session_start();
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));
if ($rol != 'auditor' && $rol != 'admin') {
    header("Location: ../index.php");
    exit;
}
$id_solicitud = $_GET['id'] ?? null;
$archivo = $_GET['archivo'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluar Solicitud</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Auditoría de Solicitud #<?php echo htmlspecialchars($id_solicitud); ?></span></h1>
        <a href="solicitudes.php" class="btn-back">⬅ Volver</a>
    </div>

    <div class="container-ancho" style="max-width: 600px;">
        <div class="card">
            <h2> Dictamen de Auditoría</h2>
            
            <?php if (!empty($archivo)): ?>
                <div style="margin-bottom: 20px; padding: 10px; background: #e0f2fe; border-radius: 4px;">
                    <strong> Documento Adjunto:</strong> 
                    <a href="../uploads/<?php echo htmlspecialchars($archivo); ?>" target="_blank" class="btn-archivo" style="background: #0284c7; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px;">Ver Archivo</a>
                </div>
            <?php endif; ?>

            <form action="../controlador/evaluacionControlador.php" method="POST">
                <input type="hidden" name="id_solicitud" value="<?php echo htmlspecialchars($id_solicitud); ?>">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Resolución</label>
                    <select name="estado_nuevo" required style="width:100%; padding:10px; border-radius:4px;">
                        <option value="">-- Seleccionar --</option>
                        <option value="aprobada"> Aprobar</option>
                        <option value="rechazada"> Rechazar</option>
                        <option value="observada"> Observar (Pedir corrección)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="4" required style="width:100%; padding:10px; border-radius:4px;"></textarea>
                </div>

                <button type="submit" class="btn btn-submit" style="width:100%; height: 45px; font-size: 16px;">Guardar Evaluación</button>
            </form>
        </div>
    </div>
</body>
</html>