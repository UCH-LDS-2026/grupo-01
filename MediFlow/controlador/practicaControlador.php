<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Practica.php';

/** @var mysqli $conexion */
$practicaModelo = new Practica($conexion);

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id = $_GET['id'] ?? null;
    if ($id && $practicaModelo->eliminar($id)) {
        header("Location: ../vista/practicas.php");
        exit;
    } else {
        echo "❌ Error al eliminar la práctica.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    if (!empty($nombre) && $practicaModelo->crear($nombre)) {
        header("Location: ../vista/practicas.php");
        exit;
    } else {
        echo "❌ Error al registrar la práctica médica.";
    }
}
?>