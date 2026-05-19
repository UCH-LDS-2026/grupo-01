<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// SEGURIDAD: Solo el Administrador puede entrar
$rolUsuario = $_SESSION['usuario']['rol'] ?? '';
$rolNormalizado = strtolower(trim($rolUsuario));

if (!isset($_SESSION['usuario']) || ($rolNormalizado != 'admin' && $rolNormalizado != 'administrador')) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Practica.php';

/** @var mysqli $conexion */
$practicaModelo = new Practica($conexion);
$listaPracticas = $practicaModelo->listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediFlow - Gestión de Prácticas</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .container-ancho { max-width: 95%; margin: 30px auto; padding: 0 20px; display: flex; flex-direction: column; gap: 30px; }
        .form-inline { display: flex; gap: 15px; align-items: end; }
        .form-group-flex { flex: 1; display: flex; flex-direction: column; gap: 5px; }
        .btn-eliminar { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>MediFlow <span>• Catálogo de Prácticas Médicas</span></h1>
        <a href="../index.php" class="btn-back"> Volver al Inicio</a>
    </div>

    <div class="container-ancho">
        
        <div class="card">
            <h2> Registrar Nueva Práctica O Nomenclador</h2>
            <form action="../controlador/practicaControlador.php" method="POST" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                <input type="hidden" name="accion" value="crear">
                
                <div style="flex-grow: 1; min-width: 250px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: bold; white-space: nowrap;">Nombre de la Práctica Médica</label>
                    <input type="text" name="nombre" required placeholder="Ej: Ecografía Abdominal, Tomografía Computada..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div>
                    <button type="submit" class="btn btn-submit" style="height: 42px; padding: 0 30px; white-space: nowrap; width: max-content;">Agregar Práctica</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2> Listado de Prácticas Autorizables</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Código</th>
                            <th>Nombre de la Prestación</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaPracticas)): ?>
                            <tr>
                                <td colspan="3" class="no-data">No hay prácticas configuradas en el nomenclador.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaPracticas as $p): ?>
                                <tr>
                                    <td><strong>#<?php echo $p['id_practica']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td style="text-align: center;">
                                        <a href="../controlador/practicaControlador.php?accion=eliminar&id=<?php echo $p['id_practica']; ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar esta práctica? Ya no se podrá seleccionar en nuevas solicitudes.');">Quitar</a>
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