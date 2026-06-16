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
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Gestión de Planes</title>
</head>
<body>
    <div class="container" style="max-width: 600px; margin-top: 30px;">
        <h2>Gestión de Planes</h2>
        <form method="POST" class="card" style="margin-bottom:20px; display:flex; gap:10px;">
            <input type="text" name="nombre" placeholder="Nombre del nuevo plan" required style="flex-grow:1;">
            <button type="submit" class="btn">Agregar</button>
        </form>
        
        <table class="card" style="width: 100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Plan</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($planes as $p): ?>
                    <tr>
                        <td><?php echo $p['id_plan']; ?></td>
                        <td><?php echo $p['nombre']; ?></td>
                        <td><a href="planes.php?del=<?php echo $p['id_plan']; ?>" style="color:red;">Eliminar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <a href="../index.php">← Volver al inicio</a>
    </div>
</body>
</html>