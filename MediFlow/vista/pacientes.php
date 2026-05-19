<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Paciente.php';

/** @var mysqli $conexion */
$pacienteModelo = new Paciente($conexion);

$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

if (!empty($busqueda)) {
    $listaPacientes = $pacienteModelo->buscarPorTexto($busqueda);
} else {
    $listaPacientes = $pacienteModelo->listar();
}

$esEdicion = false;
$pacienteAEditar = null;

if (isset($_GET['editar'])) {
    $esEdicion = true;
    $pacienteAEditar = $pacienteModelo->buscarPorId($_GET['editar']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediFlow - Gestión de Pacientes</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        /* Ajustes específicos para ensanchar esta vista y que entre toda la tabla */
        .container-ancho {
            max-width: 95%;
            margin: 30px auto;
            padding: 0 20px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        /* El formulario ahora usa 2 o 3 columnas para no ser tan largo hacia abajo */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0; /* Anulamos el margen porque usamos el gap del grid */
        }

        .input-readonly {
            background-color: #f0f4f8;
            color: #666;
            cursor: not-allowed;
            border: 1px dashed #ccc !important;
        }
        
        /* Ajuste de la tabla para texto más compacto y legible */
        td, th {
            white-space: nowrap;
            font-size: 13px !important;
            padding: 10px !important;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>MediFlow <span>• Obra Social</span></h1>
        <a href="../index.php" class="btn-back"> Volver al Inicio</a>
    </div>

    <div class="container-ancho">
        
        <div class="card">
            <h2><?php echo $esEdicion ? " Modificar Datos del Afiliado" : " Alta de Nuevo Afiliado"; ?></h2>
            
            <form action="../controlador/pacienteControlador.php" method="POST">
                <input type="hidden" name="accion" value="<?php echo $esEdicion ? 'editar' : 'crear'; ?>">
                
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($pacienteAEditar['id_paciente'] ?? ''); ?>">
                <?php endif; ?>

                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>ID en Sistema</label>
                        <input type="text" class="input-readonly" value="<?php echo $esEdicion ? '#' . htmlspecialchars($pacienteAEditar['id_paciente'] ?? '') : 'Autogenerado'; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Fecha de Alta</label>
                        <input type="text" class="input-readonly" value="<?php echo $esEdicion ? htmlspecialchars($pacienteAEditar['fecha_alta'] ?? '') : 'Se asigna al guardar'; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" value="<?php echo $esEdicion ? htmlspecialchars($pacienteAEditar['nombre'] ?? '') : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" name="apellido" value="<?php echo $esEdicion ? htmlspecialchars($pacienteAEditar['apellido'] ?? '') : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" name="dni" value="<?php echo $esEdicion ? htmlspecialchars($pacienteAEditar['dni'] ?? '') : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" value="<?php echo $esEdicion ? htmlspecialchars($pacienteAEditar['fecha_nacimiento'] ?? '') : ''; ?>" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                    </div>

                    <div class="form-group">
                        <label>N° Afiliado</label>
                        <input type="text" name="nro_afiliado" value="<?php echo $esEdicion ? htmlspecialchars($pacienteAEditar['nro_afiliado'] ?? '') : ''; ?>" placeholder="Ej: F-55322-01" required>
                    </div>

                    <div class="form-group">
                        <label>Plan de Obra Social</label>
                        <select name="plan" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            <?php 
                            $planes = ['PMO Inicial', 'Plenitud 200', 'MediFlow Advanced 400', 'Premium Oro'];
                            foreach ($planes as $p):
                                $planActual = $pacienteAEditar['plan'] ?? '';
                                $selected = ($esEdicion && $planActual == $p) ? 'selected' : '';
                                echo "<option value='$p' $selected>$p</option>";
                            endforeach;
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" value="<?php echo $esEdicion ? htmlspecialchars($pacienteAEditar['email'] ?? '') : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Celular / Teléfono</label>
                        <input type="text" name="telefono" value="<?php echo $esEdicion ? htmlspecialchars($pacienteAEditar['telefono'] ?? '') : ''; ?>" placeholder="Ej: 1122334455">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-submit" style="height: 40px;">
                            <?php echo $esEdicion ? "Guardar Cambios" : "Confirmar Afiliación"; ?>
                        </button>
                    </div>
                    
                    <?php if ($esEdicion): ?>
                    <div class="form-group">
                        <a href="pacientes.php" class="btn btn-cancel" style="height: 40px; line-height: 20px;">Cancelar</a>
                    </div>
                    <?php endif; ?>

                </div>
            </form>
        </div>

        <div class="card">
            <h2> Padrón de Pacientes Afiliados</h2>
            
            <form action="pacientes.php" method="GET" class="search-box">
                <input type="text" name="buscar" placeholder="Buscar por Nombre, Apellido, DNI o N° de Afiliado..." value="<?php echo htmlspecialchars($busqueda ?? ''); ?>">
                <button type="submit" class="btn btn-search">Buscar</button>
                <?php if (!empty($busqueda)): ?>
                    <a href="pacientes.php" class="btn btn-clear">Limpiar</a>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>N° Afiliado</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>DNI</th>
                            <th>Nacimiento</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Plan</th>
                            <th>Fecha Alta</th>
                            <th style="text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaPacientes)): ?>
                            <tr>
                                <td colspan="11" class="no-data">No se encontraron pacientes registrados en el sistema.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaPacientes as $p): 
                                $id = $p['id_paciente'] ?? '0';
                                $nro_afiliado = !empty($p['nro_afiliado']) ? $p['nro_afiliado'] : 'S/N';
                                $nombre = !empty($p['nombre']) ? $p['nombre'] : '';
                                $apellido = !empty($p['apellido']) ? $p['apellido'] : '';
                                $dni = !empty($p['dni']) ? $p['dni'] : 'S/D';
                                // Formateamos la fecha de nacimiento para que se lea mejor (DD/MM/YYYY)
                                $fecha_nac = !empty($p['fecha_nacimiento']) ? date("d/m/Y", strtotime($p['fecha_nacimiento'])) : '--/--/----';
                                $email = !empty($p['email']) ? $p['email'] : '---';
                                $telefono = !empty($p['telefono']) ? $p['telefono'] : '---';
                                $plan = !empty($p['plan']) ? $p['plan'] : 'Sin Plan';
                                // Formateamos la fecha y hora de alta
                                $fecha_alta = !empty($p['fecha_alta']) ? date("d/m/Y H:i", strtotime($p['fecha_alta'])) : '---';
                            ?>
                                <tr>
                                    <td><strong style="color: #0b5687;">#<?php echo htmlspecialchars($id); ?></strong></td>
                                    <td><span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:12px; font-weight:600;"><?php echo htmlspecialchars($nro_afiliado); ?></span></td>
                                    <td><?php echo htmlspecialchars($nombre); ?></td>
                                    <td><?php echo htmlspecialchars($apellido); ?></td>
                                    <td><?php echo htmlspecialchars($dni); ?></td>
                                    <td><?php echo htmlspecialchars($fecha_nac); ?></td>
                                    <td><?php echo htmlspecialchars($email); ?></td>
                                    <td><?php echo htmlspecialchars($telefono); ?></td>
                                    <td><strong><?php echo htmlspecialchars($plan); ?></strong></td>
                                    <td style="color: #777;"><?php echo htmlspecialchars($fecha_alta); ?></td>
                                    <td style="text-align: center;">
                                        <a href="pacientes.php?editar=<?php echo htmlspecialchars($id); ?>" class="btn-table-action">Modificar</a>
                                    </td>
                                </tr>
                                <td style="text-align: center;">
                                        <a href="pacientes.php?editar=<?php echo htmlspecialchars($id); ?>" class="btn-table-action" style="margin-right: 15px;">Modificar</a>
                                        
                                        <a href="../controlador/pacienteControlador.php?eliminar=<?php echo htmlspecialchars($id); ?>" 
                                           class="btn-table-action" 
                                           style="color: #ef5350;" 
                                           onclick="return confirm('⚠️ ¿Está seguro de que desea dar de baja a este afiliado? Esta acción eliminará su registro del sistema y no se puede deshacer.');">
                                           Dar de Baja
                                        </a>
                                    </td>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>