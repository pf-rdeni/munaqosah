<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-2"></i>
                    Data Juri
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/juri/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Juri
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Alert Success Special for Juri Credential -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <table id="tabelJuri" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Juri</th>
                            <th>Grup Juri</th>
                            <th>Materi Ujian</th>
                            <th>Kriteria</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($juriList as $juri): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <strong><?= esc($juri['nama_juri']) ?></strong><br>
                                    <code class="text-muted"><?= esc($juri['username']) ?></code>
                                </td>
                                <td>
                                    <?php 
                                        $grupId = $juri['id_grup_juri'] ?? 0;
                                        $badges = [
                                            0 => 'badge-secondary',
                                            1 => 'badge-primary',
                                            2 => 'badge-success',
                                            3 => 'badge-danger',
                                            4 => 'badge-warning',
                                            5 => 'badge-info',
                                            6 => 'badge-indigo',
                                            7 => 'badge-lightblue',
                                            8 => 'badge-navy',
                                            9 => 'badge-purple',
                                            10 => 'badge-pink'
                                        ];
                                        $badgeClass = $badges[$grupId] ?? 'badge-secondary';
                                        $grupName = ($grupId > 0) ? "Grup $grupId" : "-";
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="font-size: 1em;"><?= $grupName ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= esc($juri['nama_grup_materi'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($juri['kriteria_custom']) && !empty($juri['kriteria_names'])): ?>
                                        <?php foreach ($juri['kriteria_names'] as $kName): ?>
                                            <span class="badge badge-warning mb-1"><?= esc($kName) ?></span>
                                        <?php endforeach; ?>
                                        <small class="text-muted d-block">(<?= $juri['kriteria_count'] ?>/<?= $juri['kriteria_total'] ?>)</small>
                                    <?php else: ?>
                                        <span class="badge badge-success">Semua Kriteria</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('backend/juri/edit/' . $juri['id']) ?>" class="btn btn-warning btn-xs" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-info btn-xs btn-set-kriteria" 
                                            data-id="<?= $juri['id'] ?>"
                                            data-nama="<?= esc($juri['nama_juri']) ?>"
                                            title="Setting Kriteria">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-xs btn-set-grup"
                                            data-id="<?= $juri['id'] ?>"
                                            data-nama="<?= esc($juri['nama_juri']) ?>"
                                            data-grup="<?= $juri['id_grup_juri'] ?? 0 ?>"
                                            title="Setting Grup Juri">
                                        <i class="fas fa-users"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-xs" onclick="confirmReset('<?= base_url('backend/juri/reset-password/' . $juri['id']) ?>')" title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-xs" onclick="confirmDelete('<?= base_url('backend/juri/delete/' . $juri['id']) ?>')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Setting Kriteria Juri -->
<div class="modal fade" id="modalSetKriteria" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-cog"></i> Setting Kriteria Juri</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formSetKriteria">
                <div class="modal-body">
                    <input type="hidden" name="juri_id" id="kriteriaJuriId">
                    
                    <div class="alert alert-info">
                        <strong>Juri:</strong> <span id="kriteriaJuriNama"></span>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="useDefaultKriteria" name="use_default" value="1">
                            <label class="custom-control-label" for="useDefaultKriteria">
                                <strong>Semua Kriteria (Default)</strong>
                            </label>
                            <small class="form-text text-muted">Juri akan menilai semua kriteria cabang lomba</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div id="kriteriaListContainer">
                        <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat kriteria...</p>
                    </div>
                    
                    <div id="kriteriaInfo" class="mt-3" style="display: none;">
                        <span class="badge badge-info">Dipilih: <span id="selectedCount">0</span> / <span id="totalCount">0</span></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Setting Grup Juri -->
<div class="modal fade" id="modalSetGrupJuri" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-users"></i> Set Grup Juri</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formSetGrupJuri">
                <div class="modal-body">
                    <input type="hidden" name="juri_id" id="grupJuriId">
                    
                    <div class="alert alert-info py-2 px-3 mb-3">
                        <small>Juri:</small><br>
                        <strong id="grupJuriNama"></strong>
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Grup Juri (1-10)</label>
                        <select class="form-control" name="id_grup_juri" id="idGrupJuriSelect">
                            <option value="0">- Pilih Grup -</option>
                            <?php for($i=1; $i<=10; $i++): ?>
                                <option value="<?= $i ?>">Grup <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <small class="text-muted">Grup ini digunakan untuk logika giliran/statistik.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function() {
        $('#tabelJuri').DataTable({
            responsive: true,
            autoWidth: false,
        });

        // ==================== GRUP JURI LOGIC ====================
        $(document).on('click', '.btn-set-grup', function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            var grup = $(this).data('grup'); // 0 if null

            $('#grupJuriId').val(id);
            $('#grupJuriNama').text(nama);
            $('#idGrupJuriSelect').val(grup);
            
            $('#modalSetGrupJuri').modal('show');
        });

        $('#formSetGrupJuri').submit(function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: '<?= base_url('backend/juri/updateGrupJuri') ?>',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if(res.success) {
                        $('#modalSetGrupJuri').modal('hide');
                        Swal.fire({
                            icon: 'success', 
                            title: 'Sukses', 
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                }
            });
        });
    });

    // Confirm Delete
    function confirmDelete(url) {
        Swal.fire({
            title: 'Yakin hapus juri ini?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    // Confirm Reset Password
    function confirmReset(url) {
        Swal.fire({
            title: 'Reset Password?',
            text: 'Password akan direset ke default: JuriMunaqosah123',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    // ==================== KRITERIA SETTINGS ====================
    
    // Open Kriteria Modal
    $(document).on('click', '.btn-set-kriteria', function() {
        var juriId = $(this).data('id');
        var juriNama = $(this).data('nama');
        
        $('#kriteriaJuriId').val(juriId);
        $('#kriteriaJuriNama').text(juriNama);
        $('#kriteriaListContainer').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat kriteria...</p>');
        $('#kriteriaInfo').hide();
        $('#useDefaultKriteria').prop('checked', false);
        
        // Load kriteria
        $.ajax({
            url: '<?= base_url('backend/juri/getJuriKriteria') ?>/' + juriId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderKriteriaList(response.data);
                } else {
                    $('#kriteriaListContainer').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#kriteriaListContainer').html('<div class="alert alert-danger">Gagal memuat data</div>');
            }
        });
        
        $('#modalSetKriteria').modal('show');
    });
    
    function renderKriteriaList(data) {
        if (data.kriteria.length === 0) {
            $('#kriteriaListContainer').html('<div class="alert alert-warning">Belum ada kriteria untuk materi ini. Silakan tambah kriteria terlebih dahulu.</div>');
            return;
        }
        
        var html = '<div class="list-group">';
        var hasCustom = data.has_custom_setting;
        
        data.kriteria.forEach(function(k) {
            var checked = hasCustom ? (k.assigned ? 'checked' : '') : '';
            var disabled = k.used_by_others && !k.assigned ? 'disabled' : '';
            var labelClass = k.used_by_others && !k.assigned ? 'list-group-item bg-light text-muted' : 'list-group-item';
            
            html += '<label class="' + labelClass + '">';
            html += '<input type="checkbox" class="kriteria-check" name="kriteria_ids[]" value="' + k.id + '" ' + checked + ' ' + disabled + '> ';
            html += '<strong>' + k.nama_kriteria + '</strong> ';
            html += '<span class="badge badge-secondary">' + k.bobot + '%</span>';
            
            // Tampilkan info juri yang menggunakan kriteria ini
            if (k.used_by_others && !k.assigned) {
                html += ' <span class="badge badge-warning ml-2"><i class="fas fa-user"></i> ' + k.used_by + '</span>';
            }
            
            html += '</label>';
        });
        html += '</div>';
        
        $('#kriteriaListContainer').html(html);
        $('#totalCount').text(data.total_count);
        $('#kriteriaInfo').show();
        
        // Set default checkbox jika tidak ada custom setting
        if (!hasCustom) {
            $('#useDefaultKriteria').prop('checked', true);
            $('.kriteria-check:not(:disabled)').prop('disabled', true);
        }
        
        updateSelectedCount();
    }
    
    function updateSelectedCount() {
        var count = $('.kriteria-check:checked').length;
        $('#selectedCount').text(count);
    }
    
    // Toggle kriteria checkboxes when default is checked
    $('#useDefaultKriteria').change(function() {
        if ($(this).is(':checked')) {
            $('.kriteria-check').prop('disabled', true).prop('checked', false);
        } else {
            $('.kriteria-check').prop('disabled', false);
        }
        updateSelectedCount();
    });
    
    // Update count on checkbox change
    $(document).on('change', '.kriteria-check', function() {
        updateSelectedCount();
        // Uncheck default if any kriteria is manually checked
        if ($('.kriteria-check:checked').length > 0) {
            $('#useDefaultKriteria').prop('checked', false);
        }
    });
    
    // Submit Kriteria Form
    $('#formSetKriteria').submit(function(e) {
        e.preventDefault();
        
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: '<?= base_url('backend/juri/saveJuriKriteria') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modalSetKriteria').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            }
        });
    });
</script>
<?= $this->endSection(); ?>
