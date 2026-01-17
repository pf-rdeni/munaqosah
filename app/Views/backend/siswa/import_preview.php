<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-eye mr-1"></i> Preview Data Import: <?= strtoupper($importType) ?></h3>
    </div>
    
    <form action="<?= base_url('backend/siswa/saveImport') ?>" method="post" id="form-import-save">
        <input type="hidden" name="filename" value="<?= $tempFileName ?>">
        <input type="hidden" name="import_type" value="<?= $importType ?>">

        <div class="card-body p-0">
            <div class="p-3">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Silakan ceklis data siswa yang ingin disimpan.
                    Baris berwarna merah menandakan data duplikat atau tidak valid (tidak bisa dipilih).
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="table-preview">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="50">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" id="check-all">
                                    <label class="custom-control-label" for="check-all"></label>
                                </div>
                            </th>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>JK</th>
                            <th>Tgl Lahir</th>
                            <th>Tgl Lahir</th>
                            <th>Tempat Lahir</th>
                            <th>Nama Ayah</th>
                            <th>Nama Ibu</th>
                            <th>No HP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sheetData)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($sheetData as $row): ?>
                                <tr class="<?= $row['valid'] ? '' : 'table-danger' ?>">
                                    <td class="text-center">
                                        <?php if ($row['valid']): ?>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input check-item" type="checkbox" 
                                                       name="selected_rows[]" 
                                                       id="check_<?= $row['row_index'] ?>" 
                                                       value="<?= $row['row_index'] ?>">
                                                <label class="custom-control-label" for="check_<?= $row['row_index'] ?>"></label>
                                            </div>
                                        <?php else: ?>
                                            <i class="fas fa-times text-danger"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $no++ ?></td>
                                    <td><?= $row['nisn'] ?></td>
                                    <td><?= $row['nama_siswa'] ?></td>
                                    <td><?= $row['jenis_kelamin'] ?></td>
                                    <td><?= $row['tanggal_lahir'] ?></td>
                                    <td><?= $row['tanggal_lahir'] ?></td>
                                    <td><?= $row['tempat_lahir'] ?></td>
                                    <td><?= $row['nama_ayah'] ?></td>
                                    <td><?= $row['nama_ibu'] ?></td>
                                    <td><?= $row['no_hp'] ?></td>
                                    <td>
                                        <?php if ($row['valid']): ?>
                                            <span class="badge badge-success">Valid</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><?= $row['error'] ?? 'Invalid' ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <a href="<?= base_url('backend/siswa') ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary float-right" id="btn-save">
                <i class="fas fa-save mr-1"></i> Simpan Terpilih
            </button>
        </div>
    </form>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Inisialisasi DataTable (Tanpa Pagination agar form submit membawa semua data yg diceklis)
    var table = $('#table-preview').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": true,
        "ordering": false,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "scrollY": "500px",
        "scrollCollapse": true,
    });

    // Check All functionality
    $('#check-all').on('click', function() {
        // Hanya pilih yang visible dan valid (in case ada filtering)
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"].check-item', rows).prop('checked', this.checked);
    });

    // Update 'Check All' state when individual checkboxes change
    $('#table-preview tbody').on('change', 'input[type="checkbox"].check-item', function(){
        if(!this.checked){
            var el = $('#check-all').get(0);
            if(el && el.checked && ('indeterminate' in el)){
                el.indeterminate = true;
            }
        }
    });
});
</script>
<?= $this->endSection(); ?>
