<?php
session_start();
// Ajustá la ruta del config.php dependiendo de dónde guardes este archivo
require_once __DIR__ . '/config/config.php'; 

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos los 4 datos y les limpiamos los espacios en blanco de los bordes con trim()
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $dni = trim($_POST['dni']);
    
    $nueva_clave = $_POST['nueva_clave'];
    $repetir_clave = $_POST['repetir_clave'];

    // 1. Validar que las contraseñas coincidan
    if ($nueva_clave !== $repetir_clave) {
        $mensaje = "Las contraseñas nuevas no coinciden.";
        $tipo_mensaje = "error";
    } else {
        // 2. Buscar si existe un usuario con esos 4 datos EXACTOS
        $sql = "SELECT id_usuario FROM usuario WHERE email = ? AND dni = ? AND nombre = ? AND apellido = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $email, $dni, $nombre, $apellido);
        $stmt->execute();
        $resultado = $stmt->get_result();

        // 3. Si encontramos al usuario, le cambiamos la clave
        if ($resultado->num_rows > 0) {
            // Agarramos el ID exacto de ese usuario para actualizar su clave
            $usuario_encontrado = $resultado->fetch_assoc();
            $id_usuario = $usuario_encontrado['id_usuario'];

            $update_sql = "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?";
            $update_stmt = $conexion->prepare($update_sql);
            $update_stmt->bind_param("si", $nueva_clave, $id_usuario);
            
            if ($update_stmt->execute()) {
                $mensaje = "¡Contraseña actualizada con éxito! Ya puedes volver al login.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "Hubo un error al guardar la contraseña en la base de datos.";
                $tipo_mensaje = "error";
            }
        } else {
            // Si tan solo UN dato no coincide, rebota
            $mensaje = "Los datos ingresados no coinciden con nuestros registros.";
            $tipo_mensaje = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - MediFlow</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f9ff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .login-container { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        h2 { color: #0c4a6e; text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #334155; font-weight: bold; font-size: 14px; }
        input[type="email"], input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #0284c7; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        button:hover { background-color: #0369a1; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .alert.error { background-color: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .alert.exito { background-color: #dcfce3; color: #166534; border: 1px solid #4ade80; }
        .link-volver { display: block; text-align: center; margin-top: 15px; color: #0284c7; text-decoration: none; font-size: 14px; }
        .link-volver:hover { text-decoration: underline; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Recuperar Contraseña</h2>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            
            <div class="grid-2">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" required placeholder="Ej: pepito">
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" name="apellido" id="apellido" required placeholder="Ej: XXXXXXX">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico registrado</label>
                <input type="email" name="email" id="email" required placeholder="ejemplo@correo.com">
            </div>

            <div class="form-group">
                <label for="dni">Número de DNI (Sin puntos)</label>
                <input type="text" name="dni" id="dni" required placeholder="Ej: 12345678">
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

            <div class="form-group">
                <label for="nueva_clave">Nueva Contraseña</label>
                <input type="password" name="nueva_clave" id="nueva_clave" required>
            </div>

            <div class="form-group">
                <label for="repetir_clave">Repetir Nueva Contraseña</label>
                <input type="password" name="repetir_clave" id="repetir_clave" required>
            </div>

            <button type="submit">Actualizar Contraseña</button>
        </form>

        <a href="index.php" class="link-volver">Volver al Inicio de Sesión</a>
    </div>

</body>
</html>