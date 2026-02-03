<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-layer-group mr-2"></i>
                    Data Grup Materi
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Grup
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="tabelGrup" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>ID Grup</th>
                            <th>Nama Grup Materi</th>
                            <th>Urutan</th>
                            <th>Kondisional Set</th>
                            <th>Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($grupList as $grup): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><code><?= esc($grup['id_grup_materi']) ?></code></td>
                                <td><?= esc($grup['nama_grup_materi']) ?></td>
                                <td><?= esc($grup['urutan']) ?></td>
                                <td class="text-center">
                                    <?php
                                        $badges = [
                                            'nilai_default' => 'badge-secondary',
                                            'nilai_pengurangan' => 'badge-warning',
                                            'nilai_penjumlahan' => 'badge-success'
                                        ];
                                        $labels = [
                                            'nilai_default' => 'Default',
                                            'nilai_pengurangan' => 'Pengurangan',
                                            'nilai_penjumlahan' => 'Penjumlahan'
                                        ];
                                        $val = $grup['kondisional_set'] ?? 'nilai_default';
                                    ?>
                                    <span class="badge <?= $badges[$val] ?? 'badge-secondary' ?>">
                                        <?= $labels[$val] ?? 'Default' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($grup['status'] == 'aktif'): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-xs btn-edit" 
                                        data-toggle="modal" data-target="#modalEdit"
                                        data-id="<?= $grup['id'] ?>"
                                        data-id-grup="<?= $grup['id_grup_materi'] ?>"
                                        data-nama="<?= esc($grup['nama_grup_materi']) ?>"
                                        data-deskripsi="<?= esc($grup['deskripsi']) ?>"
                                        data-urutan="<?= $grup['urutan'] ?>"
                                        data-kondisional="<?= $grup['kondisional_set'] ?>"
                                        data-status="<?= $grup['status'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <button type="button" class="btn btn-danger btn-xs" onclick="confirmDelete('<?= base_url('backend/grup-materi/delete/' . $grup['id']) ?>')">
                                        <i class="fas fa-trash"></i> Hapus
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Grup Materi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('backend/grup-materi/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Grup Materi</label>
                        <input type="text" id="inputNama" name="nama_grup_materi" class="form-control" placeholder="Contoh: Praktek Sholat" required>
                    </div>
                    <div class="form-group">
                        <label>ID Grup (Otomatis/Manual)</label>
                        <input type="text" id="inputID" name="id_grup_materi" class="form-control" placeholder="Generated ID">
                        <small class="text-muted">Akan digenerate otomatis jika kosong.</small>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Urutan (Prioritas)</label>
                        <input type="number" name="urutan" class="form-control" value="0">
                        <small class="text-muted">Semakin kecil, semakin prioritas.</small>
                    </div>
                    <div class="form-group">
                        <label>Kondisional Set</label>
                        <select name="kondisional_set" class="form-control">
                            <option value="nilai_default">Nilai Default / Normal</option>
                            <option value="nilai_pengurangan">Pengurangan (Minus)</option>
                            <option value="nilai_penjumlahan">Penjumlahan (Bonus)</option>
                        </select>
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
                <h4 class="modal-title">Edit Grup Materi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEdit" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>ID Grup (Read-Only)</label>
                        <input type="text" id="editIdGrup" name="id_grup_materi" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nama Grup Materi</label>
                        <input type="text" id="editNama" name="nama_grup_materi" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea id="editDeskripsi" name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Urutan (Prioritas)</label>
                        <input type="number" id="editUrutan" name="urutan" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Kondisional Set</label>
                        <select id="editKondisional" name="kondisional_set" class="form-control">
                            <option value="nilai_default">Nilai Default / Normal</option>
                            <option value="nilai_pengurangan">Pengurangan (Minus)</option>
                            <option value="nilai_penjumlahan">Penjumlahan (Bonus)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="editStatus" name="status" class="form-control">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
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
        $('#tabelGrup').DataTable({
            responsive: true,
            autoWidth: false,
        });

        // Auto Generate ID on Input Nama (Modal Tambah)
        $('#inputNama').on('input', function() {
            let name = $(this).val().trim();
            if (name.length === 0) {
                $('#inputID').val('');
                return;
            }
            let words = name.split(/\s+/);
            let code = '';
            if (words.length >= 2) {
                let c1 = words[0].charAt(0);
                let c2 = words[1].charAt(0);
                code = (c1 + c2).toUpperCase();
            } else if (words.length === 1) {
                let word = words[0];
                let c1 = word.charAt(0);
                let c2 = word.charAt(word.length - 1);
                code = (c1 + c2).toUpperCase();
            }
            if (code.length === 2) {
                $('#inputID').val(code + '01');
            }
        });

        // Handle Edit Button Click
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let idGrup = $(this).data('id-grup');
            let nama = $(this).data('nama');
            let deskripsi = $(this).data('deskripsi');
            let urutan = $(this).data('urutan');
            let kondisional = $(this).data('kondisional');
            let status = $(this).data('status');

            // Populate Form
            $('#formEdit').attr('action', '<?= base_url('backend/grup-materi/update') ?>/' + id);
            $('#editIdGrup').val(idGrup);
            $('#editNama').val(nama);
            $('#editDeskripsi').val(deskripsi);
            $('#editUrutan').val(urutan);
            $('#editKondisional').val(kondisional);
            $('#editStatus').val(status);
        });
    });
</script>
<?= $this->endSection(); ?>
