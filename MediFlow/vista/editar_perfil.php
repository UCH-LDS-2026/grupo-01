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
    // Asegúrate de que esta función traiga nro_afiliado, obra social, plan, etc.
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
        .form-box { background: white; width: 100%; max-width: 550px; padding: 35px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { color: #0c4a6e; margin-top: 0; margin-bottom: 25px; font-size: 24px; }
        
        /* Estilos de la Ficha Informativa (Solo lectura) */
        .info-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .info-section h3 { margin-top: 0; font-size: 14px; color: #0284c7; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .info-item { display: flex; flex-direction: column; background: #ffffff; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .info-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; }
        .info-value { font-size: 14px; color: #1e293b; font-weight: 500; }

        /* Estilos de inputs editables */
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; color: #475569; font-weight: 600; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: all 0.2s; outline: none; }
        input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
        
        .btn-submit { width: 100%; background-color: #0c4a6e; color: white; padding: 14px; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; transition: background-color 0.2s; margin-top: 10px; }
        .btn-submit:hover { background-color: #075985; }
        .btn-volver { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-weight: 600; font-size: 14px; transition: color 0.2s; }
        .btn-volver:hover { color: #0c4a6e; }
    </style>
</head>
<body>
    <div class="form-box">
        <a href="../index.php" class="btn-volver">← Volver al Dashboard</a>
        <h2>✏️ Mi Perfil</h2>
        
        <form action="actualizar_perfil.php" method="POST">
            <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rolNormalizado); ?>">
            <?php if ($rolNormalizado == 'paciente' && $datosPaciente): ?>
                <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($datosPaciente['id_paciente']); ?>">
            <?php endif; ?>
            
            <!-- TARJETA DE DATOS FIJOS (NO EDITABLES) -->
            <div class="info-section">
                <h3>🛡️ Información de Afiliación</h3>
                <div class="info-grid">
                    
                    <!-- Nombre y Apellido separados -->
                    <div class="info-item">
                        <span class="info-label">Nombre</span>
                        <span class="info-value"><?php echo htmlspecialchars($usuario_sesion['nombre']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Apellido</span>
                        <span class="info-value"><?php echo htmlspecialchars($usuario_sesion['apellido']); ?></span>
                    </div>
                    
                    <?php if ($rolNormalizado == 'paciente' && $datosPaciente): ?>
                    <!-- DNI y Nro Afiliado -->
                    <div class="info-item">
                        <span class="info-label">DNI</span>
                        <span class="info-value"><?php echo htmlspecialchars($datosPaciente['dni'] ?? '---'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nro. de Afiliado</span>
                        <span class="info-value"><?php echo htmlspecialchars($datosPaciente['nro_afiliado'] ?? '---'); ?></span>
                    </div>
                    
                    <!-- Obra Social y Plan -->
                    <div class="info-item">
                        <span class="info-label">Obra Social</span>
                        <span class="info-value"><?php echo htmlspecialchars($datosPaciente['obra_social'] ?? '---'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Plan / Cobertura</span>
                        <span class="info-value"><?php echo htmlspecialchars($datosPaciente['plan'] ?? '---'); ?></span>
                    </div>

                    <!-- Fecha de Alta -->
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <span class="info-label">Fecha de Alta</span>
                        <span class="info-value">
                            <?php 
                            echo !empty($datosPaciente['fecha_alta']) 
                                 ? date('d/m/Y', strtotime($datosPaciente['fecha_alta'])) 
                                 : '---'; 
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- CAMPOS EDITABLES -->
            <div class="form-group">
                <label>Correo Electrónico (Para recibir notificaciones)</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email_usuario); ?>" required>
            </div>

            <?php if ($rolNormalizado == 'paciente' && $datosPaciente): ?>
            <div class="form-group">
                <label>Teléfono de Contacto</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($datosPaciente['telefono'] ?? ''); ?>" required>
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn-submit">Guardar Datos de Contacto</button>
        </form>
    </div>
</body>
</html>