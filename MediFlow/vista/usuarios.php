<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// SEGURIDAD: Solo el Administrador (admin) puede gestionar usuarios
$rolUsuarioOriginal = $_SESSION['usuario']['rol'] ?? '';
$rolNormalizado = strtolower(trim($rolUsuarioOriginal));

if (!isset($_SESSION['usuario']) || ($rolNormalizado != 'admin' && $rolNormalizado != 'administrador')) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Usuario.php';

/** @var mysqli $conexion */
$usuarioModelo = new Usuario($conexion);
$listaUsuarios = $usuarioModelo->listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediFlow - Gestión de Usuarios</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .container-ancho { max-width: 95%; margin: 30px auto; padding: 0 20px; display: flex; flex-direction: column; gap: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end; }
        .badge { padding: 4px 8px; border-radius: 12px; font-weight: bold; font-size: 12px; text-transform: capitalize; background: #e0f2fe; color: #0369a1; }
        .btn-eliminar { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        /* Oculta los campos dinámicos por defecto */
        .extra-field { display: none; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>MediFlow <span>• Módulo Usuarios (Exclusivo Admin)</span></h1>
        <a href="../index.php" class="btn-back"> Volver al Inicio</a>
    </div>

    <div class="container-ancho">
        
        <div class="card">
            <h2>👤 Dar de Alta Personal / Usuarios</h2>
            <form action="../controlador/usuarioControlador.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required placeholder="Ej: Juan">
                    </div>
                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" name="apellido" required placeholder="Ej: Pérez">
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" required placeholder="juan@mediflow.com">
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="text" name="password" required placeholder="Asignar contraseña">
                    </div>
                    <div class="form-group">
                        <label>Rol en el Sistema</label>
                        <select name="rol" id="select-rol" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            <option value="">-- Seleccione un Rol --</option>
                            <option value="admin">Administrador</option>
                            <option value="administrativo">Administrativo</option>
                            <option value="medico">Médico</option>
                            <option value="auditor">Auditor</option>
                            <option value="paciente">Paciente (Afiliado)</option>
                        </select>
                    </div>

                    <div class="form-group extra-field" data-rol="medico">
                        <label>Matrícula</label>
                        <input type="text" name="matricula" class="req-dinamico" placeholder="Ej: MN-112233">
                    </div>
                    <div class="form-group extra-field" data-rol="medico">
                        <label>Especialidad</label>
                        <input type="text" name="especialidad" class="req-dinamico" placeholder="Ej: Traumatología">
                    </div>

                    <div class="form-group extra-field" data-rol="auditor">
                        <label>Sector</label>
                        <input type="text" name="sector" class="req-dinamico" placeholder="Ej: Auditoría Central">
                    </div>

                    <div class="form-group extra-field" data-rol="paciente">
                        <label>DNI</label>
                        <input type="text" name="dni" class="req-dinamico" placeholder="Ej: 46866891">
                    </div>
                    <div class="form-group extra-field" data-rol="paciente">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="req-dinamico">
                    </div>
                    <div class="form-group extra-field" data-rol="paciente">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="Ej: 2617158502">
                    </div>
                    <div class="form-group extra-field" data-rol="paciente">
                        <label>Plan</label>
                        <input type="text" name="plan" class="req-dinamico" placeholder="Ej: Plenitud 200">
                    </div>
                    <div class="form-group extra-field" data-rol="paciente">
                        <label>N° Afiliado</label>
                        <input type="text" name="nro_afiliado" class="req-dinamico" placeholder="Ej: F-00001-01">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-submit" style="height: 40px;">Crear Usuario</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>📋 Padrón de Usuarios del Sistema</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Email (Usuario)</th>
                            <th>Rol</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaUsuarios)): ?>
                            <tr>
                                <td colspan="5" class="no-data">No hay usuarios registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaUsuarios as $u): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($u['id_usuario']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['apellido'] . ', ' . $u['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><span class="badge"><?php echo htmlspecialchars($u['rol']); ?></span></td>
                                    <td style="text-align: center;">
                                        <a href="../controlador/usuarioControlador.php?accion=eliminar&id=<?php echo $u['id_usuario']; ?>" class="btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario? Perderá el acceso al sistema.');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        document.getElementById('select-rol').addEventListener('change', function() {
            const selectedRol = this.value;
            
            // Recorremos todos los div ocultos "extra-field"
            document.querySelectorAll('.extra-field').forEach(div => {
                const input = div.querySelector('input');
                
                // Si el div corresponde al rol seleccionado, lo mostramos
                if (div.getAttribute('data-rol') === selectedRol) {
                    div.style.display = 'block';
                    // Obligamos a rellenarlo solo a los que tienen la clase req-dinamico
                    if(input && input.classList.contains('req-dinamico')) {
                        input.required = true;
                    }
                } else {
                    // Si no corresponde, lo ocultamos y limpiamos
                    div.style.display = 'none';
                    if(input) {
                        input.required = false;
                        input.value = ''; // Limpiamos para evitar basura en el envío
                    }
                }
            });
        });
    </script>
</body>
</html>