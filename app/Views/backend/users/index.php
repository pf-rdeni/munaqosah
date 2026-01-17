<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users-cog mr-2"></i>
                    Daftar Pengguna
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/users/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="tabelUsers" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role / Grup</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($usersList as $user): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= esc($user['username']) ?></td>
                                <td><?= esc($user['fullname']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td>
                                    <?php 
                                        $badges = [
                                            'admin'     => 'danger',
                                            'panitia'   => 'warning',
                                            'juri'      => 'success',
                                            'kepala'    => 'info',
                                            'siswa'     => 'secondary'
                                        ];
                                        $roleName = strtolower($user['group_name'] ?? 'user');
                                        $badgeColor = $badges[$roleName] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $badgeColor ?>">
                                        <?= strtoupper($user['group_name'] ?? 'User') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?= base_url('backend/users/edit/'.$user['id']) ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if(session()->get('user_id') != $user['id']): ?>
                                            <a href="<?= base_url('backend/users/delete/'.$user['id']) ?>" 
                                               class="btn btn-danger btn-sm btn-delete" 
                                               data-name="<?= esc($user['username']) ?>"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-danger btn-sm" disabled><i class="fas fa-trash"></i></button>
                                        <?php endif; ?>
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
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function() {
        $('#tabelUsers').DataTable({
            responsive: true,
            autoWidth: false,
        });
    });
</script>
<?= $this->endSection(); ?>
