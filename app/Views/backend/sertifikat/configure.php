<?= $this->extend('backend/template/template'); ?>
<?= $this->section('content'); ?>
<section class="content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cog"></i> Konfigurasi Sertifikat - Halaman <?= ucfirst($halaman) ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Template:</strong> <?= $template['width'] ?>x<?= $template['height'] ?>px (<?= ucfirst($template['orientation']) ?>)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Panel: Configuration -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Field Configuration</h3>
                    </div>
                    <div class="card-body">
                        <!-- Help Card -->
                        <div class="card card-warning card-outline collapsed-card mb-3">
                            <div class="card-header py-2">
                                <h6 class="card-title mb-0"><i class="fas fa-question-circle text-warning"></i> Panduan</h6>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-2" style="display: none;">
                                <small class="text-muted">
                                    <ul class="mb-0 pl-3">
                                        <li><strong>Klik field</strong> di canvas untuk memilih</li>
                                        <li><strong>Drag & drop</strong> untuk memindahkan posisi</li>
                                        <li><strong>Resize handle</strong> (kotak biru) untuk mengubah ukuran font</li>
                                        <li><strong>Arrow keys</strong> untuk pergerakan presisi (+ Shift = 10px)</li>
                                        <li><strong>Zoom:</strong> Tombol +/- atau Ctrl+Scroll</li>
                                        <li><strong>Pan:</strong> Space+Drag atau Middle Mouse</li>
                                        <li><strong>Mobile:</strong> Pinch untuk zoom, drag untuk geser</li>
                                        <li>Klik <strong>Preview PDF</strong> untuk melihat hasil akhir</li>
                                    </ul>
                                </small>
                            </div>
                        </div>

                         <!-- Section: Fields -->
                         <div class="form-group">
                            <label>Tambah Field</label>
                            <select class="form-control" id="selectField">
                                <option value="">-- Pilih Field --</option>
                                <?php foreach ($available_fields as $af): ?>
                                    <option value="<?= $af['name'] ?>" 
                                            data-label="<?= $af['label'] ?>" 
                                            data-sample="<?= $af['sample'] ?>">
                                        <?= $af['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-sm btn-success mt-2" id="btnAddField">
                                <i class="fas fa-plus"></i> Tambah Field
                            </button>
                        </div>
                        <hr>
                        <div id="fieldsList" style="max-height: 400px; overflow-y: auto;">
                            <!-- Fields will be added here dynamically -->
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary btn-block mb-2" id="btnSaveConfig">
                            <i class="fas fa-save"></i> Simpan Konfigurasi
                        </button>
                        <a href="<?= base_url('backend/sertifikat/preview/' . $halaman) ?>" target="_blank" class="btn btn-info btn-block mb-2">
                             <i class="fas fa-eye"></i> Preview PDF
                        </a>
                        <a href="<?= base_url('backend/sertifikat') ?>" 
                           class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Canvas -->
            <div class="col-md-8">
                 <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-eye"></i> Preview Template</h3>
                        <div class="card-tools">
                            <!-- Zoom Controls -->
                            <button type="button" class="btn btn-sm btn-secondary" id="btnZoomOut" title="Zoom Out">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <span class="badge badge-light mx-1" id="zoomIndicator">100%</span>
                            <button type="button" class="btn btn-sm btn-secondary" id="btnZoomIn" title="Zoom In">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary ml-1" id="btnZoomReset" title="Reset Zoom">
                                <i class="fas fa-expand-arrows-alt"></i>
                            </button>
                            <button type="button" class="btn btn-tool ml-2" id="btnTogglePreview">
                                <i class="fas fa-sync"></i> Refresh Canvas
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="canvasContainer" style="background: #f4f4f4; overflow: auto; text-align: center; position: relative; cursor: grab; max-height: calc(100vh - 200px);">
                        <div id="canvasWrapper" style="position: relative; display: inline-block; transform-origin: center top; transition: transform 0.1s ease-out;">
                            <canvas id="templateCanvas" 
                                    width="<?= $template['width'] ?>" 
                                    height="<?= $template['height'] ?>"
                                    style="border: 1px solid #ddd; cursor: crosshair; max-width: none; height: auto;">
                            </canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<input type="hidden" id="templateId" value="<?= $template['id'] ?>">
<input type="hidden" id="templatePath" value="<?= base_url('uploads/' . $template['file_template']) ?>">

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    let canvas, ctx;
    let templateImage = new Image();
    let fields = [];
    let selectedFieldIndex = -1;
    let isDragging = false;
    let isResizing = false;
    let resizeStartY = 0;
    let resizeStartFontSize = 0;
    let dragOffset = {x: 0, y: 0};
    var hasUnsavedChanges = false;

    // Zoom and Pan variables
    let zoomLevel = 1.0;
    let panX = 0, panY = 0;
    let isPanning = false;
    let panStartX = 0, panStartY = 0;
    let lastTouchDistance = 0;

    $(document).ready(function() {
        canvas = document.getElementById('templateCanvas');
        ctx = canvas.getContext('2d');
        
        // Load template image
        templateImage.src = $('#templatePath').val();
        templateImage.onload = function() {
            drawCanvas();
            // Auto-fit canvas to viewport on initial load
            setTimeout(function() {
                fitCanvasToViewport();
                centerCanvas();
            }, 100);
        };

        // Load existing fields from DB
        <?php if (!empty($fields)): ?>
        var dbFields = <?= json_encode($fields) ?>;
        fields = dbFields.map(function(f) {
            // Parse border_settings JSON
            var borderSettings = {enabled: false, color: '#000000', width: 1};
            if (f.border_settings) {
                try {
                    borderSettings = typeof f.border_settings === 'string' 
                        ? JSON.parse(f.border_settings) 
                        : f.border_settings;
                    console.log('Loaded border_settings for', f.field_name, ':', borderSettings);
                } catch(e) {
                    console.error('Error parsing border_settings:', e);
                }
            }
            
            return {
                name: f.field_name,
                label: f.field_label,
                sample: getSampleText(f.field_name, f.field_label), 
                x: parseFloat(f.pos_x),
                y: parseFloat(f.pos_y),
                font_family: f.font_family || 'Arial',
                font_size: parseInt(f.font_size),
                font_style: f.font_style,
                text_align: f.text_align,
                text_color: f.text_color,
                max_width: parseInt(f.max_width),
                has_border: borderSettings.enabled || false,
                border_color: borderSettings.color || '#000000',
                border_width: parseInt(borderSettings.width) || 1
            };
        });
        <?php endif; ?>

        renderFieldsList();

        // Add field button
        $('#btnAddField').click(function() {
            var selectedOption = $('#selectField option:selected');
            var fieldName = selectedOption.val();
            
            if (!fieldName) {
                Swal.fire('Perhatian', 'Pilih field terlebih dahulu', 'warning');
                return;
            }

            // Check if field already exists
            if (fields.find(f => f.name === fieldName)) {
                Swal.fire('Perhatian', 'Field sudah ditambahkan', 'warning');
                return;
            }

            var field = {
                name: fieldName,
                label: selectedOption.data('label'),
                sample: selectedOption.data('sample'),
                x: 100,
                y: 100,
                font_family: 'Arial',
                font_size: 24,
                font_style: 'B',
                text_align: 'C',
                text_color: '#000000',
                max_width: 0,
                has_border: false,
                border_color: '#000000',
                border_width: 1
            };

            fields.push(field);
            renderFieldsList();
            drawCanvas();
            markAsDirty();
            
            $('#selectField').val('');
        });

        // Save Config
        $('#btnSaveConfig').click(function() {
            if (fields.length === 0) {
                Swal.fire('Perhatian', 'Tambahkan minimal 1 field', 'warning');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            // Debug: Log data yang akan dikirim
            console.log('Saving fields:', fields);
            console.log('Fields with border info:', fields.map(f => ({
                name: f.name,
                has_border: f.has_border,
                has_border_type: typeof f.has_border,
                border_color: f.border_color,
                border_width: f.border_width
            })));

            $.ajax({
                url: '<?= base_url('backend/sertifikat/save-config') ?>',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    template_id: $('#templateId').val(),
                    fields: fields
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        hasUnsavedChanges = false;
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Konfigurasi');
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Konfigurasi');
                }
            });
        });

        // Toggle Preview / Refresh
        $('#btnTogglePreview').click(function() {
            drawCanvas();
        });

        // --- Canvas Interactions ---

        // Mouse Down
        canvas.addEventListener('mousedown', function(e) {
            e.preventDefault();
            var rect = canvas.getBoundingClientRect();
            var scaleX = canvas.width / rect.width;
            var scaleY = canvas.height / rect.height;
            var mouseX = (e.clientX - rect.left) * scaleX;
            var mouseY = (e.clientY - rect.top) * scaleY;

            // 1. Check resize handle
            if (selectedFieldIndex >= 0) {
                var field = fields[selectedFieldIndex];
                if (field._handleBounds) {
                    var h = field._handleBounds;
                    if (mouseX >= h.x && mouseX <= h.x + h.width &&
                        mouseY >= h.y && mouseY <= h.y + h.height) {
                        isResizing = true;
                        resizeStartY = mouseY;
                        resizeStartFontSize = field.font_size;
                        return;
                    }
                }
            }

            // 2. Check field selection
            for (let i = fields.length - 1; i >= 0; i--) {
                var field = fields[i];
                var dims = getFieldDimensions(field);
                
                // Hit testing with some padding
                // Hit testing with some padding
                if (field.name === 'block_table') {
                    // Similar logic to below but using specific dimensions
                    var boxHeight = Math.max(field.font_size * 8, 150);
                    var boxWidth = canvas.width - (field.x * 2);
                    if (boxWidth < 300) boxWidth = 300;

                    if (mouseX >= field.x && mouseX <= field.x + boxWidth &&
                        mouseY >= field.y && mouseY <= field.y + boxHeight) {
                        
                        selectedFieldIndex = i;
                        isDragging = true;
                        dragOffset.x = mouseX - field.x;
                        dragOffset.y = mouseY - field.y;
                        drawCanvas();
                        renderFieldsList();
                        break;
                    }

                } else if (field.name.startsWith('block_')) {
                    var boxHeight = field.font_size * 5;
                    var boxWidth = canvas.width * 0.8;
                     var x = field.x;
                    if (field.text_align === 'C') x -= boxWidth / 2;
                    
                    if (mouseX >= x && mouseX <= x + boxWidth &&
                        mouseY >= field.y && mouseY <= field.y + boxHeight) {
                        selectedFieldIndex = i;
                        isDragging = true;
                        dragOffset.x = mouseX - field.x;
                        dragOffset.y = mouseY - field.y;
                        drawCanvas();
                        renderFieldsList();
                        break;
                    }
                } else if (mouseX >= dims.x - 10 && mouseX <= dims.x + dims.width + 10 &&
                    mouseY >= dims.y - 10 && mouseY <= dims.y + dims.height + 10) {
                    
                    selectedFieldIndex = i;
                    isDragging = true;
                    dragOffset.x = mouseX - field.x;
                    dragOffset.y = mouseY - field.y;
                    
                    drawCanvas();
                    renderFieldsList(); // Update UI selection
                    break;
                }
            }
        });

        // Mouse Move
        document.addEventListener('mousemove', function(e) {
            if (!isDragging && !isResizing) return;

            var rect = canvas.getBoundingClientRect();
            var scaleX = canvas.width / rect.width;
            var scaleY = canvas.height / rect.height;
            var mouseX = (e.clientX - rect.left) * scaleX;
            var mouseY = (e.clientY - rect.top) * scaleY;

            if (isResizing && selectedFieldIndex >= 0) {
                var deltaY = mouseY - resizeStartY;
                var newFontSize = Math.max(8, Math.min(200, resizeStartFontSize + Math.round(deltaY / 2)));
                fields[selectedFieldIndex].font_size = newFontSize;
                
                drawCanvas();
                renderFieldsList(); // Update form values
                markAsDirty();
            } else if (isDragging && selectedFieldIndex >= 0) {
                var newX = mouseX - dragOffset.x;
                var newY = mouseY - dragOffset.y;
                
                fields[selectedFieldIndex].x = Math.round(newX);
                fields[selectedFieldIndex].y = Math.round(newY);
                
                drawCanvas();
                renderFieldsList(); // Update form values
                markAsDirty();
            }
        });

        // Mouse Up
        document.addEventListener('mouseup', function(e) {
            isDragging = false;
            isResizing = false;
        });

        // Keyboard support
        $(document).keydown(function(e) {
            if (selectedFieldIndex < 0) return;
            if ($(e.target).is('input, select, textarea')) return;
            
            var step = e.shiftKey ? 10 : 1;
            var moved = false;
            
            switch(e.keyCode) {
                case 37: fields[selectedFieldIndex].x -= step; moved = true; break; // Left
                case 38: fields[selectedFieldIndex].y -= step; moved = true; break; // Up
                case 39: fields[selectedFieldIndex].x += step; moved = true; break; // Right
                case 40: fields[selectedFieldIndex].y += step; moved = true; break; // Down
                case 46: // Delete
                    removeField(selectedFieldIndex);
                    return;
            }
            
            if (moved) {
                e.preventDefault();
                drawCanvas();
                renderFieldsList();
                markAsDirty();
            }
        });

        // ========== ZOOM AND PAN INITIALIZATION ==========
        
        // Zoom button handlers
        $('#btnZoomIn').click(function() {
            setZoom(zoomLevel + 0.25);
        });

        $('#btnZoomOut').click(function() {
            setZoom(zoomLevel - 0.25);
        });

        $('#btnZoomReset').click(function() {
            panX = 0;
            panY = 0;
            fitCanvasToViewport();
            // Center the canvas after fit
            setTimeout(function() {
                centerCanvas();
            }, 50);
        });

        // Mouse wheel zoom
        const canvasContainer = document.getElementById('canvasContainer');
        canvasContainer.addEventListener('wheel', function(e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? -0.1 : 0.1;
                setZoom(zoomLevel + delta);
            }
        }, { passive: false });

        // Pan with middle mouse button or space+drag
        let spacePressed = false;
        document.addEventListener('keydown', function(e) {
            if (e.code === 'Space' && !e.repeat) {
                spacePressed = true;
                canvasContainer.style.cursor = 'grab';
            }
        });

        document.addEventListener('keyup', function(e) {
            if (e.code === 'Space') {
                spacePressed = false;
                canvasContainer.style.cursor = 'grab';
                if (isPanning) {
                    isPanning = false;
                    canvasContainer.style.cursor = 'grab';
                }
            }
        });

        canvasContainer.addEventListener('mousedown', function(e) {
            if (e.button === 1 || (e.button === 0 && spacePressed)) {
                e.preventDefault();
                isPanning = true;
                panStartX = e.clientX - canvasContainer.scrollLeft;
                panStartY = e.clientY - canvasContainer.scrollTop;
                canvasContainer.style.cursor = 'grabbing';
            }
        });

        canvasContainer.addEventListener('mousemove', function(e) {
            if (isPanning) {
                e.preventDefault();
                const x = e.clientX - panStartX;
                const y = e.clientY - panStartY;
                canvasContainer.scrollLeft = -x;
                canvasContainer.scrollTop = -y;
            }
        });

        canvasContainer.addEventListener('mouseup', function(e) {
            if (isPanning) {
                isPanning = false;
                canvasContainer.style.cursor = spacePressed ? 'grab' : 'grab';
            }
        });

        canvasContainer.addEventListener('mouseleave', function() {
            if (isPanning) {
                isPanning = false;
                canvasContainer.style.cursor = 'grab';
            }
        });

        // Touch support for mobile
        canvas.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                // Pinch to zoom
                e.preventDefault();
                lastTouchDistance = getTouchDistance(e.touches[0], e.touches[1]);
            } else if (e.touches.length === 1) {
                // Single touch - handle field interaction
                const touch = e.touches[0];
                const pos = getCanvasMousePos(touch);
                
                // First check if touching resize handle of selected field
                if (selectedFieldIndex >= 0) {
                    const field = fields[selectedFieldIndex];
                    if (field._handleBounds) {
                        const hb = field._handleBounds;
                        if (pos.x >= hb.x && pos.x <= hb.x + hb.width &&
                            pos.y >= hb.y && pos.y <= hb.y + hb.height) {
                            // Touching resize handle
                            isResizing = true;
                            resizeStartY = touch.clientY;
                            resizeStartFontSize = field.font_size;
                            e.preventDefault();
                            return;
                        }
                    }
                }
                
                // Check if touching a field
                for (let i = fields.length - 1; i >= 0; i--) {
                    const dims = getFieldDimensions(fields[i]);
                    if (pos.x >= dims.x && pos.x <= dims.x + dims.width &&
                        pos.y >= dims.y && pos.y <= dims.y + dims.height) {
                        
                        selectedFieldIndex = i;
                        isDragging = true;
                        dragOffset.x = pos.x - fields[i].x;
                        dragOffset.y = pos.y - fields[i].y;
                        updateFieldForm();
                        drawCanvas();
                        e.preventDefault();
                        break;
                    }
                }
            }
        }, { passive: false });

        canvas.addEventListener('touchmove', function(e) {
            if (e.touches.length === 2) {
                // Pinch zoom
                e.preventDefault();
                const currentDistance = getTouchDistance(e.touches[0], e.touches[1]);
                if (lastTouchDistance > 0) {
                    const delta = (currentDistance - lastTouchDistance) * 0.01;
                    setZoom(zoomLevel + delta);
                }
                lastTouchDistance = currentDistance;
            } else if (e.touches.length === 1 && selectedFieldIndex >= 0) {
                const touch = e.touches[0];
                
                if (isResizing) {
                    // Resize field
                    e.preventDefault();
                    const deltaY = touch.clientY - resizeStartY;
                    const newSize = Math.max(8, Math.min(200, resizeStartFontSize + Math.round(deltaY / 2)));
                    fields[selectedFieldIndex].font_size = newSize;
                    updateFieldForm();
                    drawCanvas();
                    markAsDirty();
                } else if (isDragging) {
                    // Drag field
                    e.preventDefault();
                    const pos = getCanvasMousePos(touch);
                    
                    fields[selectedFieldIndex].x = pos.x - dragOffset.x;
                    fields[selectedFieldIndex].y = pos.y - dragOffset.y;
                    
                    updateFieldForm();
                    drawCanvas();
                    markAsDirty();
                }
            }
        }, { passive: false });

        canvas.addEventListener('touchend', function(e) {
            if (e.touches.length < 2) {
                lastTouchDistance = 0;
            }
            if (e.touches.length === 0) {
                isDragging = false;
                isResizing = false;
            }
        });
    });

    function markAsDirty() {
        hasUnsavedChanges = true;
    }

    function removeField(index) {
        fields.splice(index, 1);
        selectedFieldIndex = -1;
        renderFieldsList();
        drawCanvas();
        markAsDirty();
    }

    // Helper to get matching sample text from dropdown
    function getSampleText(name, label) {
        var opt = $('#selectField option[value="' + name + '"]');
        if (opt.length) {
            var sample = opt.data('sample');
            console.log('getSampleText for', name, ':', sample);
            return sample || label; // Return label if sample is empty
        }
        console.log('getSampleText for', name, ': option not found, using label');
        return label;
    }

    function renderFieldsList() {
        var html = '';
        fields.forEach((f, i) => {
            var isSelected = (i === selectedFieldIndex);
            var activeClass = isSelected ? 'border-primary bg-light' : '';
            var settingsDisplay = isSelected ? 'block' : 'none';
            
            html += `
                <div class="card mb-2 ${activeClass}" data-field-index="${i}">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong style="cursor: pointer;" onclick="selectField(${i})">${f.label}</strong>
                            <div>
                                <button type="button" class="btn btn-xs btn-outline-info mr-1" onclick="toggleFieldSettings(${i}); event.stopPropagation();" title="Edit Settings">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-danger" onclick="removeField(${i}); event.stopPropagation();" title="Hapus Field">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="field-settings mt-2" id="fieldSettings${i}" style="display: ${settingsDisplay};" onclick="event.stopPropagation()">
                            <div class="row">
                                <div class="col-12 mb-1">
                                    <small>Font:</small>
                                    <select class="form-control form-control-sm" onchange="updateFieldProp(${i}, 'font_family', this.value)">
                                        <option value="Arial" ${f.font_family==='Arial'?'selected':''}>Arial</option>
                                        <option value="Times New Roman" ${f.font_family==='Times New Roman'?'selected':''}>Times New Roman</option>
                                        <option value="Georgia" ${f.font_family==='Georgia'?'selected':''}>Georgia</option>
                                        <option value="Verdana" ${f.font_family==='Verdana'?'selected':''}>Verdana</option>
                                        <option value="Courier New" ${f.font_family==='Courier New'?'selected':''}>Courier New</option>
                                        <option value="Tahoma" ${f.font_family==='Tahoma'?'selected':''}>Tahoma</option>
                                        <option value="Trebuchet MS" ${f.font_family==='Trebuchet MS'?'selected':''}>Trebuchet MS</option>
                                        <option value="Palatino Linotype" ${f.font_family==='Palatino Linotype'?'selected':''}>Palatino</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <small>Align:</small>
                                    <select class="form-control form-control-sm" onchange="updateFieldProp(${i}, 'text_align', this.value)">
                                        <option value="L" ${f.text_align==='L'?'selected':''}>Left</option>
                                        <option value="C" ${f.text_align==='C'?'selected':''}>Center</option>
                                        <option value="R" ${f.text_align==='R'?'selected':''}>Right</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <small>Style:</small>
                                    <select class="form-control form-control-sm" onchange="updateFieldProp(${i}, 'font_style', this.value)">
                                        <option value="N" ${f.font_style==='N'?'selected':''}>Normal</option>
                                        <option value="B" ${f.font_style==='B'?'selected':''}>Bold</option>
                                        <option value="I" ${f.font_style==='I'?'selected':''}>Italic</option>
                                    </select>
                                </div>
                                <div class="col-6 mt-1">
                                    <small>Size (px):</small>
                                    <input type="number" class="form-control form-control-sm" value="${f.font_size}" onchange="updateFieldProp(${i}, 'font_size', this.value)">
                                </div>
                                <div class="col-6 mt-1">
                                    <small>Color:</small>
                                    <input type="color" class="form-control form-control-sm" value="${f.text_color}" onchange="updateFieldProp(${i}, 'text_color', this.value)">
                                </div>
                                <div class="col-6 mt-1">
                                    <small>X:</small>
                                    <input type="number" class="form-control form-control-sm" value="${f.x}" onchange="updateFieldProp(${i}, 'x', this.value)">
                                </div>
                                <div class="col-6 mt-1">
                                    <small>Y:</small>
                                    <input type="number" class="form-control form-control-sm" value="${f.y}" onchange="updateFieldProp(${i}, 'y', this.value)">
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="border${i}" 
                                                   ${f.has_border ? 'checked' : ''} 
                                                   onchange="toggleBorder(${i}, this.checked)">
                                            <label class="custom-control-label" for="border${i}"><small>Border</small></label>
                                        </div>
                                    </div>
                                    <div class="row" id="borderOptions${i}" style="display: ${f.has_border ? 'flex' : 'none'};">
                                        <div class="col-6">
                                            <small>Warna:</small>
                                            <input type="color" class="form-control form-control-sm" 
                                                   value="${f.border_color || '#000000'}" 
                                                   onchange="updateFieldProp(${i}, 'border_color', this.value)">
                                        </div>
                                        <div class="col-6">
                                            <small>Tebal (px):</small>
                                            <input type="number" class="form-control form-control-sm" 
                                                   value="${f.border_width || 1}" min="1" max="10"
                                                   onchange="updateFieldProp(${i}, 'border_width', this.value)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#fieldsList').html(html);
    }

    function toggleFieldSettings(index) {
        var settingsEl = document.getElementById('fieldSettings' + index);
        if (settingsEl) {
            if (settingsEl.style.display === 'none') {
                // Close all others first
                document.querySelectorAll('.field-settings').forEach(el => el.style.display = 'none');
                settingsEl.style.display = 'block';
                selectedFieldIndex = index;
                drawCanvas();
            } else {
                settingsEl.style.display = 'none';
            }
        }
    }

    function selectField(index) {
        selectedFieldIndex = index;
        renderFieldsList();
        drawCanvas();
    }

    window.updateFieldProp = function(index, prop, value) {
        if (prop === 'font_size' || prop === 'x' || prop === 'y' || prop === 'border_width') {
            value = parseInt(value);
        }
        fields[index][prop] = value;
        drawCanvas();
        markAsDirty();
    }

    window.toggleBorder = function(index, checked) {
        fields[index].has_border = checked;
        var optionsEl = document.getElementById('borderOptions' + index);
        if (optionsEl) {
            optionsEl.style.display = checked ? 'flex' : 'none';
        }
        drawCanvas();
        markAsDirty();
    }

    function getFieldDimensions(field) {
        // Mock dimensions based on font
        ctx.font = `${field.font_style === 'B' ? 'bold' : 'normal'} ${field.font_size}px ${field.font_family}`;
        var width = ctx.measureText(field.sample).width;
        var height = field.font_size;
        
        // Adjust X for alignment
        var x = field.x;
        if (field.text_align === 'C') x -= width / 2;
        if (field.text_align === 'R') x -= width;
        
        return { x: x, y: field.y, width: width, height: height };
    }

    function drawCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(templateImage, 0, 0, canvas.width, canvas.height);

        fields.forEach((field, index) => {
            var isActive = (index === selectedFieldIndex);
            
            // Check for image/QR fields
            if (field.name === 'qr_code' || field.name === 'foto_peserta') {
                // Draw box for image/QR
                var size = field.font_size; // Use size as height/width for square
                var x = field.x;
                if (field.text_align === 'C') x -= size / 2;
                if (field.text_align === 'R') x -= size;
                
                ctx.fillStyle = '#eee';
                ctx.fillRect(x, field.y, size, size); // Draw square
                ctx.strokeStyle = '#333';
                ctx.strokeRect(x, field.y, size, size);
                
                ctx.fillStyle = '#000';
                ctx.font = '10px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(field.name === 'qr_code' ? 'QR' : 'FOTO', x + size/2, field.y + size/2);
                
                if (isActive) {
                    ctx.strokeStyle = 'blue';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(x, field.y, size, size);
                    
                    // Handle for resize
                    field._handleBounds = {x: x + size - 5, y: field.y + size - 5, width: 10, height: 10};
                    ctx.fillStyle = 'blue';
                    ctx.fillRect(x + size - 5, field.y + size - 5, 10, 10);
                }
            } else if (field.name === 'block_table') {
                // Special Visual for Table Block
                var boxHeight = Math.max(field.font_size * 8, 150);
                var boxWidth = canvas.width - (field.x * 2);
                if (boxWidth < 300) boxWidth = 300; // Min width

                var x = field.x;
                var y = field.y;
                
                // Draw Table Background
                ctx.fillStyle = '#fff';
                ctx.fillRect(x, y, boxWidth, boxHeight);
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 1;
                ctx.strokeRect(x, y, boxWidth, boxHeight);

                // Draw Header Row
                var headerHeight = field.font_size * 2;
                ctx.beginPath();
                ctx.moveTo(x, y + headerHeight);
                ctx.lineTo(x + boxWidth, y + headerHeight);
                ctx.stroke();

                // Draw Columns (Approximate percentages: 5%, 35%, 15%, 15%, 30%)
                var cols = [0.05, 0.40, 0.55, 0.70]; // x positions
                cols.forEach(p => {
                    var cx = x + (boxWidth * p);
                    ctx.beginPath();
                    ctx.moveTo(cx, y);
                    ctx.lineTo(cx, y + boxHeight);
                    ctx.stroke();
                });

                // Draw Dummy Rows
                var rowHeight = field.font_size * 1.5;
                var currentY = y + headerHeight + rowHeight;
                
                // Draw a few dummy rows 
                for (var r = y + headerHeight + rowHeight; r < y + boxHeight - (rowHeight * 2); r += rowHeight) {
                    ctx.beginPath();
                    ctx.moveTo(x, r);
                    ctx.lineTo(x + boxWidth, r);
                    ctx.strokeStyle = '#ccc';
                    ctx.stroke();
                    currentY = r;
                }

                // Draw Footer Border (Top of Total Section)
                ctx.beginPath();
                ctx.moveTo(x, y + boxHeight - (rowHeight * 2));
                ctx.lineTo(x + boxWidth, y + boxHeight - (rowHeight * 2));
                ctx.strokeStyle = '#000';
                ctx.stroke();

                // Draw Footer Row Separator (Between Total and Average)
                ctx.beginPath();
                ctx.moveTo(x, y + boxHeight - rowHeight);
                ctx.lineTo(x + boxWidth, y + boxHeight - rowHeight);
                ctx.strokeStyle = '#ccc';
                ctx.stroke();

                // Draw Footer Text (Approximate)
                ctx.fillStyle = '#000';
                ctx.textAlign = 'right';
                ctx.font = 'bold ' + (field.font_size * 0.7) + 'px Arial';
                
                var col2End = x + (boxWidth * 0.40);
                var col3Center = x + (boxWidth * 0.475);

                // Row Jumlah
                ctx.fillText("Jumlah", col2End - 5, y + boxHeight - (rowHeight * 1.5));
                
                // Row Rata-Rata
                ctx.fillText("Rata-Rata", col2End - 5, y + boxHeight - (rowHeight * 0.5));

                // Draw Header Text
                ctx.fillStyle = '#000';
                ctx.font = 'bold ' + (field.font_size * 0.8) + 'px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                
                var headers = ['No', 'Materi Ujian', 'Nilai Angka', 'Nilai Huruf', 'Terbilang'];
                var centerPos = [
                    x + (boxWidth * 0.025), 
                    x + (boxWidth * 0.225),
                    x + (boxWidth * 0.475), 
                    x + (boxWidth * 0.625),
                    x + (boxWidth * 0.85)
                ];
                
                headers.forEach((h, idx) => {
                    ctx.fillText(h, centerPos[idx], y + (headerHeight/2));
                });

                // Selection Box
                if (isActive) {
                    ctx.strokeStyle = 'blue';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(x, y, boxWidth, boxHeight);
                    
                    field._handleBounds = {x: x + boxWidth - 10, y: y + boxHeight - 10, width: 10, height: 10};
                    ctx.fillStyle = 'blue';
                    ctx.fillRect(x + boxWidth - 10, y + boxHeight - 10, 10, 10);
                }

            } else if (field.name.startsWith('block_materi_')) {
                // Special Visual for Materi Table Block
                // We use font_size to determine scale, similar to block_table but maybe tailored
                var boxHeight = Math.max(field.font_size * 6, 100);
                var boxWidth = (field.max_width > 0) ? field.max_width : (canvas.width - (field.x * 2));
                if (boxWidth < 200) boxWidth = 200; // Min width

                var x = field.x;
                var y = field.y;
                
                // Draw Table Background
                ctx.fillStyle = '#f9f9f9';
                ctx.fillRect(x, y, boxWidth, boxHeight);
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 1;
                ctx.strokeRect(x, y, boxWidth, boxHeight);

                 // Header Row (Materi Name)
                var headerHeight = field.font_size * 1.5;
                ctx.fillStyle = '#e0e0e0';
                ctx.fillRect(x, y, boxWidth, headerHeight);
                ctx.strokeRect(x, y, boxWidth, headerHeight);
                
                // Header Text
                ctx.fillStyle = '#000';
                ctx.font = 'bold ' + (field.font_size) + 'px Arial';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                ctx.fillText(field.label, x + 10, y + (headerHeight/2));

                // Column Headers
                var colHeaderY = y + headerHeight;
                ctx.beginPath();
                ctx.moveTo(x, colHeaderY + headerHeight);
                ctx.lineTo(x + boxWidth, colHeaderY + headerHeight);
                ctx.stroke();

                // Columns
                var cols = [0.05, 0.40, 0.55, 0.70]; 
                cols.forEach(p => {
                    var cx = x + (boxWidth * p);
                    ctx.beginPath();
                    ctx.moveTo(cx, colHeaderY);
                    ctx.lineTo(cx, y + boxHeight);
                    ctx.stroke();
                });

                // Dummy Rows
                var rowHeight = field.font_size * 1.2;
                for (var r = colHeaderY + headerHeight + rowHeight; r < y + boxHeight - rowHeight; r += rowHeight) {
                     ctx.beginPath();
                     ctx.moveTo(x, r);
                     ctx.lineTo(x + boxWidth, r);
                     ctx.strokeStyle = '#eee';
                     ctx.stroke();
                }

                // Selection Box
                if (isActive) {
                    ctx.strokeStyle = 'blue';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(x, y, boxWidth, boxHeight);
                    
                    // Handle for resize
                    field._handleBounds = {x: x + boxWidth - 10, y: y + boxHeight - 10, width: 10, height: 10};
                    ctx.fillStyle = 'blue';
                    ctx.fillRect(x + boxWidth - 10, y + boxHeight - 10, 10, 10);
                }

                // Text Field
                ctx.font = `${field.font_style === 'B' ? 'bold' : 'normal'} ${field.font_size}px ${field.font_family}`;
                ctx.textAlign = field.text_align === 'C' ? 'center' : (field.text_align === 'R' ? 'right' : 'left');
                ctx.textBaseline = 'top';

                // Draw text with stroke outline if enabled
                if (field.has_border) {
                    // Draw outline first (stroke)
                    ctx.strokeStyle = field.border_color || '#000000';
                    ctx.lineWidth = (field.border_width || 1) * 2;
                    ctx.lineJoin = 'round';
                    ctx.miterLimit = 2;
                    ctx.strokeText(field.sample, field.x, field.y);
                    
                    // Then draw fill on top
                    ctx.fillStyle = field.text_color;
                    ctx.fillText(field.sample, field.x, field.y);
                } else {
                    // Normal text without outline
                    ctx.fillStyle = field.text_color;
                    ctx.fillText(field.sample, field.x, field.y);
                }

                if (isActive) {
                    var dims = getFieldDimensions(field);
                    ctx.strokeStyle = 'blue';
                    ctx.lineWidth = 1;
                    ctx.strokeRect(dims.x - 2, dims.y - 2, dims.width + 4, dims.height + 4);
                    
                    // Handle
                    field._handleBounds = {x: dims.x + dims.width - 5, y: dims.y + dims.height - 5, width: 10, height: 10};
                    ctx.fillStyle = 'blue';
                    ctx.fillRect(dims.x + dims.width - 5, dims.y + dims.height - 5, 10, 10);
                }
            } else if (field.name.startsWith('block_group_')) {
                 // Special Visual for Group Table Block
                var boxHeight = Math.max(field.font_size * 6, 100);
                var boxWidth = (field.max_width > 0) ? field.max_width : (canvas.width - (field.x * 2));
                if (boxWidth < 200) boxWidth = 200; // Min width

                var x = field.x;
                var y = field.y;
                
                // Draw Table Background (Light Cyan for Groups)
                ctx.fillStyle = '#e0f7fa';
                ctx.fillRect(x, y, boxWidth, boxHeight);
                ctx.strokeStyle = '#006064';
                ctx.lineWidth = 1;
                ctx.strokeRect(x, y, boxWidth, boxHeight);

                 // Header Row (Group Name)
                var headerHeight = field.font_size * 1.5;
                ctx.fillStyle = '#b2ebf2';
                ctx.fillRect(x, y, boxWidth, headerHeight);
                ctx.strokeRect(x, y, boxWidth, headerHeight);
                
                // Header Text
                ctx.fillStyle = '#006064';
                ctx.font = 'bold ' + (field.font_size) + 'px Arial';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                ctx.fillText(field.label, x + 10, y + (headerHeight/2));

                // Column Headers
                var colHeaderY = y + headerHeight;
                ctx.beginPath();
                ctx.moveTo(x, colHeaderY + headerHeight);
                ctx.lineTo(x + boxWidth, colHeaderY + headerHeight);
                ctx.stroke();

                // Columns
                var cols = [0.05, 0.40, 0.55, 0.70]; 
                cols.forEach(p => {
                    var cx = x + (boxWidth * p);
                    ctx.beginPath();
                    ctx.moveTo(cx, colHeaderY);
                    ctx.lineTo(cx, y + boxHeight);
                    ctx.stroke();
                });

                // Dummy Rows
                var rowHeight = field.font_size * 1.2;
                for (var r = colHeaderY + headerHeight + rowHeight; r < y + boxHeight - rowHeight; r += rowHeight) {
                     ctx.beginPath();
                     ctx.moveTo(x, r);
                     ctx.lineTo(x + boxWidth, r);
                     ctx.strokeStyle = '#80deea';
                     ctx.stroke();
                }

                // Selection Box
                if (isActive) {
                    ctx.strokeStyle = 'blue';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(x, y, boxWidth, boxHeight);
                    
                    // Handle for resize
                    field._handleBounds = {x: x + boxWidth - 10, y: y + boxHeight - 10, width: 10, height: 10};
                    ctx.fillStyle = 'blue';
                    ctx.fillRect(x + boxWidth - 10, y + boxHeight - 10, 10, 10);
                }

                // Text Field Sample
                ctx.font = `${field.font_style === 'B' ? 'bold' : 'normal'} ${field.font_size}px ${field.font_family}`;
                ctx.textAlign = field.text_align === 'C' ? 'center' : (field.text_align === 'R' ? 'right' : 'left');
                ctx.textBaseline = 'top';

                if (field.has_border) {
                     ctx.strokeStyle = field.border_color || '#000000';
                     ctx.lineWidth = (field.border_width || 1) * 2;
                     ctx.strokeText(field.sample, field.x, field.y);
                     ctx.fillStyle = field.text_color;
                     ctx.fillText(field.sample, field.x, field.y);
                } else {
                     ctx.fillStyle = field.text_color;
                     ctx.fillText(field.sample, field.x, field.y);
                }
                 
                 if (isActive) {
                    var dims = getFieldDimensions(field);
                    ctx.strokeStyle = 'blue';
                    ctx.lineWidth = 1;
                    ctx.strokeRect(dims.x - 2, dims.y - 2, dims.width + 4, dims.height + 4);
                    // Handle
                    field._handleBounds = {x: dims.x + dims.width - 5, y: dims.y + dims.height - 5, width: 10, height: 10};
                    ctx.fillStyle = 'blue';
                    ctx.fillRect(dims.x + dims.width - 5, dims.y + dims.height - 5, 10, 10);
                }
                 
            } else {
                 // GENERIC TEXT FIELDS
                ctx.font = `${field.font_style === 'B' ? 'bold' : 'normal'} ${field.font_size}px ${field.font_family}`;
                ctx.textAlign = field.text_align === 'C' ? 'center' : (field.text_align === 'R' ? 'right' : 'left');
                ctx.textBaseline = 'top';

                // Draw text with stroke outline if enabled
                if (field.has_border) {
                    ctx.strokeStyle = field.border_color || '#000000';
                    ctx.lineWidth = (field.border_width || 1) * 2;
                    ctx.lineJoin = 'round';
                    ctx.miterLimit = 2;
                    ctx.strokeText(field.sample, field.x, field.y);
                    
                    ctx.fillStyle = field.text_color;
                    ctx.fillText(field.sample, field.x, field.y);
                } else {
                    ctx.fillStyle = field.text_color;
                    ctx.fillText(field.sample, field.x, field.y);
                }

                if (isActive) {
                    var dims = getFieldDimensions(field);
                    ctx.strokeStyle = 'blue';
                    ctx.lineWidth = 1;
                    ctx.strokeRect(dims.x - 2, dims.y - 2, dims.width + 4, dims.height + 4);
                    
                    // Handle
                    field._handleBounds = {x: dims.x + dims.width - 5, y: dims.y + dims.height - 5, width: 10, height: 10};
                    ctx.fillStyle = 'blue';
                    ctx.fillRect(dims.x + dims.width - 5, dims.y + dims.height - 5, 10, 10);
                }
            }
        });
    }

    // ========== ZOOM AND PAN FUNCTIONS ==========
    
    function setZoom(level) {
        zoomLevel = Math.max(0.25, Math.min(3.0, level));
        applyTransform();
        updateZoomIndicator();
    }

    function applyTransform() {
        const wrapper = document.getElementById('canvasWrapper');
        wrapper.style.transform = `scale(${zoomLevel})`;
    }

    function updateZoomIndicator() {
        document.getElementById('zoomIndicator').textContent = Math.round(zoomLevel * 100) + '%';
    }

    function fitCanvasToViewport() {
        const container = document.getElementById('canvasContainer');
        const wrapper = document.getElementById('canvasWrapper');
        
        // Get container dimensions (available space)
        const containerWidth = container.clientWidth;
        const containerHeight = container.clientHeight;
        
        // Get canvas actual dimensions
        const canvasWidth = canvas.width;
        const canvasHeight = canvas.height;
        
        // Calculate zoom to fit (with minimal padding)
        const padding = 20; // 10px padding on each side
        const zoomX = (containerWidth - padding) / canvasWidth;
        const zoomY = (containerHeight - padding) / canvasHeight;
        
        // Use the smaller zoom to ensure it fits in both dimensions
        const fitZoom = Math.min(zoomX, zoomY, 1.0); // Max 1.0 (100%)
        
        // Apply the zoom
        setZoom(fitZoom);
    }

    function centerCanvas() {
        const container = document.getElementById('canvasContainer');
        const wrapper = document.getElementById('canvasWrapper');
        
        // Get the dimensions
        const containerWidth = container.clientWidth;
        const wrapperWidth = wrapper.offsetWidth;
        
        // Calculate scroll position to center horizontally only
        const scrollLeft = Math.max(0, (wrapperWidth - containerWidth) / 2);
        
        // Set scroll position - horizontal center, vertical top
        container.scrollLeft = scrollLeft;
        container.scrollTop = 0;
    }

    function getCanvasMousePos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        return {
            x: (e.clientX - rect.left) * scaleX / zoomLevel,
            y: (e.clientY - rect.top) * scaleY / zoomLevel
        };
    }

    function getTouchDistance(touch1, touch2) {
        const dx = touch1.clientX - touch2.clientX;
        const dy = touch1.clientY - touch2.clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // Zoom button handlers


</script>
<?= $this->endSection(); ?>
