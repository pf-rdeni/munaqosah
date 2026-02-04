<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Login - Munaqosah SDIT An-Nahl' ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('template/backend/dist/css/adminlte.min.css') ?>">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: #fff;
        }

        .login-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Left Side */
        .login-left {
            flex: 3;
            background: linear-gradient(135deg, #7F7FD5 0%, #86A8E7 50%, #91EAE4 100%);
            /* Alternative Gradient to match image loosely: Blue/Purple */
            background: linear-gradient(135deg, #5b55e6 0%, #a85bf2 100%); 
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: #fff;
            overflow: hidden;
        }

        /* Decorative Shapes in Background */
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        .shape-1 { width: 300px; height: 300px; top: -50px; left: -50px; }
        .shape-2 { width: 200px; height: 200px; bottom: 100px; right: 50px; }
        .shape-3 { 
            width: 150px; height: 500px; 
            background: linear-gradient(to bottom, rgba(255,255,255,0.05), rgba(255,255,255,0.2));
            transform: rotate(45deg);
            bottom: -150px; left: 20%;
            border-radius: 100px;
        }
        .shape-4 { 
            width: 100px; height: 400px; 
            background: linear-gradient(to bottom, rgba(255,255,255,0.05), rgba(255,255,255,0.2));
            transform: rotate(45deg);
            bottom: -50px; left: 40%;
            border-radius: 100px;
        }

        .login-left-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
        }

        .login-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        }

        .login-subtitle {
            font-size: 1.1rem;
            font-weight: 300;
            line-height: 1.6;
            opacity: 0.9;
        }

        /* Right Side */
        .login-right {
            flex: 2;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
        }

        .login-form-container {
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .login-header h3 {
            font-weight: 700;
            color: #5b55e6; /* Match primary color */
            font-size: 1.5rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group-text {
            background-color: #f3f4f7;
            border: none;
            color: #8898aa;
            border-radius: 50px 0 0 50px;
            padding-left: 1.5rem;
        }

        .form-control {
            background-color: #f3f4f7;
            border: none;
            height: 50px;
            padding-left: 10px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background-color: #e9ecef;
            box-shadow: none;
        }

        /* Radius adjustments */
        .input-group .form-control:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .input-group-append .input-group-text {
            border-radius: 0 50px 50px 0;
            background-color: #f3f4f7;
            padding-right: 1.5rem;
        }

        /* Normal input radius (if no append) */
        .input-group > .form-control {
             border-radius: 0 50px 50px 0;
        }

        .btn-login {
            background: linear-gradient(to right, #5b55e6, #a85bf2);
            border: none;
            height: 50px;
            border-radius: 50px;
            color: #fff;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(91, 85, 230, 0.4);
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(91, 85, 230, 0.6);
            color: #fff;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-container {
                flex-direction: column;
            }
            .login-left {
                flex: 1;
                padding: 3rem 2rem;
                min-height: 40vh;
            }
            .login-title {
                font-size: 2.5rem;
            }
            .login-right {
                flex: 2;
                padding: 3rem 2rem;
                border-top-left-radius: 30px;
                border-top-right-radius: 30px;
                margin-top: -30px; /* Overlap effect */
                z-index: 10;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side: Welcome Info -->
        <div class="login-left">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>

            <div class="login-left-content">
                <h1 class="login-title">Selamat Datang<br>di e-Munaqosah</h1>
                <p class="login-subtitle">
                    Sistem Informasi Manajemen Penilaian Ujian Munaqosah<br>
                    <strong>SDIT AN-NAHL</strong>
                </p>
                <div class="mt-4">
                    <span class="badge badge-light text-primary px-3 py-2 mr-2" style="border-radius:20px">Realtime</span>
                    <span class="badge badge-light text-primary px-3 py-2" style="border-radius:20px">Terintegrasi</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="login-right">
            <div class="login-form-container">
                <div class="login-header">
                    <h3>User Login</h3>
                    <p class="text-muted small mt-2">Masukan username & password anda</p>
                </div>

                <!-- Alert Error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show text-sm">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                
                <!-- Alert Success -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show text-sm">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" name="username" 
                               placeholder="Username" value="<?= old('username') ?>" required autofocus style="border-radius: 0 50px 50px 0;">
                    </div>
                    
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <input type="password" class="form-control" name="password" id="password"
                               placeholder="Password" required style="border-radius: 0;">
                        <div class="input-group-append">
                            <span class="input-group-text" style="cursor: pointer;" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group d-flex justify-content-between align-items-center mb-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remember">
                            <label class="custom-control-label small text-muted" for="remember">Ingat Saya</label>
                        </div>
                        <a href="#" class="small text-muted">Lupa Password?</a>
                    </div>

                    <button type="submit" class="btn btn-login btn-block">
                        LOGIN
                    </button>
                </form>
                
                <div class="mt-5 text-center">
                    <p class="text-muted small mb-0">&copy; <?= date('Y') ?> SDIT An-Nahl</p>
                    <p class="text-muted text-xs mb-0 mt-2" style="font-size: 0.75rem;">
                        Developer by <strong>Deni Rusandi, S.Kom</strong> | 
                        <a href="https://wa.me/6281364290165" target="_blank" class="text-muted" style="text-decoration: none;">
                            <i class="fab fa-whatsapp text-success"></i> 0813-6429-0165
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('template/backend/plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('template/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('template/backend/dist/js/adminlte.min.js') ?>"></script>
    
    <script>
        $(document).ready(function() {
            // Toggle Password
            $('#togglePassword').click(function() {
                const passwordInput = $('#password');
                const icon = $(this).find('i');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
</body>
</html>
