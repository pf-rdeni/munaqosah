<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trash-alt mr-1"></i>
                    Reset Data Nilai Ujian
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>PERHATIAN!</strong> Fitur ini akan menghapus <u>SEMUA DATA NILAI</u> pada tahun ajaran yang dipilih secara permanen. Data yang sudah dihapus tidak dapat dikembalikan.
                </div>

                <form id="form-reset">
                    <div class="form-group">
                        <label>Pilih Tahun Ajaran</label>
                        <select name="tahun_ajaran" id="select-tahun" class="form-control" required>
                            <option value="">-- Pilih Tahun --</option>
                            <?php foreach($years as $y): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hanya tahun ajaran yang memiliki data nilai yang muncul di sini.</small>
                    </div>

                    <div id="preview-area" class="d-none mt-4">
                        <h5>Ringkasan Data yang Akan Dihapus:</h5>
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Materi Ujian</th>
                                    <th class="text-center">Jumlah Nilai (Record)</th>
                                </tr>
                            </thead>
                            <tbody id="preview-body"></tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td>TOTAL RECORD</td>
                                    <td class="text-center" id="preview-total">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary btn-lg" id="btn-check">
                            <i class="fas fa-search mr-2"></i> Cek Data
                        </button>
                        
                        <button type="submit" class="btn btn-danger btn-lg d-none" id="btn-execute">
                            <i class="fas fa-trash mr-2"></i> HAPUS PERMANEN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $('#btn-check').click(function() {
        let ta = $('#select-tahun').val();
        if(!ta) {
            Swal.fire('Error', 'Pilih Tahun Ajaran dulu.', 'error');
            return;
        }

        // Reset UI
        $('#preview-area').addClass('d-none');
        $('#btn-execute').addClass('d-none');
        $('#btn-check').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memeriksa...');

        $.ajax({
            url: '<?= base_url('backend/setting/reset-nilai/preview') ?>',
            type: 'POST',
            data: { tahun_ajaran: ta },
            success: function(res) {
                $('#btn-check').prop('disabled', false).html('<i class="fas fa-search mr-2"></i> Cek Data');
                
                if(res.success) {
                    if(res.total === 0) {
                        Swal.fire('Info', 'Tidak ada data nilai pada tahun ajaran ini.', 'info');
                        return;
                    }

                    // Populate UI
                    let html = '';
                    res.stats.forEach(s => {
                        html += `<tr><td>${s.nama_materi || 'Materi Belum Terdefinisi'}</td><td class="text-center">${s.jumlah}</td></tr>`;
                    });
                    $('#preview-body').html(html);
                    $('#preview-total').text(res.total);
                    
                    $('#preview-area').removeClass('d-none');
                    $('#btn-execute').removeClass('d-none');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                $('#btn-check').prop('disabled', false).html('<i class="fas fa-search mr-2"></i> Cek Data');
                Swal.fire('Error', 'Server Error', 'error');
            }
        });
    });

    $('#form-reset').submit(function(e) {
        e.preventDefault();
        let ta = $('#select-tahun').val();
        let total = $('#preview-total').text();

        Swal.fire({
            title: 'YAKIN HAPUS?',
            text: `Anda akan menghapus ${total} data nilai untuk tahun ajaran ${ta}. Tindakan ini TIDAK BISA DIBATALKAN!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('backend/setting/reset-nilai/execute') ?>',
                    type: 'POST',
                    data: { tahun_ajaran: ta },
                    success: function(res) {
                        if(res.success) {
                            Swal.fire('Terhapus!', res.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // Hide execute button if select changed
    $('#select-tahun').change(function() {
        $('#preview-area').addClass('d-none');
        $('#btn-execute').addClass('d-none');
    });
</script>
<?= $this->endSection(); ?>
