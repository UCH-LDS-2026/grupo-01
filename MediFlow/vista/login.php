<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediFlow - Portal del Afiliado</title>

<link rel="stylesheet" href="estilos.css">

<style>
body {
background: linear-gradient(135deg, #eef2f7 0%, #e2e8f0 100%);
min-height: 100vh;
margin: 0;
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* ANIMACIÓN */
.card {
animation: fadeIn 0.6s ease;
}
@keyframes fadeIn {
from { opacity: 0; transform: translateY(20px); }
to { opacity: 1; transform: translateY(0); }
}

/* HEADER */
.portal-header {
background: rgba(255,255,255,0.85);
backdrop-filter: blur(6px);
border-bottom: 1px solid #e2e8f0;
padding: 12px 0;
font-size: 13px;
}

.portal-header .container-top {
max-width: 1100px;
margin: auto;
padding: 0 20px;
display: flex;
justify-content: space-between;
}

.portal-header a {
color: #0b5687;
text-decoration: none;
margin-left: 15px;
font-weight: 600;
}

/* NAVBAR */
.navbar {
background: linear-gradient(90deg, #0b5687, #0a4a73);
padding: 20px 30px;
color: white;
display: flex;
justify-content: space-between;
}

.navbar h1 {
margin: 0;
font-size: 26px;
}
.navbar span {
color: #4fc3f7;
}

/* CONTENEDOR */
.container {
max-width: 1100px;
margin: 40px auto;
padding: 0 20px;
}

.grid-2col {
display: grid;
grid-template-columns: 1fr 1fr;
gap: 35px;
}

@media (max-width: 900px) {
.grid-2col {
grid-template-columns: 1fr;
}
}

/* CARDS */
.card {
background: white;
border-radius: 14px;
padding: 35px;
box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}

/* TITULOS */
.card h2 {
text-align: center;
font-size: 24px;
margin-bottom: 10px;
color: #0b5687;
}

.card p {
text-align: center;
color: #64748b;
font-size: 14px;
margin-bottom: 25px;
}

/* FORM */
.form-group {
margin-bottom: 18px;
}

label {
font-weight: 600;
font-size: 14px;
}

input {
width: 100%;
padding: 13px;
border-radius: 8px;
border: 1px solid #cbd5e1;
background: #f8fafc;
margin-top: 5px;
transition: all 0.25s ease;
}

input:focus {
background: white;
border-color: #0b5687;
box-shadow: 0 0 0 3px rgba(11,86,135,0.15);
outline: none;
transform: scale(1.02);
}

/* BOTON */
.btn-submit {
width: 100%;
padding: 14px;
border-radius: 8px;
font-size: 15px;
background: linear-gradient(135deg, #0b5687, #0a4a73);
color: white;
border: none;
cursor: pointer;
box-shadow: 0 6px 12px rgba(11,86,135,0.25);
transition: all 0.25s ease;
position: relative;
}

.btn-submit:hover {
transform: translateY(-2px);
box-shadow: 0 10px 18px rgba(11,86,135,0.35);
}

/* LOADING BOTON */
.btn-submit.loading {
pointer-events: none;
opacity: 0.7;
}

.btn-submit.loading::after {
content: "";
width: 18px;
height: 18px;
border: 3px solid white;
border-top: 3px solid transparent;
border-radius: 50%;
position: absolute;
right: 15px;
top: 50%;
transform: translateY(-50%);
animation: spin 0.8s linear infinite;
}

@keyframes spin {
to { transform: translateY(-50%) rotate(360deg); }
}

/* ALERTA LOGIN */
.alert-error {
background: #fee2e2;
color: #991b1b;
padding: 12px;
border-radius: 8px;
font-size: 13px;
margin-bottom: 15px;
text-align: center;
animation: fadeIn 0.4s ease;
}

/* ACCIONES */
.quick-actions {
display: grid;
grid-template-columns: repeat(2, 1fr);
gap: 15px;
margin-bottom: 20px;
}

.action-card {
background: #fff;
border: 1px solid #cbd5e1;
border-radius: 10px;
padding: 20px 10px;
text-align: center;
text-decoration: none;
color: #1e293b;
font-weight: 600;
font-size: 13px;
transition: all 0.25s ease;
}

.action-card:hover {
background: #0b5687;
color: white;
transform: translateY(-4px);
}

.icon {
font-size: 24px;
display: block;
margin-bottom: 5px;
}

/* BLOQUES */
.seccion-bloque {
background: #f8fafc;
border-radius: 10px;
padding: 22px;
margin-bottom: 20px;
}

.seccion-bloque h3 {
margin-top: 0;
color: #0b5687;
}

/* ALERTA */
.news-box {
background: #fffbeb;
border-left: 5px solid #eab308;
padding: 18px;
border-radius: 10px;
margin-bottom: 20px;
}

/* DIRECTORIO */
.directory-grid {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
gap: 12px;
}

.directory-item {
background: white;
padding: 15px;
border-radius: 8px;
}

.directory-item h4 {
margin: 0;
font-size: 12px;
color: #64748b;
}

.directory-item p {
margin: 5px 0 0 0;
color: #0b5687;
font-weight: bold;
}

/* FOOTER */
footer {
text-align: center;
font-size: 12px;
color: #94a3b8;
margin: 40px 0 20px;
}
</style>
</head>

<body>

<div class="portal-header">
<div class="container-top">
<div>📍 Mendoza, Argentina</div>
<div>
<a href="#">Cartilla Médica</a>
<a href="#">Sedes</a>
</div>
</div>
</div>

<header class="navbar">
<h1>MediFlow<span>•</span></h1>
<div>Portal de Salud</div>
</header>

<main class="container">
<div class="grid-2col">

<!-- LOGIN -->
<div class="card">
<h2>Portal de Autogestión</h2>
<p>Accedé a turnos, recetas y gestiones médicas</p>

<?php if (isset($_GET['error'])): ?>
<div class="alert-error">
❌ Usuario o contraseña incorrectos
</div>
<?php endif; ?>

<form action="../controlador/loginControlador.php" method="POST" onsubmit="activarLoading(this)">
<div class="form-group">
<label>Email</label>
<input type="email" name="email" placeholder="ejemplo@test.com" required>
</div>

<div class="form-group">
<label>Contraseña</label>
<input type="password" name="password" required>
</div>

<button class="btn-submit">Ingresar</button>
</form>

<p style="font-size:12px; margin-top:10px;">
🔐 Conexión segura - Tus datos están protegidos
</p>

<div style="text-align:center; margin-top:10px;">
<a href="#" style="font-size:13px; color:#0b5687;">¿Olvidaste tu contraseña?</a>
</div>
</div>

<!-- INFO -->
<div class="card">
<h2>Servicios al Afiliado</h2>

<div class="quick-actions">
<a href="#" class="action-card"><span class="icon">📋</span>Cartilla</a>
<a href="#" class="action-card"><span class="icon">📅</span>Turnos</a>
<a href="#" class="action-card"><span class="icon">💳</span>Pagos</a>
<a href="#" class="action-card"><span class="icon">💊</span>Recetas</a>
</div>

<div class="news-box">
<strong>📢 Importante</strong><br>
Recordá mantener actualizados tus datos para evitar demoras en trámites.
</div>

<div class="seccion-bloque">
<h3>Últimas Novedades</h3>
<ul style="font-size:13px; color:#475569;">
<li>Cartilla médica actualizada 2026</li>
<li>Nuevo sistema de turnos online</li>
<li>Ampliación de cobertura</li>
</ul>
</div>

<div class="seccion-bloque">
<h3>Contacto</h3>
<div class="directory-grid">
<div class="directory-item">
<h4>Turnos</h4>
<p>0810-888-3569</p>
</div>
<div class="directory-item">
<h4>Urgencias</h4>
<p>0800-999-1911</p>
</div>
<div class="directory-item">
<h4>Soporte</h4>
<p>soporte@mediflow.com</p>
</div>
</div>
</div>

</div>

</div>
</main>

<footer>
MediFlow © 2026 · Sistema de Gestión de Salud <br>
Created by: Grupo01-LDDS-UCH
</footer>

<script>
function activarLoading(form) {
const btn = form.querySelector("button");
btn.classList.add("loading");
}
</script>
<?php if (strtolower(trim($_SESSION['usuario']['rol'] ?? '')) == 'medico'): ?>
    <a href="vista/notificaciones.php" class="btn-menu">🔔 Notificaciones de Auditoría</a>
<?php endif; ?>
</body>
</html>