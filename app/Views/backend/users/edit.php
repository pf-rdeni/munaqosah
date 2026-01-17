<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit mr-2"></i> Edit User
                </h3>
            </div>
            <form action="<?= base_url('backend/users/update/' . $targetUser['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                   
                   <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="fullname" class="form-control <?= session('errors.fullname') ? 'is-invalid' : '' ?>" value="<?= old('fullname', $targetUser['fullname']) ?>">
                        <div class="invalid-feedback"><?= session('errors.fullname') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control <?= session('errors.username') ? 'is-invalid' : '' ?>" value="<?= old('username', $targetUser['username']) ?>">
                        <div class="invalid-feedback"><?= session('errors.username') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" value="<?= old('email', $targetUser['email']) ?>">
                        <div class="invalid-feedback"><?= session('errors.email') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Password (Biarkan kosong jika tidak ingin mengganti)</label>
                        <input type="password" name="password" class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" placeholder="Isi hanya jika ingin ubah password">
                        <div class="invalid-feedback"><?= session('errors.password') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Role / Group</label>
                        <select name="group_id" class="form-control <?= session('errors.group_id') ? 'is-invalid' : '' ?>" required>
                            <?php 
                                // Get current group id
                                $currentGroupId = $targetUser['groups'][0]['id'] ?? '';
                            ?>
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>" <?= (old('group_id') == $group['id'] || $currentGroupId == $group['id']) ? 'selected' : '' ?>>
                                    <?= strtoupper($group['name']) ?> - <?= $group['description'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= session('errors.group_id') ?></div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('backend/users') ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
