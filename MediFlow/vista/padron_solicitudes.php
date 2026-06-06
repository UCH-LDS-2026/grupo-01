<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }

$rol = $_SESSION['usuario']['rol'] ?? '';
$email_usuario = $_SESSION['usuario']['email'] ?? '';
$id_usuario_actual = $_SESSION['usuario']['id_usuario'] ?? 0; // Agregamos el ID
$rolNormalizado = strtolower(trim($rol));

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$solicitudModelo = new Solicitud($conexion);

if ($rolNormalizado == 'paciente') {
    $solicitudes = $solicitudModelo->listarPorEmailPaciente($email_usuario);
} else {
    $solicitudes = $solicitudModelo->listar();
}

// --- NUEVO: ORDENAMIENTO ESPECÍFICO PARA AUDITOR / ADMIN ---
if ($rolNormalizado == 'auditor' || $rolNormalizado == 'admin' || $rolNormalizado == 'administrador') {
    usort($solicitudes, function($a, $b) {
        // 1. Estado: Aprobadas van al final (1), las demás arriba (0)
        $estadoA = strtolower(trim($a['estado'])) == 'aprobada' ? 1 : 0;
        $estadoB = strtolower(trim($b['estado'])) == 'aprobada' ? 1 : 0;
        
        if ($estadoA !== $estadoB) {
            return $estadoA - $estadoB;
        }
        
        // 2. Desempate por Prioridad (Alta = 1, Media = 2, Baja = 3)
        $prioMap = ['alta' => 1, 'media' => 2, 'baja' => 3];
        $pA = $prioMap[strtolower(trim($a['prioridad']))] ?? 4;
        $pB = $prioMap[strtolower(trim($b['prioridad']))] ?? 4;
        
        if ($pA !== $pB) {
            return $pA - $pB;
        }
        
        // 3. Desempate final por fecha (las más nuevas primero)
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
}
// -----------------------------------------------------------

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Padrón de Solicitudes - MediFlow</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; color: #333; }
        .container-ancho { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .panel-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .panel-header { font-size: 20px; font-weight: 600; color: #0c4a6e; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;}
        .search-container { position: relative; width: 400px; }
        .search-input { width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 20px; outline: none; }
        
        /* NUEVO: Estilos para la barra de filtros */
        .filtros-barra { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .filtro-item { display: flex; flex-direction: column; font-size: 12px; font-weight: bold; color: #475569; }
        .filtro-item select, .filtro-item input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; margin-top: 4px; font-size: 13px; }
        /* -------------------------------------- */

        .table-responsive table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 14px; }
        .table-responsive th { padding: 15px 12px; background-color: #f1f5f9; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .table-responsive td { padding: 15px 12px; border-bottom: 1px solid #e2e8f0; }
        .badge { font-weight: 600; padding: 5px 10px; border-radius: 20px; font-size: 12px; text-transform: capitalize; }
        .badge-pendiente { background: #fef08a; color: #854d0e; }
        .badge-observada { background: #fed7aa; color: #9a3412; }
        .badge-aprobada { background: #bbf7d0; color: #166534; }
        .badge-rechazada { background: #fecaca; color: #991b1b; }
        .btn-icon { background: #f1f5f9; color: #0f172a; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold; cursor: pointer; border: none; transition: 0.2s;}
        .btn-icon:hover { opacity: 0.8; }
        .btn-print { background-color: #22c55e; color: white; margin-left: 5px;}
        .paginacion { margin-top: 20px; display: flex; justify-content: center; gap: 8px; }
        .btn-page { background: #f1f5f9; color: #0f172a; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-page.active { background: #0c4a6e; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• <?php echo ($rolNormalizado == 'paciente') ? 'Mis Solicitudes' : 'Padrón General'; ?></span></h1>
        <a href="../index.php" class="btn-back">Volver al Dashboard</a>
    </div>

    <div class="container-ancho">
        <div class="panel-box">
            <div class="panel-header">
                <div>
                    Listado de Registros
                    <?php if ($rolNormalizado == 'medico'): ?>
                        <div style="margin-top: 10px;">
                            <button id="btnFiltroTodas" class="btn-icon" style="background:#0ea5e9; color:white;">Todas las Solicitudes</button>
                            <button id="btnFiltroMis" class="btn-icon">Solo mis Solicitudes</button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="search-container">
                    <input type="text" id="buscadorGlobal" class="search-input" placeholder="Ingrese Nombre, DNI o N° de orden">
                </div>
            </div>

            <div class="filtros-barra">
                <div class="filtro-item">
                    <label>Fecha Desde</label>
                    <input type="date" id="filtroFechaDesde">
                </div>
                <div class="filtro-item">
                    <label>Fecha Hasta</label>
                    <input type="date" id="filtroFechaHasta">
                </div>
                <div class="filtro-item">
                    <label>Estado</label>
                    <select id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="observada">Observada</option>
                        <option value="aprobada">Aprobada</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                </div>
                <div class="filtro-item">
                    <label>Prioridad</label>
                    <select id="filtroPrioridad">
                        <option value="">Todas</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div class="filtro-item" style="justify-content: flex-end;">
                    <button id="btnLimpiarFiltros" class="btn-icon" style="background: #e2e8f0;">Limpiar Filtros</button>
                </div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>N°</th><th>Fecha</th><th>Paciente</th><th>DNI</th><th>Práctica</th><th>Prioridad</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php if (empty($solicitudes)): ?>
                            <tr><td colspan="8" style="text-align:center; padding: 30px;">No hay registros.</td></tr>
                        <?php else: foreach ($solicitudes as $s): 
                            $estado = strtolower(trim($s['estado']));
                            $clase = 'badge-pendiente';
                            if($estado == 'observada') $clase = 'badge-observada';
                            if($estado == 'aprobada') $clase = 'badge-aprobada';
                            if($estado == 'rechazada') $clase = 'badge-rechazada';
                        ?>
                            <tr class="fila-dato" 
                                data-id-medico="<?php echo $s['id_medico'] ?? ''; ?>"
                                data-fecha="<?php echo date('Y-m-d', strtotime($s['fecha'])); ?>"
                                data-estado="<?php echo $estado; ?>"
                                data-prioridad="<?php echo strtolower($s['prioridad']); ?>">
                                
                                <td class="col-num">#<?php echo $s['id_solicitud']; ?></td>
                                <td><?php echo date("d/m/Y", strtotime($s['fecha'])); ?></td>
                                <td class="col-pac"><?php echo htmlspecialchars($s['apellido_paciente'] . ', ' . $s['nombre_paciente']); ?></td>
                                <td class="col-dni"><?php echo htmlspecialchars($s['dni'] ?? '---'); ?></td>
                                <td><?php echo htmlspecialchars($s['nombre_practica'] ?? '---'); ?></td>
                                <td><?php echo ucfirst($s['prioridad']); ?></td>
                                <td><span class="badge <?php echo $clase; ?>"><?php echo htmlspecialchars($s['estado']); ?></span></td>
                                <td>
                                    <a href="ver_solicitud.php?id=<?php echo $s['id_solicitud']; ?>" target="_blank" class="btn-icon">👁️ Ver</a>
                                    <?php if ($estado == 'aprobada'): ?>
                                        <a href="imprimir_solicitud.php?id=<?php echo $s['id_solicitud']; ?>" target="_blank" class="btn-icon btn-print">🖨️ Orden</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div id="paginacion" class="paginacion"></div>
            <div id="infoPaginacion" style="text-align: center; color: #64748b; font-size: 13px; margin-top: 10px;"></div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const buscadorSol = document.getElementById('buscadorGlobal');
            const filas = Array.from(document.querySelectorAll('.fila-dato'));
            const paginacionDiv = document.getElementById('paginacion');
            const infoPaginacion = document.getElementById('infoPaginacion');
            
            const btnMis = document.getElementById('btnFiltroMis');
            const btnTodas = document.getElementById('btnFiltroTodas');

            // NUEVO: Elementos de los filtros
            const filtroFechaDesde = document.getElementById('filtroFechaDesde');
            const filtroFechaHasta = document.getElementById('filtroFechaHasta');
            const filtroEstado = document.getElementById('filtroEstado');
            const filtroPrioridad = document.getElementById('filtroPrioridad');
            const btnLimpiar = document.getElementById('btnLimpiarFiltros');

            const idUsuarioActual = "<?php echo $id_usuario_actual; ?>";
            let filtroActivo = 'todas'; // puede ser 'todas' o 'mis'
            
            // Configuración de paginación
            let currentPage = 1;
            const rowsPerPage = 10;

            function actualizarVista() {
                const query = buscadorSol ? buscadorSol.value.toLowerCase().trim() : '';
                
                // NUEVO: Capturar valores de los filtros
                const vDesde = filtroFechaDesde.value;
                const vHasta = filtroFechaHasta.value;
                const vEstado = filtroEstado.value;
                const vPrioridad = filtroPrioridad.value;

                let filasFiltradas = [];

                // 1. Aplicamos los filtros de búsqueda y botón
                filas.forEach(fila => {
                    const textoFila = fila.textContent.toLowerCase();
                    const idMedicoFila = fila.getAttribute('data-id-medico');
                    const rowFecha = fila.getAttribute('data-fecha');
                    const rowEstado = fila.getAttribute('data-estado');
                    const rowPrioridad = fila.getAttribute('data-prioridad');
                    
                    let coincideBusqueda = query === '' || textoFila.includes(query);
                    let coincideMedico = (filtroActivo === 'todas') || (filtroActivo === 'mis' && idMedicoFila === idUsuarioActual);
                    
                    // NUEVO: Condiciones de los filtros avanzados
                    let coincideDesde = vDesde === '' || rowFecha >= vDesde;
                    let coincideHasta = vHasta === '' || rowFecha <= vHasta;
                    let coincideEstado = vEstado === '' || rowEstado === vEstado;
                    let coincidePrioridad = vPrioridad === '' || rowPrioridad === vPrioridad;

                    if (coincideBusqueda && coincideMedico && coincideDesde && coincideHasta && coincideEstado && coincidePrioridad) {
                        filasFiltradas.push(fila);
                        fila.style.display = ''; // Visible temporalmente para la paginación
                    } else {
                        fila.style.display = 'none';
                    }
                });

                // 2. Aplicamos la paginación sobre las filas resultantes
                const totalPages = Math.ceil(filasFiltradas.length / rowsPerPage);
                if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;

                const startIndex = (currentPage - 1) * rowsPerPage;
                const endIndex = startIndex + rowsPerPage;

                filasFiltradas.forEach((fila, index) => {
                    if (index >= startIndex && index < endIndex) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });

                // 3. Dibujamos los botones de paginación
                renderPaginacion(totalPages, filasFiltradas.length);
            }

            function renderPaginacion(totalPages, totalRows) {
                paginacionDiv.innerHTML = '';
                
                if (totalRows === 0) {
                    infoPaginacion.textContent = 'No hay resultados.';
                    return;
                }

                infoPaginacion.textContent = `Mostrando página ${currentPage} de ${totalPages} (${totalRows} registros en total)`;

                if (totalPages <= 1) return;

                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.className = `btn-page ${i === currentPage ? 'active' : ''}`;
                    btn.onclick = () => {
                        currentPage = i;
                        actualizarVista();
                    };
                    paginacionDiv.appendChild(btn);
                }
            }

            // Eventos
            if (buscadorSol) {
                buscadorSol.addEventListener('input', () => { currentPage = 1; actualizarVista(); });
            }

            // NUEVO: Eventos de los filtros avanzados
            [filtroFechaDesde, filtroFechaHasta, filtroEstado, filtroPrioridad].forEach(filtro => {
                filtro.addEventListener('change', () => { currentPage = 1; actualizarVista(); });
            });

            btnLimpiar.addEventListener('click', () => {
                filtroFechaDesde.value = '';
                filtroFechaHasta.value = '';
                filtroEstado.value = '';
                filtroPrioridad.value = '';
                if(buscadorSol) buscadorSol.value = '';
                currentPage = 1;
                actualizarVista();
            });

            if (btnMis && btnTodas) {
                btnMis.addEventListener('click', () => {
                    filtroActivo = 'mis';
                    btnMis.style.background = '#0ea5e9'; btnMis.style.color = 'white';
                    btnTodas.style.background = '#f1f5f9'; btnTodas.style.color = '#0f172a';
                    currentPage = 1;
                    actualizarVista();
                });

                btnTodas.addEventListener('click', () => {
                    filtroActivo = 'todas';
                    btnTodas.style.background = '#0ea5e9'; btnTodas.style.color = 'white';
                    btnMis.style.background = '#f1f5f9'; btnMis.style.color = '#0f172a';
                    currentPage = 1;
                    actualizarVista();
                });
            }

            // Iniciar por primera vez
            actualizarVista();
        });
    </script>
</body>
</html>