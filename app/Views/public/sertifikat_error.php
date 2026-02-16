<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat - Link Tidak Valid</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-card {
            background: white;
            border-radius: 16px;
            padding: 3rem 2rem;
            max-width: 480px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error-icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.75rem;
        }
        .error-message {
            color: #7f8c8d;
            font-size: 1rem;
            line-height: 1.6;
        }
        .school-logo {
            margin-top: 2rem;
            opacity: 0.5;
            font-size: 0.85rem;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1 class="error-title">Link Tidak Valid</h1>
        <p class="error-message">
            <?= esc($message ?? 'Link sertifikat tidak valid atau telah kadaluarsa. Silakan hubungi panitia untuk mendapatkan link yang benar.') ?>
        </p>
        <div class="school-logo">
            <i class="fas fa-school"></i> SDIT AN-NAHL — Munaqosah
        </div>
    </div>
</body>
</html>
