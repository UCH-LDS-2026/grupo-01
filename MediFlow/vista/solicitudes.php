<?php
session_start();
$rol = $_SESSION['usuario']['rol'] ?? '';
$rolNormalizado = strtolower(trim($rol));

if (!$rol) { header("Location: login.php"); exit; }

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

$pacientes = $conexion->query("SELECT * FROM paciente")->fetch_all(MYSQLI_ASSOC);
$practicas = $conexion->query("SELECT * FROM practica")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar Solicitud</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .container-ancho { max-width: 1000px; margin: 0 auto; padding: 20px; }
        
        .panel-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .panel-header { font-size: 20px; font-weight: 600; color: #0c4a6e; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-col { display: flex; flex-direction: column; position: relative; }
        .form-col.full-width { grid-column: 1 / -1; }
        .form-col label { font-size: 13px; color: #475569; margin-bottom: 8px; font-weight: 600; }
        
        .form-col input:not([type="file"]), .form-col select, .form-col textarea { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; width: 100%; transition: all 0.2s; outline: none; box-sizing: border-box; }
        .form-col input:focus, .form-col select:focus, .form-col textarea:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
        .input-bloqueado { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; border-color: #cbd5e1; font-weight: 500; }
        
        .form-col textarea { resize: none; min-height: 90px; overflow-y: hidden; line-height: 1.5; }
        .char-counter { font-size: 11px; color: #94a3b8; text-align: right; margin-top: 4px; }

        .custom-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; max-height: 180px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin-top: 4px; }
        .custom-option { padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
        .custom-option:hover { background-color: #f0f9ff; color: #0284c7; }
        .custom-option span.right-text { font-size: 12px; color: #64748b; float: right; }

        .data-card { background: #f8fafc; border: 1px dashed #0ea5e9; border-radius: 8px; padding: 15px; margin-top: 10px; display: none; }
        .data-card-title { font-size: 12px; font-weight: 700; color: #0284c7; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px; }
        .data-card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
        .info-subgroup { display: flex; flex-direction: column; background: #fff; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .info-sublabel { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
        .info-subvalue { font-size: 13px; color: #1e293b; font-weight: 500; margin-top: 2px; }
        
        .btn-cargar { background-color: #0c4a6e; color: white; padding: 14px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 16px; width: 100%; transition: background-color 0.2s; margin-top: 10px; }
        .btn-cargar:hover { background-color: #075985; }
        
        /* Drag & Drop Múltiple */
        .drop-zone { min-height: 130px; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; cursor: pointer; color: #64748b; border: 2px dashed #cbd5e1; border-radius: 8px; background-color: #f8fafc; transition: all 0.3s ease; box-sizing: border-box; }
        .drop-zone--over { border-style: solid; border-color: #0ea5e9; background-color: #f0f9ff; color: #0284c7; }
        .drop-zone__input { display: none; }
        
        /* Cuadrícula de Vista Previa Múltiple */
        .preview-container { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px; width: 100%; justify-content: center; }
        .preview-item { position: relative; width: 100px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .preview-item img { max-width: 100%; height: 60px; object-fit: cover; border-radius: 4px; }
        .preview-item .file-icon { font-size: 35px; margin-bottom: 5px; display: block; color: #64748b; }
        .preview-item .file-name { font-size: 10px; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 5px; }
        
        .btn-quitar-item { position: absolute; top: -8px; right: -8px; background-color: #ef4444; color: white; border: none; border-radius: 50%; width: 22px; height: 22px; font-size: 11px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: transform 0.2s; }
        .btn-quitar-item:hover { background-color: #dc2626; transform: scale(1.1); }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>MediFlow <span>• Módulo de Carga</span></h1>
        <a href="../index.php" class="btn-back">Volver al Inicio</a>
    </div>

    <div class="container-ancho">
<?php if ($rolNormalizado !== 'paciente'): ?>
    <div class="panel-box">
            <div class="panel-header">Cargar Nueva Solicitud Médica</div>
            <form action="../controlador/solicitudControlador.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">
                
                <div class="form-grid">
                    
                    <div class="form-col full-width" id="paciente-scope">
                        <label>Buscar Afiliado por DNI</label>
                        <input type="text" id="inputDNI" placeholder="Escriba el número de DNI del paciente..." autocomplete="off" required>
                        <input type="hidden" name="id_paciente" id="id_paciente_oculto" required>
                        
                        <div class="custom-dropdown" id="dniDropdown">
                            <?php foreach($pacientes as $p): ?>
                                <div class="custom-option dni-option" data-id="<?php echo $p['id_paciente']; ?>" data-dni="<?php echo htmlspecialchars($p['dni']); ?>" data-json='<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                    👤 <strong><?php echo htmlspecialchars($p['dni']); ?></strong> 
                                    <span class="right-text"><?php echo htmlspecialchars($p['apellido'] . ', ' . $p['nombre']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="data-card" id="pacienteCardCompleta">
                            <div class="data-card-title">📋 Datos Completos del Afiliado</div>
                            <div class="data-card-grid" id="pacienteDataGrid"></div>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <label>Médico Informante / Solicitante</label>
                        <input type="text" class="input-bloqueado" value="<?php echo htmlspecialchars(($_SESSION['usuario']['apellido'] ?? '') . ', ' . ($_SESSION['usuario']['nombre'] ?? '') . ' (' . ucfirst($rol) . ')'); ?>" readonly>
                        <input type="hidden" name="id_medico" value="<?php echo htmlspecialchars($_SESSION['usuario']['id_usuario'] ?? ''); ?>" required>
                    </div>

                    <div class="form-col" id="practica-scope">
                        <label>Práctica Requerida</label>
                        <input type="text" id="inputPractica" placeholder="Escriba para buscar práctica médica..." required autocomplete="off">
                        <input type="hidden" name="id_practica" id="id_practica_oculto" required>
                        
                        <div class="custom-dropdown" id="practicaDropdown">
                            <?php foreach($practicas as $pr): ?>
                                <div class="custom-option practica-option" data-id="<?php echo $pr['id_practica']; ?>" data-display="<?php echo htmlspecialchars($pr['nombre']); ?>" data-json='<?php echo json_encode($pr, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                    🔬 <strong><?php echo htmlspecialchars($pr['nombre']); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="data-card" id="practicaCardCompleta">
                            <div class="data-card-title">🔬 Detalles de la Práctica Médica</div>
                            <div class="data-card-grid" id="practicaDataGrid"></div>
                        </div>
                    </div>

                    <div class="form-col">
                        <label>Fecha de Solicitud</label>
                        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-col full-width">
                        <label>Prioridad</label>
                        <select name="prioridad" required>
                            <option value="baja">🟢 Baja</option>
                            <option value="media" selected>🟡 Media</option>
                            <option value="alta">🔴 Alta / Urgente</option>
                        </select>
                    </div>

                    <div class="form-col full-width">
                        <label>Diagnóstico (Justificación Clínica)</label>
                        <textarea name="diagnostico" id="diagnosticoTextArea" required placeholder="Escriba detalladamente la justificación médica..." maxlength="1000"></textarea>
                        <div class="char-counter"><span id="charCount">0</span> / 1000 caracteres</div>
                    </div>

                    <!-- Drag & Drop Zone Múltiple -->
                    <div class="form-col full-width">
                        <label>Estudios / Recetas Adjuntas (Opcional)</label>
                        <div class="drop-zone" id="drop-zone">
                            <span class="drop-zone__prompt" id="dropPrompt">
                                📥 Arrastra tus archivos aquí o haz clic para examinar
                                <span style="display:block; font-size:12px; color:#94a3b8; margin-top:5px;">Formatos: JPG, PNG, PDF. <b>Puedes subir varios archivos.</b></span>
                            </span>
                            <!-- Importante: atributo name[] y multiple -->
                            <input type="file" name="adjuntos[]" id="archivo" class="drop-zone__input" accept=".jpg,.jpeg,.png,.pdf" multiple>
                            <div class="preview-container" id="previewContainer"></div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-cargar">Registrar Solicitud en la Obra Social</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // --- Lógica Textarea Autoexpandible ---
        const tx = document.getElementById('diagnosticoTextArea');
        const charCount = document.getElementById('charCount');
        tx.addEventListener("input", function() {
            this.style.height = "auto";
            this.style.height = this.scrollHeight + "px";
            charCount.textContent = this.value.length;
        });

        function renderFichaData(dataJson, gridElement, cardElement, idAExcluir) {
            gridElement.innerHTML = '';
            for (const [columna, valor] of Object.entries(dataJson)) {
                if(columna === idAExcluir) continue;
                const bloque = document.createElement('div');
                bloque.className = 'info-subgroup';
                bloque.innerHTML = `<span class="info-sublabel">${columna.toUpperCase().replace('_', ' ')}</span><span class="info-subvalue">${valor ? valor : '---'}</span>`;
                gridElement.appendChild(bloque);
            }
            cardElement.style.display = 'block';
        }

        // --- Lógica Dropdowns (Paciente y Práctica) ---
        const setupDropdown = (inputId, hiddenId, dropdownId, optionsClass, cardId, gridId, idField) => {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            const dropdown = document.getElementById(dropdownId);
            const options = document.querySelectorAll(optionsClass);
            const card = document.getElementById(cardId);
            const grid = document.getElementById(gridId);

            input.addEventListener('focus', () => { dropdown.style.display = 'block'; });
            input.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                dropdown.style.display = 'block'; hidden.value = ''; card.style.display = 'none';
                options.forEach(opt => {
                    const text = (opt.getAttribute('data-dni') || opt.textContent).toLowerCase();
                    opt.style.display = text.includes(query) ? 'block' : 'none';
                });
            });

            options.forEach(opt => {
                opt.addEventListener('click', function() {
                    input.value = this.getAttribute('data-dni') || this.getAttribute('data-display');
                    hidden.value = this.getAttribute('data-id');
                    dropdown.style.display = 'none';
                    renderFichaData(JSON.parse(this.getAttribute('data-json')), grid, card, idField);
                });
            });

            document.addEventListener('click', (e) => {
                if (!input.parentElement.contains(e.target)) {
                    dropdown.style.display = 'none';
                    if (!hidden.value) { input.value = ''; card.style.display = 'none'; }
                }
            });
        };

        setupDropdown('inputDNI', 'id_paciente_oculto', 'dniDropdown', '.dni-option', 'pacienteCardCompleta', 'pacienteDataGrid', 'id_paciente');
        setupDropdown('inputPractica', 'id_practica_oculto', 'practicaDropdown', '.practica-option', 'practicaCardCompleta', 'practicaDataGrid', 'id_practica');

        // --- Lógica Drag & Drop MULTIPLE con DataTransfer ---
        const inputElement = document.getElementById("archivo");
        const dropZoneElement = document.getElementById("drop-zone");
        const previewContainer = document.getElementById("previewContainer");
        const dropPrompt = document.getElementById("dropPrompt");
        
        let dataTransfer = new DataTransfer(); // Contenedor dinámico de archivos

        dropZoneElement.addEventListener("click", (e) => {
            // Evitar que el click en el botón "X" abra la ventana de archivos
            if(e.target.closest('.btn-quitar-item')) return;
            inputElement.click();
        });

        inputElement.addEventListener("change", () => {
            if (inputElement.files.length) procesarArchivos(inputElement.files);
        });

        dropZoneElement.addEventListener("dragover", (e) => {
            e.preventDefault(); dropZoneElement.classList.add("drop-zone--over");
        });

        ["dragleave", "dragend"].forEach(type => {
            dropZoneElement.addEventListener(type, () => dropZoneElement.classList.remove("drop-zone--over"));
        });

        dropZoneElement.addEventListener("drop", (e) => {
            e.preventDefault();
            dropZoneElement.classList.remove("drop-zone--over");
            if (e.dataTransfer.files.length) procesarArchivos(e.dataTransfer.files);
        });

        function procesarArchivos(files) {
            // Añadir nuevos archivos al DataTransfer
            Array.from(files).forEach(file => dataTransfer.items.add(file));
            
            // Actualizar el input real
            inputElement.files = dataTransfer.files;
            actualizarVistaPrevia();
        }

        function actualizarVistaPrevia() {
            previewContainer.innerHTML = "";
            
            if (dataTransfer.files.length > 0) {
                dropPrompt.style.display = "none";
                dropZoneElement.style.borderColor = "#10b981";
                dropZoneElement.style.backgroundColor = "#f0fdf4";
            } else {
                dropPrompt.style.display = "block";
                dropZoneElement.style.borderColor = "#cbd5e1";
                dropZoneElement.style.backgroundColor = "#f8fafc";
            }

            Array.from(dataTransfer.files).forEach((file, index) => {
                const itemDiv = document.createElement("div");
                itemDiv.className = "preview-item";
                
                const btnQuitar = document.createElement("button");
                btnQuitar.className = "btn-quitar-item";
                btnQuitar.innerHTML = "✖";
                btnQuitar.type = "button";
                btnQuitar.title = "Quitar archivo";
                btnQuitar.onclick = (e) => {
                    e.stopPropagation();
                    eliminarArchivo(index);
                };

                const fileName = document.createElement("div");
                fileName.className = "file-name";
                fileName.textContent = file.name;
                fileName.title = file.name;

                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    const img = document.createElement("img");
                    reader.onload = () => img.src = reader.result;
                    reader.readAsDataURL(file);
                    itemDiv.appendChild(img);
                } else {
                    const icon = document.createElement("span");
                    icon.className = "file-icon";
                    icon.textContent = file.type === "application/pdf" ? "📕" : "📄";
                    itemDiv.appendChild(icon);
                }

                itemDiv.appendChild(btnQuitar);
                itemDiv.appendChild(fileName);
                previewContainer.appendChild(itemDiv);
            });
        }

        function eliminarArchivo(index) {
            const dtNuevo = new DataTransfer();
            const files = inputElement.files;
            
            // Copiar todos los archivos excepto el eliminado
            for (let i = 0; i < files.length; i++) {
                if (i !== index) dtNuevo.items.add(files[i]);
            }
            
            inputElement.files = dtNuevo.files;
            dataTransfer = dtNuevo; // Sincronizar nuestro contenedor
            actualizarVistaPrevia();
        }
    </script>
</body>
</html>