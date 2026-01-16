<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Login - Munaqosah SDIT An-Nahl' ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .login-box {
            margin-top: 5%;
        }
        
        .login-logo a {
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .login-logo i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .card {
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.5);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        
        .form-control {
            border-left: none;
            border-radius: 0 0.25rem 0.25rem 0 !important;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <!-- Logo -->
        <div class="login-logo">
            <a href="#">
                <i class="fas fa-graduation-cap d-block"></i>
                <b>Munaqosah</b> SDIT An-Nahl
            </a>
        </div>
        
        <!-- Card Login -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg text-muted">
                    Silakan login untuk mengakses sistem
                </p>

                <!-- Alert Error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                
                <!-- Alert Success -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle mr-2"></i>
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form action="<?= base_url('login') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" name="username" 
                               placeholder="Username" value="<?= old('username') ?>" required autofocus>
                    </div>
                    
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <input type="password" class="form-control" name="password" 
                               placeholder="Password" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login
                            </button>
                        </div>
                    </div>
                </form>
                
                <hr>
                
                <p class="mb-0 text-center text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Sistem Penilaian Ujian Munaqosah<br>
                    SDIT An-Nahl &copy; <?= date('Y') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
