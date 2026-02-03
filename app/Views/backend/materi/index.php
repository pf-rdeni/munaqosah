<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book mr-2"></i>
                    Data Materi Ujian
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Materi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="tabelMateri" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>ID Code</th>
                            <th>Nama Materi</th>
                            <th>Grup Materi</th>
                            <th>Kriteria Penilaian</th>
                            <th>Bobot</th>
                            <th>Nilai Max</th>
                            <th>Deskripsi</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($materiList as $materi): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><code><?= esc($materi['id_materi']) ?></code></td>
                                <td><?= esc($materi['nama_materi']) ?></td>
                                <td>
                                    <span class="badge badge-info"><?= esc($materi['nama_grup_materi'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <small class="d-block text-left" style="line-height: 1.4;">
                                        <?= nl2br(esc($materi['list_kriteria_nama'] ?? '-')) ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="d-block text-left" style="line-height: 1.4;">
                                        <?= nl2br(esc($materi['list_kriteria_bobot'] ?? '-')) ?>
                                    </small>
                                </td>
                                <td class="text-center"><?= esc($materi['nilai_maksimal']) ?></td>
                                <td><?= esc($materi['deskripsi']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?= base_url('backend/materi/kriteria/' . $materi['id']) ?>" class="btn btn-info btn-sm" title="Kelola Kriteria">
                                            <i class="fas fa-list-ul"></i> Kriteria
                                        </a>
                                        <button type="button" class="btn btn-warning btn-sm btn-edit"
                                            data-toggle="modal" data-target="#modalEdit"
                                            data-id="<?= $materi['id'] ?>"
                                            data-id-materi="<?= esc($materi['id_materi']) ?>"
                                            data-nama="<?= esc($materi['nama_materi']) ?>"
                                            data-id-grup="<?= $materi['id_grup_materi'] ?>"
                                            data-deskripsi="<?= esc($materi['deskripsi']) ?>"
                                            data-nilai="<?= $materi['nilai_maksimal'] ?>"
                                            title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('<?= base_url('backend/materi/delete/' . $materi['id']) ?>')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Materi Ujian</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('backend/materi/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Grup Materi</label>
                        <select name="id_grup_materi" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Grup --</option>
                            <?php foreach ($grupMateri as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['nama_grup_materi']) ?> (<?= esc($g['id_grup_materi']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Materi</label>
                        <input type="text" id="inputNama" name="nama_materi" class="form-control" placeholder="Contoh: Hafalan Juz 30" required>
                    </div>
                    <div class="form-group">
                        <label>ID Materi (Preview)</label>
                        <input type="text" id="inputIdMateri" class="form-control" placeholder="Otomatis" readonly>
                        <small class="text-muted">Kode otomatis digenerate sistem (e.g., MJU01).</small>
                    </div>
                    <div class="form-group">
                        <label>Nilai Maksimal</label>
                        <input type="number" name="nilai_maksimal" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Materi Ujian</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEdit" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Grup Materi</label>
                        <select id="editGrup" name="id_grup_materi" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Grup --</option>
                            <?php foreach ($grupMateri as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['nama_grup_materi']) ?> (<?= esc($g['id_grup_materi']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Materi</label>
                        <input type="text" id="editNama" name="nama_materi" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>ID Materi (Read Only)</label>
                        <input type="text" id="editIdMateri" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nilai Maksimal</label>
                        <input type="number" id="editNilai" name="nilai_maksimal" class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea id="editDeskripsi" name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>



<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function() {
        $('#tabelMateri').DataTable({
            responsive: true,
            autoWidth: false,
        });

        // Initialize Select2 Elements
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Auto Generate ID Materi on Input Name (Add Modal)
        $('#inputNama').on('input', function() {
            let text = $(this).val();
            let words = text.split(' ').filter(word => word.length > 0);
            let initials = '';

            if (words.length >= 2) {
                if(words[0].length > 0 && words[1].length > 0) {
                     initials = words[0][0] + words[1][0];
                }
            } else if (words.length === 1) {
                if(words[0].length >= 2) {
                    initials = words[0].substring(0, 2);
                } else {
                    initials = words[0]; 
                }
            }

            let code = 'M' + initials.toUpperCase() + '01';
            $('#inputIdMateri').val(code);
        });

        // Handle Edit Button Click
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let idMateri = $(this).data('id-materi');
            let nama = $(this).data('nama');
            let idGrup = $(this).data('id-grup');
            let deskripsi = $(this).data('deskripsi');
            let nilai = $(this).data('nilai');

            // Set Form Action
            $('#formEdit').attr('action', '<?= base_url('backend/materi/update') ?>/' + id);

            // Populate Fields
            $('#editNama').val(nama);
            $('#editIdMateri').val(idMateri);
            $('#editDeskripsi').val(deskripsi);
            $('#editNilai').val(nilai);
            
            // Set Select2 Value
            $('#editGrup').val(idGrup).trigger('change');
        });
    });
</script>
<?= $this->endSection(); ?>
