<?php
session_start();

if (!isset($_SESSION['usuario'])) { 
    header("Location: login.php"); 
    exit; 
}

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$usuario_sesion = $_SESSION['usuario'];
$rolNormalizado = strtolower(trim($usuario_sesion['rol']));
$email_usuario = $usuario_sesion['email'];

$datosPaciente = null;
if ($rolNormalizado == 'paciente') {
    $solicitudModelo = new Solicitud($conexion);
    $datosPaciente = $solicitudModelo->obtenerDatosPacientePorEmail($email_usuario);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - MediFlow</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .form-box { background: white; width: 100%; max-width: 450px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { color: #0b5687; margin-top: 0; margin-bottom: 25px; font-size: 24px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 14px; color: #475569; font-weight: 600; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: border-color 0.2s; }
        input:focus { border-color: #0b5687; outline: none; }
        input:disabled { background-color: #f1f5f9; color: #94a3b8; cursor: not-allowed; border-color: #e2e8f0; }
        .btn-submit { width: 100%; background: linear-gradient(135deg, #0b5687, #0a4a73); color: white; padding: 14px; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; transition: transform 0.2s; margin-top: 10px; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11,86,135,0.2); }
        .btn-volver { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-volver:hover { color: #0b5687; }
        .row-inputs { display: flex; gap: 15px; }
        .row-inputs .form-group { flex: 1; }
    </style>
</head>
<body>
    <div class="form-box">
        <a href="../index.php" class="btn-volver">← Volver al Dashboard</a>
        <h2>✏️ Editar Mis Datos</h2>
        
        <form action="actualizar_perfil.php" method="POST">
            <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rolNormalizado); ?>">
            <?php if ($rolNormalizado == 'paciente' && $datosPaciente): ?>
                <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($datosPaciente['id_paciente']); ?>">
            <?php endif; ?>
            
            <div class="row-inputs">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario_sesion['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($usuario_sesion['apellido']); ?>" required>
                </div>
            </div>
            
            <?php if ($rolNormalizado == 'paciente' && $datosPaciente): ?>
            <div class="form-group">
                <label>DNI (No editable)</label>
                <input type="text" value="<?php echo htmlspecialchars($datosPaciente['dni']); ?>" disabled>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email_usuario); ?>" required>
            </div>

            <?php if ($rolNormalizado == 'paciente' && $datosPaciente): ?>
            <div class="form-group">
                <label>Teléfono de Contacto</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($datosPaciente['telefono'] ?? ''); ?>" required>
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>