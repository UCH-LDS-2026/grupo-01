<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

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
        .badge-inactivo { background: #fef2f2; color: #dc2626; }
        .btn-eliminar { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .extra-field { display: none; }
        .filtros-container { display: flex; gap: 15px; align-items: center; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .filtros-container select, .filtros-container input { padding: 8px; border-radius: 4px; border: 1px solid #cbd5e1; }
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
                        <input type="text" name="nombre" required placeholder="Ej: pepito">
                    </div>
                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" name="apellido" required placeholder="Ej: XXXXXXX">
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" required placeholder="pepito@ejemplo.com">
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="text" name="password" required placeholder="Asignar contraseña">
                    </div>
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" name="dni" required placeholder="Ej: XXXXXXXX">
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
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="req-dinamico">
                    </div>
                    <div class="form-group extra-field" data-rol="paciente">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="Ej: 2617158502">
                    </div>
                    
                    <div class="form-group extra-field" data-rol="paciente">
                        <label>Plan</label>
                        <select name="plan" class="req-dinamico" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            <option value="">-- Seleccione un Plan --</option>
                            <option value="PMO Inicial">PMO Inicial</option>
                            <option value="Plenitud 200">Plenitud 200</option>
                            <option value="MediPro">MediPro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-submit" style="height: 40px;">Crear Usuario</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>📋 Padrón de Usuarios del Sistema</h2>
            
            <div class="filtros-container">
                <strong>Filtros:</strong>
                <input type="text" id="filtro-texto" placeholder="🔍 Buscar por Nombre o DNI..." onkeyup="filtrarUsuarios()" style="flex-grow: 1;">
                
                <select id="filtro-rol" onchange="filtrarUsuarios()">
                    <option value="todos">Todos los roles</option>
                    <option value="admin">Administrador</option>
                    <option value="administrativo">Administrativo</option>
                    <option value="medico">Médico</option>
                    <option value="auditor">Auditor</option>
                    <option value="paciente">Paciente</option>
                </select>
                <select id="filtro-estado" onchange="filtrarUsuarios()">
                    <option value="todos">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Dados de baja</option>
                </select>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>DNI</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Alta</th>
                            <th>Fecha Baja</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-usuarios">
                        <?php if (empty($listaUsuarios)): ?>
                            <tr>
                                <td colspan="8" class="no-data">No hay usuarios registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaUsuarios as $u): ?>
                                <tr class="fila-usuario" data-rol="<?php echo strtolower($u['rol'] ?? ''); ?>" data-estado="<?php echo $u['activo'] ?? 1; ?>">
                                    <td><strong>#<?php echo htmlspecialchars($u['id_usuario'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars(($u['apellido'] ?? '') . ', ' . ($u['nombre'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($u['dni'] ?? '---'); ?></td>
                                    <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge <?php echo (isset($u['activo']) && $u['activo'] == 0) ? 'badge-inactivo' : ''; ?>">
                                            <?php echo htmlspecialchars($u['rol'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td><?php echo !empty($u['fecha_alta']) ? date('d/m/Y H:i', strtotime($u['fecha_alta'])) : '---'; ?></td>
                                    <td><?php echo !empty($u['fecha_baja']) ? date('d/m/Y H:i', strtotime($u['fecha_baja'])) : '---'; ?></td>
                                    <td style="text-align: center;">
                                        <?php if (!isset($u['activo']) || $u['activo'] == 1): ?>
                                            <a href="../controlador/usuarioControlador.php?accion=eliminar&id=<?php echo $u['id_usuario']; ?>" class="btn-eliminar" onclick="return confirm('¿Dar de baja a este usuario? Ya no podrá acceder al sistema.');">Dar de Baja</a>
                                        <?php else: ?>
                                            <span style="color: #64748b; font-size: 12px; font-weight: bold;">Desactivado</span>
                                        <?php endif; ?>
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
            document.querySelectorAll('.extra-field').forEach(div => {
                const elements = div.querySelectorAll('input, select');
                if (div.getAttribute('data-rol') === selectedRol) {
                    div.style.display = 'block';
                    elements.forEach(el => {
                        if(el.classList.contains('req-dinamico')) el.required = true;
                    });
                } else {
                    div.style.display = 'none';
                    elements.forEach(el => {
                        el.required = false;
                        el.value = ''; 
                    });
                }
            });
        });

        function filtrarUsuarios() {
            const filtroTexto = document.getElementById('filtro-texto').value.toLowerCase();
            const filtroRol = document.getElementById('filtro-rol').value.toLowerCase();
            const filtroEstado = document.getElementById('filtro-estado').value;
            const filas = document.querySelectorAll('.fila-usuario');

            filas.forEach(fila => {
                const rolFila = fila.getAttribute('data-rol');
                const estadoFila = fila.getAttribute('data-estado');
                
                const nombreFila = fila.cells[1].innerText.toLowerCase();
                const dniFila = fila.cells[2].innerText.toLowerCase();

                const coincideTexto = nombreFila.includes(filtroTexto) || dniFila.includes(filtroTexto);
                const coincideRol = (filtroRol === 'todos' || rolFila === filtroRol);
                const coincideEstado = (filtroEstado === 'todos' || estadoFila === filtroEstado);

                if (coincideTexto && coincideRol && coincideEstado) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>