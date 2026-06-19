<?php
session_start();
// Control de seguridad por Rol
if (strtolower(trim($_SESSION['usuario']['rol'] ?? '')) != 'medico') {
    header("Location: ../index.php");
    exit;
}
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);
$id_medico = $_SESSION['usuario']['id_usuario'];
$observadas = $solicitudModelo->obtenerObservadasPorMedico($id_medico);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - Correcciones Pendientes</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Correcciones Solicitadas</span></h1>
        <a href="../index.php" class="btn-back"> Volver al Inicio</a>
    </div>

    <div class="container-ancho">
        <div class="card">
            <h2> Solicitudes Devueltas por Auditoría</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Paciente</th>
                            <th>Motivo del Auditor (Lo que hay que arreglar)</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($observadas)): ?>
                            <tr><td colspan="4" style="text-align:center; padding: 25px;">No tenés solicitudes para corregir. ¡Excelente!</td></tr>
                        <?php else: ?>
                            <?php foreach ($observadas as $obs): ?>
                                <tr>
                                    <td><strong>#<?php echo $obs['id_solicitud']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($obs['apellido_paciente'] . ', ' . $obs['nombre_paciente']); ?></td>
                                    <td style="color: #b45309; font-weight: bold;">
                                        "<?php echo htmlspecialchars($obs['motivo_correccion'] ?? 'No se especificaron observaciones adicionales.'); ?>"
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="ver_solicitud.php?id=<?php echo $obs['id_solicitud']; ?>" target="_blank" class="btn-observar" style="background-color: #f59e0b; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold;">Corregir Solicitud</a>
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