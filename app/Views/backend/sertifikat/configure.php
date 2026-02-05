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
                            <button type="button" class="btn btn-tool" id="btnTogglePreview">
                                <i class="fas fa-sync"></i> Refresh Canvas
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="background: #f4f4f4; overflow: auto; text-align: center;">
                        <div style="position: relative; display: inline-block;">
                            <canvas id="templateCanvas" 
                                    width="<?= $template['width'] ?>" 
                                    height="<?= $template['height'] ?>"
                                    style="border: 1px solid #ddd; cursor: crosshair; max-width: 100%; height: auto;">
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

    $(document).ready(function() {
        canvas = document.getElementById('templateCanvas');
        ctx = canvas.getContext('2d');
        
        // Load template image
        templateImage.src = $('#templatePath').val();
        templateImage.onload = function() {
            drawCanvas();
        };

        // Load existing fields from DB
        <?php if (!empty($fields)): ?>
        var dbFields = <?= json_encode($fields) ?>;
        fields = dbFields.map(function(f) {
            return {
                name: f.field_name,
                label: f.field_label,
                sample: getSampleText(f.field_name, f.field_label), 
                x: parseFloat(f.pos_x),
                y: parseFloat(f.pos_y),
                font_family: f.font_family,
                font_size: parseInt(f.font_size),
                font_style: f.font_style,
                text_align: f.text_align,
                text_color: f.text_color,
                max_width: parseInt(f.max_width)
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
                max_width: 0
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

            $.ajax({
                url: '<?= base_url('backend/sertifikat/save-config') ?>',
                type: 'POST',
                data: {
                    template_id: $('#templateId').val(),
                    fields: fields
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil', response.message, 'success');
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
        if (opt.length) return opt.data('sample');
        return label;
    }

    function renderFieldsList() {
        var html = '';
        fields.forEach((f, i) => {
            var activeClass = (i === selectedFieldIndex) ? 'border-primary bg-light' : '';
            html += `
                <div class="card mb-2 ${activeClass}" onclick="selectField(${i})">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between">
                            <strong>${f.label}</strong>
                            <button type="button" class="btn btn-xs btn-danger" onclick="removeField(${i}); event.stopPropagation();"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="row mt-2" onclick="event.stopPropagation()">
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
                        </div>
                    </div>
                </div>
            `;
        });
        $('#fieldsList').html(html);
    }

    function selectField(index) {
        selectedFieldIndex = index;
        renderFieldsList();
        drawCanvas();
    }

    window.updateFieldProp = function(index, prop, value) {
        if (prop === 'font_size' || prop === 'x' || prop === 'y') {
            value = parseInt(value);
        }
        fields[index][prop] = value;
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

            } else if (field.name.startsWith('block_')) {
                // Other Blocks (Header, Footer)

            } else {
                // Text Field
                ctx.font = `${field.font_style === 'B' ? 'bold' : 'normal'} ${field.font_size}px ${field.font_family}`;
                ctx.fillStyle = field.text_color;
                ctx.textAlign = field.text_align === 'C' ? 'center' : (field.text_align === 'R' ? 'right' : 'left');
                ctx.textBaseline = 'top';
                ctx.fillText(field.sample, field.x, field.y);

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

</script>
<?= $this->endSection(); ?>
