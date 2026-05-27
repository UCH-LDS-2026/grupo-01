<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../../src/config.php'; 
require_once __DIR__ . '/../modelo/Paciente.php';

/** @var mysqli $conexion */
$pacienteModelo = new Paciente($conexion);

$usuario = $_SESSION['usuario'] ?? null;
$rol = strtolower($usuario['rol'] ?? '');

// Traemos todos los pacientes por defecto para que el buscador en vivo de JS tenga todos los datos
$listaPacientes = $pacienteModelo->listar();

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
<title>MediFlow - Pacientes</title>
<link rel="stylesheet" href="../css/estilos.css">

<style>
.container-ancho { max-width:95%; margin:30px auto; }
.form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:15px; }
.input-readonly { background:#f1f5f9; border:1px solid #cbd5e1; color:#64748b; pointer-events:none; }
label { font-weight:bold; font-size:13px; display:block; margin-bottom:4px; }

.btn-submit { background:#0b5687; color:white; padding:10px; border:none; border-radius:6px; cursor: pointer; font-weight: bold;}
.btn-submit:hover { background: #0a4a73; }

.btn-volver-nav {
    background-color: transparent; 
    color: white;
    padding: 6px 15px;
    border: 2px solid white; 
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-volver-nav:hover { background-color: white; color: #0b5687; }

/* Buscador y Controles Superiores */
.top-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
}
.search-form { display: flex; gap: 10px; flex: 1; max-width: 500px;}
.search-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    outline: none;
}
.search-input:focus { border-color: #0b5687; }

/* Diseño del botón modificar */
.btn-modificar {
    display: inline-block;
    padding: 6px 12px;
    border: 1px solid #0284c7;
    color: #0284c7;
    background: transparent;
    border-radius: 4px;
    text-decoration: none;
    font-size: 12px;
    font-weight: bold;
    transition: all 0.2s;
}
.btn-modificar:hover { background: #0284c7; color: white; }
</style>
</head>
<body>

<div class="navbar">
    <h1>MediFlow</h1>
    <a href="../index.php" class="btn-volver-nav">Volver</a>
</div>

<div class="container-ancho">

<?php if (!$esEdicion): ?>
<div class="top-controls">
    <div class="search-form">
        <input type="text" id="buscadorPacientes" class="search-input" placeholder="Ingrese Nombre o DNI">
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h2>
    <?php 
    if ($esEdicion) {
        echo " Modificar Paciente";
        echo " <a href='pacientes.php' style='float:right; font-size:14px; text-decoration:none; color:#ef4444;'>Cancelar Edición</a>";
    } else {
        echo " Alta Paciente";
    }
    ?>
    </h2>

    <?php if ($rol == 'admin' || $esEdicion): ?>
    <form action="../controlador/pacienteControlador.php" method="POST">
        <input type="hidden" name="accion" value="<?php echo $esEdicion ? 'editar' : 'crear'; ?>">
        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $pacienteAEditar['id_paciente']; ?>">
        <?php endif; ?>

        <div class="form-grid">
            <?php
            function campo($label, $name, $value, $readonly=false) {
                $ro = $readonly ? 'readonly class="input-readonly"' : '';
                echo "
                <div>
                    <label>$label</label>
                    <input type='text' name='$name' value='$value' $ro>
                </div>";
            }
            ?>

            <?php
            $esMedico = ($rol == 'medico');
            campo("Nombre", "nombre", $pacienteAEditar['nombre'] ?? '', $esEdicion && $esMedico);
            campo("Apellido", "apellido", $pacienteAEditar['apellido'] ?? '', $esEdicion && $esMedico);
            campo("DNI", "dni", $pacienteAEditar['dni'] ?? '', $esEdicion && $esMedico);
            campo("N° Afiliado", "nro_afiliado", $pacienteAEditar['nro_afiliado'] ?? '', $esEdicion && $esMedico);
            ?>

            <div>
                <label>Fecha Nacimiento</label>
                <input type="date" name="fecha_nacimiento"
                value="<?php echo $pacienteAEditar['fecha_nacimiento'] ?? ''; ?>"
                <?php echo ($esEdicion && $esMedico) ? 'readonly class="input-readonly"' : ''; ?>>
            </div>

            <div>
                <label>Plan</label>
                <input type="text" name="plan"
                value="<?php echo $pacienteAEditar['plan'] ?? ''; ?>"
                <?php echo ($esEdicion && $esMedico) ? 'readonly class="input-readonly"' : ''; ?>>
            </div>

            <?php
            campo("Email", "email", $pacienteAEditar['email'] ?? '', false);
            campo("Teléfono", "telefono", $pacienteAEditar['telefono'] ?? '', false);
            ?>

            <div>
                <button class="btn-submit">
                <?php echo $esEdicion ? "Guardar Cambios" : "Crear Paciente"; ?>
                </button>
            </div>
        </div>
    </form>
    <?php else: ?>
        <p style="color:red;">No tenés permisos para dar de alta pacientes.</p>
    <?php endif; ?>
</div>

<?php if (!$esEdicion): ?>
<div class="card">
    <h2>Padrón de Pacientes</h2>
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: #f1f5f9;">
                <th style="padding:10px; border-bottom:1px solid #cbd5e1;">ID</th>
                <th style="padding:10px; border-bottom:1px solid #cbd5e1;">Apellido y Nombre</th>
                <th style="padding:10px; border-bottom:1px solid #cbd5e1;">DNI</th>
                <th style="padding:10px; border-bottom:1px solid #cbd5e1;">Email</th>
                <th style="padding:10px; border-bottom:1px solid #cbd5e1;">Teléfono</th>
                <th style="padding:10px; border-bottom:1px solid #cbd5e1; text-align:center;">Acción</th>
            </tr>
        </thead>
        <tbody id="tablaPacientes">
            <?php if(empty($listaPacientes)): ?>
                <tr><td colspan="6" style="padding:15px; text-align:center; color:#64748b;">No se encontraron pacientes.</td></tr>
            <?php else: ?>
                <?php foreach ($listaPacientes as $p): ?>
                <tr class="fila-paciente">
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo $p['id_paciente']; ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong><?php echo htmlspecialchars($p['apellido'] . ', ' . $p['nombre']); ?></strong></td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo htmlspecialchars($p['dni']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo htmlspecialchars($p['email']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo htmlspecialchars($p['telefono']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9; text-align:center;">
                        <a href="pacientes.php?editar=<?php echo $p['id_paciente']; ?>" class="btn-modificar">Modificar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

</div>

<script>
    const buscadorPac = document.getElementById('buscadorPacientes');
    if(buscadorPac) {
        buscadorPac.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const filas = document.querySelectorAll('.fila-paciente');

            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                if(textoFila.includes(query)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
</script>

</body>
</html>