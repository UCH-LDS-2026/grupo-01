<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Si el usuario está logueado
if (isset($_SESSION['usuario'])) {

    $usuario = $_SESSION['usuario'];

    echo "<h2>Bienvenido " . $usuario['nombre'] . "</h2>";
    echo "<p>Email: " . $usuario['email'] . "</p>";
    echo "<p>Rol: " . $usuario['rol'] . "</p>";

    echo "<br><a href='/MediFlow/grupo-01/MediFlow/logout.php'>Cerrar sesión</a>";

} else {
    // Si no está logueado → va al login
    header("Location: /MediFlow/grupo-01/MediFlow/vista/login.php");
    exit;
}
?>