<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../src/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rol = $_POST['rol'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $nuevo_email = trim($_POST['email'] ?? '');
    
    // Obtenemos el email actual de la sesión para buscar el registro
    $email_actual_sesion = $_SESSION['usuario']['email'] ?? '';

    if (!empty($nuevo_email) && !empty($email_actual_sesion) && !empty($nombre) && !empty($apellido)) {
        
        // 1. ACTUALIZAR TABLA 'USUARIO' (Nombre, Apellido, Email)
        $sqlUser = "UPDATE usuario SET nombre = ?, apellido = ?, email = ? WHERE email = ?";
        $stmtUser = $conexion->prepare($sqlUser);
        $stmtUser->bind_param("ssss", $nombre, $apellido, $nuevo_email, $email_actual_sesion);
        $stmtUser->execute();

        // 2. ACTUALIZAR TABLA 'PACIENTE' (Solo si el que edita es un paciente)
        if ($rol == 'paciente') {
            $id_paciente = $_POST['id_paciente'] ?? 0;
            $telefono = trim($_POST['telefono'] ?? '');
            
            if ($id_paciente > 0) {
                $sqlPac = "UPDATE paciente SET nombre = ?, apellido = ?, email = ?, telefono = ? WHERE id_paciente = ?";
                $stmtPac = $conexion->prepare($sqlPac);
                $stmtPac->bind_param("ssssi", $nombre, $apellido, $nuevo_email, $telefono, $id_paciente);
                $stmtPac->execute();
            }
        }

        // 3. REFLEJAR EL CAMBIO EN LA SESIÓN (Para que el nombre se actualice de inmediato en el Dashboard)
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['apellido'] = $apellido;
        $_SESSION['usuario']['email'] = $nuevo_email;
    }
    
    // Volver al panel de control
    header("Location: ../index.php");
    exit;
}
?>