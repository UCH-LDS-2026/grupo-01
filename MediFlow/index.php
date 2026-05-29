<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    $nombre = $usuario['nombre'] ?? 'Usuario';
    $apellido = $usuario['apellido'] ?? '';
    $email = $usuario['email'] ?? 'Sin email';
    $rol = $usuario['rol'] ?? 'Sin rol';

    $rolLimpio = strtolower(str_replace(['é','É'], ['e','E'], trim($rol)));
    
    // Contamos las notificaciones dependiendo del rol
    $notificaciones_medico = 0;
    $notificaciones_auditor = 0;
    
    if ($rolLimpio == 'medico' || $rolLimpio == 'auditor') {
        require_once __DIR__ . '/../src/config.php';
        
        if ($rolLimpio == 'medico') {
            $id_usuario = $usuario['id_usuario'];
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM solicitud WHERE id_medico = ? AND LOWER(TRIM(estado)) = 'observada'");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $notificaciones_medico = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        } 
        elseif ($rolLimpio == 'auditor') {
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM solicitud WHERE LOWER(TRIM(estado)) = 'pendiente'");
            $stmt->execute();
            $notificaciones_auditor = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        }
    }

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
<title>MediFlow - Panel</title>

<link rel="stylesheet" href="css/estilos.css">

<style>
.grid-inicio {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
}
@media (max-width: 800px) {
    .grid-inicio { grid-template-columns: 1fr; }
}

.profile-card { text-align: center; }

.avatar {
    width: 80px;
    height: 80px;
    background: #e0f2fe;
    color: #0b5687;
    border-radius: 50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    font-weight:bold;
    margin:auto;
}

.badge {
    display:inline-block;
    background:#e0f2fe;
    color:#0369a1;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    margin:10px 0;
}

.profile-info {
    margin-top:15px;
    text-align:left;
    font-size:14px;
    border-top:1px solid #eee;
    padding-top:10px;
}

.modules-grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(220px,1fr));
    gap:20px;
}

.module-button {
    background:white;
    border:1px solid #e2e8f0;
    border-radius:8px;
    padding:25px;
    text-align:center;
    text-decoration:none;
    transition:0.3s;
}

.module-button:hover {
    transform:translateY(-3px);
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

.icon-container {
    width: 60px;
    height: 60px;
    background-color: #f0f7fc;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #0b5687;
    margin: 0 auto 10px auto;
    transition: all 0.3s ease;
}

.module-button:hover .icon-container {
    background-color: #e0f2fe;
    color: #0284c7;
}

.module-button h4 {
    margin: 0;
    color: #0b5687;
    font-size: 16px;
    margin-bottom: 5px;
}

.module-button p {
    margin: 0;
    color: #777;
    font-size: 13px;
    line-height: 1.4;
}

/* Estilo para el puntito de notificación */
.notif-container { position: relative; display: inline-block; cursor:pointer; }
.notif-dot { position: absolute; top: -5px; right: -8px; background: #ef4444; color: white; border-radius: 50%; padding: 2px 6px; font-size: 11px; font-weight: bold; border: 2px solid #0b5687; }
</style>
</head>

<body>

<div class="navbar">
    <h1>MediFlow <span>• Obra Social</span></h1>

    <div style="display:flex; align-items:center; gap:20px;">
        <?php if ($rolLimpio == 'paciente'): ?>
            <a href="vista/notificaciones_paciente.php"
               style="background:#f59e0b; color:white; padding:8px 12px; border-radius:50%; text-decoration:none;" title="Ver Notificaciones">
               🔔
            </a>
        <?php elseif ($rolLimpio == 'medico'): ?>
            <a href="vista/notificaciones.php" class="notif-container"
               style="background:#f59e0b; color:white; padding:8px 12px; border-radius:50%; text-decoration:none;" title="Ver Notificaciones">
               🔔
               <?php if ($notificaciones_medico > 0): ?>
                   <span class="notif-dot" style="border:none; box-shadow:none;"><?php echo $notificaciones_medico; ?></span>
               <?php endif; ?>
            </a>
        <?php elseif ($rolLimpio == 'auditor'): ?>
            <a href="vista/dashboard.php" class="notif-container"
               style="background:#f59e0b; color:white; padding:8px 12px; border-radius:50%; text-decoration:none;" title="Ver Solicitudes Pendientes">
               🔔
               <?php if ($notificaciones_auditor > 0): ?>
                   <span class="notif-dot" style="border:none; box-shadow:none;"><?php echo $notificaciones_auditor; ?></span>
               <?php endif; ?>
            </a>
        <?php endif; ?>

        <a href="/mediflow/grupo-01/MediFlow/logout.php"
           style="background:#ef4444; color:white; padding:8px 14px; border-radius:6px; text-decoration:none;">
           Cerrar Sesión
        </a>
    </div>
</div>

<div class="container grid-inicio">

    <div class="card profile-card">
        <div class="avatar">
            <?php echo strtoupper(substr($nombre,0,1)); ?>
        </div>

        <h3><?php echo htmlspecialchars($nombre." ".$apellido); ?></h3>
        <span class="badge"><?php echo htmlspecialchars($rol); ?></span>

        <div class="profile-info">
            <p><strong>Email:</strong><br><?php echo htmlspecialchars($email); ?></p>
        </div>

        <div style="margin-top:20px;">
            <a href="vista/editar_perfil.php"
               style="
               display:block;
               background: linear-gradient(135deg, #0b5687, #0a4a73);
               color:white;
               padding:10px;
               border-radius:8px;
               text-decoration:none;
               font-weight:600;
               box-shadow:0 4px 10px rgba(11,86,135,0.25);
               ">
                Editar Perfil
            </a>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top:0; color:#0b5687; font-size:26px;">¡Hola, <?php echo htmlspecialchars($nombre); ?>!</h2>
        <p style="color: #666; margin-bottom: 30px;"></p>

        <div class="modules-grid">

            <?php if ($rolLimpio == 'paciente'): ?>
            <a href="/mediflow/grupo-01/MediFlow/vista/credencial.php" class="module-button">
                <div class="icon-container" style="font-size: 28px;">🪪</div>
                <h4>Mi Credencial</h4>
                <p>Identificación digital para presentar en centros médicos.</p>
            </a>
            <?php endif; ?>

            <?php if ($rolLimpio == 'medico' || $rolLimpio == 'admin' ): ?>
            <a href="/mediflow/grupo-01/MediFlow/vista/solicitudes.php" class="module-button">
                <div class="icon-container"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
                <h4>Gestión de Solicitudes</h4>
                <p>Carga de solicitudes médicas.</p>
            </a>
            <?php endif; ?>

            <?php if (in_array($rolLimpio, ['admin','administrador','medico','paciente','auditor'])): ?>
            <a href="/mediflow/grupo-01/MediFlow/vista/padron_solicitudes.php" class="module-button">
                <div class="icon-container"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg></div>
                <h4><?php echo ($rolLimpio == 'paciente') ? 'Mis Solicitudes' : 'Padrón de Solicitudes'; ?></h4>
                <p><?php echo ($rolLimpio == 'paciente') ? 'Consulta el historial y estado de tus solicitudes.' : 'Explorador y listado general de solicitudes médicas.'; ?></p>
            </a>
            <?php endif; ?>

            <?php if ($rolLimpio == 'admin' ||$rolLimpio == 'medico' || $rolLimpio == 'administrador'): ?>
            <a href="/mediflow/grupo-01/MediFlow/vista/pacientes.php" class="module-button">
                <div class="icon-container"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h4>Gestión de Pacientes</h4>
                <p>Edición y padrón de afiliados.</p>
            </a>
            <?php endif; ?>

            <?php if ($rolLimpio == 'admin' || $rolLimpio == 'administrador'): ?>
            <a href="/mediflow/grupo-01/MediFlow/vista/usuarios.php" class="module-button">
                <div class="icon-container"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h4>Gestión de Personal</h4>
                <p>Alta y baja de médicos, auditores y usuarios.</p>
            </a>
            <?php endif; ?>

            <?php if ($rolLimpio == 'admin' || $rolLimpio == 'auditor' || $rolLimpio == 'administrador'): ?>
            <a href="/mediflow/grupo-01/MediFlow/vista/dashboard.php" class="module-button">
                <div class="icon-container"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg></div>
                <h4>Dashboard Analítico</h4>
                <p>Métricas, contadores y prioridades.</p>
            </a>
            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>