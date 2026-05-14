<?php
echo "<h1> MediFlow funcionando correctamente</h1>";
echo "<p>El servidor PHP está activo.</p>";

// Prueba de conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mediflow");

if ($conexion->connect_error) {
    die("<p>Error de conexión a la base de datos</p>");
} else {
    echo "<p>Conexión a la base de datos exitosa</p>";
}
?>