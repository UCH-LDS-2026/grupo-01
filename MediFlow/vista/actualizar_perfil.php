<?php
session_start();

// Verificamos que sea un usuario logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../src/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_paciente = $_POST['id_paciente'] ?? 0;
    $telefono = trim($_POST['telefono'] ?? '');

    // Actualizamos solo el teléfono por seguridad (el email está vinculado al login)
    if ($id_paciente > 0) {
        $sql = "UPDATE paciente SET telefono = ? WHERE id_paciente = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $telefono, $id_paciente);
        $stmt->execute();
    }
    
    // Redirigimos de vuelta al padrón con un mensaje de éxito
    header("Location: padron_solicitudes.php?perfil=ok");
    exit;
}
?>