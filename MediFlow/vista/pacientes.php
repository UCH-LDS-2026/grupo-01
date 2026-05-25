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

$busqueda = $_GET['buscar'] ?? '';

$listaPacientes = !empty($busqueda)
    ? $pacienteModelo->buscarPorTexto($busqueda)
    : $pacienteModelo->listar();

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
.input-readonly {
    background:#f1f5f9;
    border:1px solid #cbd5e1;
    color:#64748b;
    pointer-events:none;
}
label { font-weight:bold; font-size:13px; display:block; margin-bottom:4px; }

.btn-submit { background:#0b5687; color:white; padding:10px; border:none; border-radius:6px; }
.btn-volver-nav {
    background-color: transparent; /* Fondo transparente para ver el azul de la navbar */
    color: white;
    padding: 6px 15px;
    border: 2px solid white; /* Este es el recuadro blanco que querés */
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-volver-nav:hover {
    background-color: white; /* Se invierte al pasar el mouse */
    color: #0b5687;
}
</style>

</head>
<body>

<div class="navbar">
    <h1>MediFlow</h1>
    <a href="../index.php" class="btn-volver-nav">Volver</a>
</div>

<div class="container-ancho">

<!-- FORM -->
<div class="card">

<h2>
<?php 
if ($esEdicion) echo "Modificar Paciente";
else echo "Alta Paciente";
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
// estos SI puede modificar el médico
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

<!-- TABLA -->
<div class="card">

<h2>Padrón de Pacientes</h2>

<table>
<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Apellido</th>
<th>DNI</th>
<th>Email</th>
<th>Teléfono</th>
<th>Acción</th>
</tr>
</thead>

<tbody>

<?php foreach ($listaPacientes as $p): ?>
<tr>
<td><?php echo $p['id_paciente']; ?></td>
<td><?php echo $p['nombre']; ?></td>
<td><?php echo $p['apellido']; ?></td>
<td><?php echo $p['dni']; ?></td>
<td><?php echo $p['email']; ?></td>
<td><?php echo $p['telefono']; ?></td>
<td>
<a href="pacientes.php?editar=<?php echo $p['id_paciente']; ?>">
Modificar
</a>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>

</div>

</body>
</html>