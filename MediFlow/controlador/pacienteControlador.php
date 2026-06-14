<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$rol = strtolower($_SESSION['usuario']['rol'] ?? '');

if ($_POST['accion'] == 'crear' && !in_array($rol, ['admin','administrador'])) {
    die("Sin permisos");
}

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Paciente.php';
require_once __DIR__ . '/../modelo/Usuario.php';

/** @var mysqli $conexion */
$pacienteModelo = new Paciente($conexion);
$usuarioModelo = new Usuario($conexion);

// =================================================================
// 1. PETICIONES GET (Ejemplo: Hacer clic en un enlace como Eliminar)
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    // Ejecuta la función del modelo y, si sale bien, vuelve a la vista
    if ($pacienteModelo->eliminar($id)) {
        header("Location: ../vista/pacientes.php");
        exit;
    } else {
        echo "❌ Error al intentar dar de baja al afiliado.";
    }
}

// =================================================================
// 2. PETICIONES POST (Ejemplo: Enviar un formulario para Crear/Editar)
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $dni = $_POST['dni'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $plan = $_POST['plan'] ?? '';
    $nro_afiliado = $_POST['nro_afiliado'] ?? '';

    // ACCIÓN: CREAR
 // ACCIÓN: CREAR
    if (isset($_POST['accion']) && $_POST['accion'] == 'crear') {
        
        // 1. Validación de DNI con cartel amigable
        if (!ctype_digit(trim($dni))) {
            echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                    <h2 style='color:#ef4444;'>❌ DNI Incorrecto</h2>
                    <p>Debes ingresar únicamente números, sin puntos ni letras.</p>
                    <a href='../vista/pacientes.php' style='padding:10px 20px; background:#0b5687; color:white; text-decoration:none; border-radius:5px;'>Volver a intentar</a>
                  </div>";
            exit;
        }

        // 2. Armamos la mochila de datos para el modelo seguro
        $extras = [
            'fecha_nacimiento' => trim($fecha_nacimiento),
            'telefono' => trim($telefono),
            'plan' => trim($plan),
            'nro_afiliado' => trim($nro_afiliado)
        ];

        // 3. Usamos Usuario.php para que encripte. Contraseña por defecto: el DNI.
        $creadoSeguro = $usuarioModelo->crear(trim($nombre), trim($apellido), trim($email), trim($dni), 'paciente', trim($dni), $extras);

        if ($creadoSeguro) {
            header("Location: ../vista/pacientes.php");
            exit;
        } else {
            echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                    <h2 style='color:#ef4444;'> Error de Registro</h2>
                    <p>El paciente no se pudo guardar. Es probable que el Email o el N° de Afiliado ya existan.</p>
                    <a href='../vista/pacientes.php' style='padding:10px 20px; background:#0b5687; color:white; text-decoration:none; border-radius:5px;'>Volver</a>
                  </div>";
            exit;
        }
    }
    
    // ACCIÓN: EDITAR
// ACCIÓN: EDITAR
    if (isset($_POST['accion']) && $_POST['accion'] == 'editar') {
        $id = $_POST['id'] ?? null;
        
        try {
            if ($id && $pacienteModelo->editar($id, trim($nombre), trim($apellido), trim($dni), trim($fecha_nacimiento), trim($email), trim($telefono), trim($plan), trim($nro_afiliado))) {
                header("Location: ../vista/pacientes.php");
                exit;
            } else {
                echo "Error al actualizar los datos.";
            }
        } catch (mysqli_sql_exception $e) {
            // Atrapamos el error feo de base de datos
            echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                    <h2 style='color:#ef4444;'> Error al Modificar</h2>
                    <p>No podés usar ese Número de Afiliado o Email porque ya le pertenece a otra persona.</p>
                    <a href='../vista/pacientes.php' style='padding:10px 20px; background:#0b5687; color:white; text-decoration:none; border-radius:5px;'>Volver</a>
                  </div>";
            exit;
        }
    }
}
?>