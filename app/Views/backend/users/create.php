<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus mr-2"></i> Tambah User Baru
                </h3>
            </div>
            <form action="<?= base_url('backend/users/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    <!-- Validation Errors -->
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
                        <input type="text" name="fullname" class="form-control <?= session('errors.fullname') ? 'is-invalid' : '' ?>" value="<?= old('fullname') ?>" placeholder="Contoh: Ahmad Admin">
                        <div class="invalid-feedback"><?= session('errors.fullname') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control <?= session('errors.username') ? 'is-invalid' : '' ?>" value="<?= old('username') ?>" placeholder="Username untuk login">
                        <div class="invalid-feedback"><?= session('errors.username') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" value="<?= old('email') ?>" placeholder="email@contoh.com">
                        <div class="invalid-feedback"><?= session('errors.email') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" placeholder="Minimal 6 karakter">
                        <div class="invalid-feedback"><?= session('errors.password') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Role / Group</label>
                        <select name="group_id" class="form-control <?= session('errors.group_id') ? 'is-invalid' : '' ?>">
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>" <?= old('group_id') == $group['id'] ? 'selected' : '' ?>>
                                    <?= strtoupper($group['name']) ?> - <?= $group['description'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= session('errors.group_id') ?></div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('backend/users') ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
