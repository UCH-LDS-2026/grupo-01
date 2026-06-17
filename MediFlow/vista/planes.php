<?php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Plan.php';

$planModelo = new Plan($conexion);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['nombre'])) {
    $planModelo->crear($_POST['nombre']);
    header("Location: planes.php"); exit;
}
if (isset($_GET['del'])) {
    $planModelo->eliminar($_GET['del']);
    header("Location: planes.php"); exit;
}

$planes = $planModelo->listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Planes - MediFlow</title>
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; margin: 0; }
        
        .navbar { display: flex; justify-content: space-between; align-items: center; background-color: #0c4a6e; padding: 15px 30px; color: white; }
        .navbar h1 { margin: 0; font-size: 20px; font-weight: 600; }
        .btn-back { color: white; text-decoration: none; font-size: 14px; background: rgba(255,255,255,0.1); padding: 8px 15px; border-radius: 6px; transition: background 0.2s; }
        .btn-back:hover { background: rgba(255,255,255,0.2); }

        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        
        .panel-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .panel-header { font-size: 18px; font-weight: 600; color: #0c4a6e; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        
        .form-row { display: flex; gap: 15px; align-items: center; }
        input[type="text"] { flex-grow: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; transition: all 0.2s; }
        input[type="text"]:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
        
        .btn-add { background-color: #0ea5e9; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background-color 0.2s; white-space: nowrap; }
        .btn-add:hover { background-color: #0284c7; }

        .styled-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .styled-table th { background-color: #f8fafc; color: #475569; font-weight: 600; text-align: left; padding: 15px; border-bottom: 2px solid #e2e8f0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .styled-table td { padding: 15px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-size: 14px; }
        .styled-table tr:hover { background-color: #f1f5f9; }
        
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 600; padding: 8px 12px; background: #fee2e2; border-radius: 6px; font-size: 13px; transition: all 0.2s; }
        .btn-delete:hover { background: #fca5a5; color: #b91c1c; }
        .empty-state { text-align: center; padding: 30px; color: #64748b; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Gestión de Planes Médicos</span></h1>
        <a href="../index.php" class="btn-back">← Volver al Inicio</a>
    </div>

    <div class="container">
        
        <div class="panel-box">
            <div class="panel-header"> Registrar Nuevo Plan</div>
            <form method="POST" class="form-row">
                <input type="text" name="nombre" placeholder="Ej: PMO inicial, Plenitud200, MediPro..." required autocomplete="off">
                <button type="submit" class="btn-add">Agregar Plan</button>
            </form>
        </div>

        <div class="panel-box" style="padding: 0; overflow: hidden;">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 60%;">Nombre del Plan de Cobertura</th>
                        <th style="width: 30%; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($planes)): ?>
                        <tr><td colspan="3" class="empty-state">No hay planes registrados en la base de datos.</td></tr>
                    <?php else: ?>
                        <?php foreach($planes as $p): ?>
                            <tr>
                                <td><strong style="color:#94a3b8;">#<?php echo htmlspecialchars($p['id_plan']); ?></strong></td>
                                <td style="font-weight: 500; color:#0c4a6e;"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td style="text-align: right;">
                                    <a href="planes.php?del=<?php echo $p['id_plan']; ?>" class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminar el plan <?php echo htmlspecialchars($p['nombre']); ?>?');"> Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>