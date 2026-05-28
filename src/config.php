<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "mediflow";

// Creamos la conexión con MySQLi
$conexion = new mysqli($host, $user, $pass, $db);

// Por si las dudas, seteamos el charset para no tener problemas con los acentos
$conexion->set_charset("utf8");
?>