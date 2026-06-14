<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Usuario.php';

/** @var mysqli $conexion */
$usuarioModelo = new Usuario($conexion);


// Eliminar usuario
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id_usuario = $_GET['id'] ?? null;
    
    if ($id_usuario) {
        // 1. Antes de borrar el usuario, buscamos su email para saber cuál es su ficha de paciente
        $sql_buscar = "SELECT email FROM usuario WHERE id_usuario = ?";
        $stmt_buscar = $conexion->prepare($sql_buscar);
        $stmt_buscar->bind_param("i", $id_usuario);
        $stmt_buscar->execute();
        $usuario_a_borrar = $stmt_buscar->get_result()->fetch_assoc();
        
        if ($usuario_a_borrar) {
            $email_a_borrar = $usuario_a_borrar['email'];
            
            // 2. Borramos primero de la tabla paciente usando su email para que no queden datos sueltos
            $sql_paciente = "DELETE FROM paciente WHERE email = ?";
            $stmt_paciente = $conexion->prepare($sql_paciente);
            $stmt_paciente->bind_param("s", $email_a_borrar);
            $stmt_paciente->execute();
        }
        
        // 3. Ahora sí, llamamos a tu función original para borrar el registro de la tabla usuario
        if ($usuarioModelo->eliminar($id_usuario)) {
            header("Location: ../vista/usuarios.php");
            exit;
        } else {
            echo " Error al eliminar el usuario.";
        }
    }
}

// Crear usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? ''; 
    $rol = $_POST['rol'] ?? '';
    
    // DNI AHORA ES GLOBAL PARA TODOS
    $dni = $_POST['dni'] ?? ''; 

    // Armamos un array con los campos extras dependiendo del rol
    $extras = [];
    if ($rol == 'medico') {
        $extras['matricula'] = $_POST['matricula'] ?? '';
        $extras['especialidad'] = $_POST['especialidad'] ?? '';
    } elseif ($rol == 'auditor') {
        $extras['sector'] = $_POST['sector'] ?? '';
    } elseif ($rol == 'paciente') {
        $extras['fecha_nacimiento'] = $_POST['fecha_nacimiento'] ?? '';
        $extras['telefono'] = $_POST['telefono'] ?? '';
        $extras['plan'] = $_POST['plan'] ?? '';
        $extras['nro_afiliado'] = $_POST['nro_afiliado'] ?? '';
    }

    // Le pasamos el $dni como un parámetro nuevo al modelo
    if ($usuarioModelo->crear($nombre, $apellido, $email, $password, $rol, $dni, $extras)) {
        header("Location: ../vista/usuarios.php");
        exit;
    } else {
        echo " Error al crear el usuario. Verifique que el correo, DNI, o Matrícula no estén duplicados.";
    }
}
?>