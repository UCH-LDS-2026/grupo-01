<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    $nombre = isset($usuario['nombre']) ? $usuario['nombre'] : 'Usuario';
    $apellido = isset($usuario['apellido']) ? $usuario['apellido'] : '';
    $email = isset($usuario['email']) ? $usuario['email'] : 'Sin email';
    $rol = isset($usuario['rol']) ? $usuario['rol'] : 'Sin rol';
} else {
    session_destroy();
    header("Location: /mediflow/grupo-01/MediFlow/vista/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediFlow - Panel de Control</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Pequeños estilos específicos que solo usa la cuadrícula de botones del Inicio */
        .grid-inicio {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 30px;
        }
        @media (max-width: 800px) { .grid-inicio { grid-template-columns: 1fr; } }
        
        .profile-card { text-align: center; }
        .avatar {
            width: 80px; height: 80px; background-color: #e0f2fe; color: #0b5687;
            border-radius: 50%; display: flex; justify-content: center; align-items: center;
            font-size: 32px; font-weight: bold; margin: 0 auto 15px auto;
        }
        .badge {
            display: inline-block; padding: 4px 10px; background-color: #e0f2fe;
            color: #0369a1; border-radius: 20px; font-size: 12px; font-weight: bold;
            text-transform: uppercase; margin-bottom: 15px;
        }
        .profile-info { border-top: 1px solid #f0f0f0; padding-top: 15px; text-align: left; font-size: 14px; }
        .profile-info p { margin: 8px 0; color: #666; }
        
        .modules-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 25px; }
        .module-button {
            background-color: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 30px 20px;
            text-align: center; text-decoration: none; color: #333; transition: all 0.3s ease;
            display: flex; flex-direction: column; align-items: center; gap: 15px;
        }
        .module-button:hover { transform: translateY(-3px); border-color: #0b5687; box-shadow: 0 4px 12px rgba(11, 86, 135, 0.1); }
        .icon-container { width: 60px; height: 60px; background-color: #f0f7fc; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #0b5687; transition: all 0.3s ease; }
        .module-button:hover .icon-container { background-color: #e0f2fe; color: #0284c7; }
        .module-button h4 { margin: 0; color: #0b5687; font-size: 16px; }
        .module-button p { margin: 0; color: #777; font-size: 13px; line-height: 1.4; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>MediFlow <span>• Obra Social</span></h1>
        <a href="/mediflow/grupo-01/MediFlow/logout.php" class="btn-logout">Cerrar Sesión</a>
    </div>

    <div class="container grid-inicio">
        
        <div class="card profile-card">
            <div class="avatar">
                <?php echo strtoupper(substr($nombre, 0, 1)); ?>
            </div>
            <h3><?php echo htmlspecialchars($nombre . " " . $apellido); ?></h3>
            <span class="badge"><?php echo htmlspecialchars($rol); ?></span>
            
            <div class="profile-info">
                <p><strong>Email:</strong><br><?php echo htmlspecialchars($email); ?></p>
                <p><strong>Entorno:</strong><br>Servidor Local (XAMPP)</p>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-top:0; color:#0b5687; font-size:26px;">¡Hola de nuevo, <?php echo htmlspecialchars($nombre); ?>!</h2>
            <p style="color: #666; margin-bottom: 30px;">Bienvenido al Panel de Administración de MediFlow. Seleccione el módulo con el que desea operar:</p>
            
            <div class="modules-grid">
                
                <a href="/mediflow/grupo-01/MediFlow/vista/pacientes.php" class="module-button">
                    <div class="icon-container">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h4>Gestión de Pacientes</h4>
                    <p>Alta, edición y padrón de afiliados.</p>
                </a>

                <a href="#" class="module-button" style="opacity: 0.6; cursor: not-allowed;">
                    <div class="icon-container" style="color: #78909c;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <h4>Gestión de Solicitudes</h4>
                    <p>Próximamente disponible.</p>
                </a>

                <a href="#" class="module-button" style="opacity: 0.6; cursor: not-allowed;">
                    <div class="icon-container" style="color: #78909c;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h4>Auditoría Médica</h4>
                    <p>Próximamente disponible.</p>
                </a>

            </div>
        </div>

    </div>

</body>
</html>