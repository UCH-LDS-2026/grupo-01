<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// validar.php - Se ubica en la raíz de MediFlow
require_once __DIR__ . '/config/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Buscamos si la solicitud existe
$sql = "SELECT s.id_solicitud, s.estado, p.nombre, p.apellido, p.dni 
        FROM solicitud s 
        JOIN paciente p ON s.id_paciente = p.id_paciente 
        WHERE s.id_solicitud = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Autorización</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 90%; }
        .icon { font-size: 60px; margin-bottom: 20px; }
        .valido { color: #166534; }
        .invalido { color: #991b1b; }
        h1 { margin: 0 0 10px 0; font-size: 24px; }
        p { color: #475569; font-size: 16px; line-height: 1.5; }
        .datos { background: #f1f5f9; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: left; }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($res && strtolower(trim($res['estado'])) == 'aprobada'): ?>
            <div class="icon valido">✅</div>
            <h1 class="valido">Autorización Válida</h1>
            <p>Este documento es auténtico y se encuentra <b>Aprobado</b> por la obra social.</p>
            <div class="datos">
                <strong>N° Solicitud:</strong> #<?php echo $res['id_solicitud']; ?><br>
                <strong>Paciente:</strong> <?php echo htmlspecialchars($res['nombre'] . ' ' . $res['apellido']); ?><br>
                <strong>DNI:</strong> <?php echo htmlspecialchars($res['dni']); ?>
            </div>
        <?php else: ?>
            <div class="icon invalido">❌</div>
            <h1 class="invalido">Autorización Inválida</h1>
            <p>Este documento <b>NO</b> está aprobado, fue rechazado o el código es falso.</p>
        <?php endif; ?>
    </div>
</body>
</html>