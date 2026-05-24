<?php
session_start();
if (!isset($_SESSION['usuario']) || strtolower(trim($_SESSION['usuario']['rol'])) != 'paciente') { header("Location: login.php"); exit; }

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);
$datosPaciente = $solicitudModelo->obtenerDatosPacientePorEmail($_SESSION['usuario']['email']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Credencial - MediFlow</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .credencial-box { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; width: 400px; padding: 30px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative; overflow: hidden; }
        .credencial-box::after { content: ''; position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%; }
        .logo { font-size: 24px; font-weight: bold; color: #38bdf8; margin-bottom: 30px; }
        h2 { margin: 0 0 5px 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1px; }
        .data-row { margin-top: 15px; font-size: 15px; color: #cbd5e1; }
        .data-row strong { color: white; }
        .chip { width: 45px; height: 35px; background: #eab308; border-radius: 6px; margin-bottom: 20px; opacity: 0.8; }
        .btn-volver { position: absolute; top: 20px; left: 20px; color: #0c4a6e; text-decoration: none; font-weight: bold; background: white; padding: 8px 15px; border-radius: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <a href="../index.php" class="btn-volver">← Volver</a>
    <div class="credencial-box">
        <div class="logo">MediFlow</div>
        <div class="chip"></div>
        <h2><?php echo htmlspecialchars($datosPaciente['nombre'] . ' ' . $datosPaciente['apellido']); ?></h2>
        <div class="data-row"><strong>N° Afiliado:</strong> <?php echo htmlspecialchars($datosPaciente['nro_afiliado'] ?? '---'); ?></div>
        <div class="data-row"><strong>DNI:</strong> <?php echo htmlspecialchars($datosPaciente['dni']); ?></div>
        <div class="data-row"><strong>Plan:</strong> <?php echo htmlspecialchars($datosPaciente['plan'] ?? 'Base'); ?></div>
        <div class="data-row"><strong>Válido:</strong> Activo</div>
    </div>
</body>
</html>