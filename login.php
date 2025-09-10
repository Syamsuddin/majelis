<?php
// =================================================================
// Login Page - eMajelis
// =================================================================
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include configuration
require_once 'config.php';

// Create database connection
$koneksi = createDatabaseConnection();

// Include auth functions
include 'auth_functions.php';

$error_message = '';
$success_message = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi';
    } else {
        $login_result = loginUser($koneksi, $username, $password);
        
        if ($login_result['success']) {
            // Login berhasil, redirect ke dashboard
            header("Location: index.php");
            exit();
        } else {
            $error_message = $login_result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - eMajelis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 20%, #334155 40%, #475569 60%, #64748b 80%, #94a3b8 100%);
            background-size: 400% 400%;
            background-attachment: fixed;
            animation: navyGradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 1rem;
            box-sizing: border-box;
        }
        
        @keyframes navyGradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Floating animation elements */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="80" cy="30" r="1" fill="white" opacity="0.15"/><circle cx="40" cy="70" r="1.5" fill="white" opacity="0.1"/><circle cx="90" cy="80" r="1" fill="white" opacity="0.2"/><circle cx="10" cy="90" r="2" fill="white" opacity="0.1"/><circle cx="60" cy="10" r="1.2" fill="%2394a3b8" opacity="0.12"/><circle cx="30" cy="50" r="0.8" fill="%2364748b" opacity="0.08"/><circle cx="75" cy="65" r="1.8" fill="white" opacity="0.08"/></svg>') repeat;
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }
        
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(100, 116, 139, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(148, 163, 184, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 40% 40%, rgba(51, 65, 85, 0.08) 0%, transparent 50%);
            pointer-events: none;
            animation: glow 8s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            0% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 28px;
            box-shadow: 
                0 40px 80px rgba(15, 23, 42, 0.25),
                0 20px 40px rgba(30, 41, 59, 0.2),
                0 8px 16px rgba(51, 65, 85, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.5),
                inset 0 -1px 0 rgba(100, 116, 139, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 950px;
            position: relative;
            z-index: 1;
            animation: containerFloat 6s ease-in-out infinite;
            min-height: 600px;
            display: flex;
            flex-direction: column;
        }
        
        @keyframes containerFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        
        .login-form {
            padding: 3.5rem;
            position: relative;
            min-height: 100%;
            background: rgba(255, 255, 255, 0.98);
        }
        
        .row.h-100.flex-fill {
            min-height: 600px;
        }
        
        .col-md-6.d-flex {
            min-height: 100%;
        }
        
        .login-image {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 20%, #334155 40%, #475569 60%, #64748b 80%, #94a3b8 100%);
            background-size: 300% 300%;
            animation: navyGradientMove 12s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 3.5rem;
            position: relative;
            overflow: hidden;
            min-height: 100%;
            height: 100%;
        }
        
        @keyframes navyGradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.15) 0%, transparent 60%),
                        radial-gradient(circle at 70% 80%, rgba(148,163,184,0.12) 0%, transparent 60%),
                        radial-gradient(circle at 50% 50%, rgba(100,116,139,0.08) 0%, transparent 70%);
            animation: shimmer 10s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }
        
        .login-image .text-center {
            position: relative;
            z-index: 1;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 30%, #334155 60%, #475569 100%);
            background-size: 200% 200%;
            border: none;
            border-radius: 14px;
            padding: 16px 0;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: navyButtonGlow 3s ease-in-out infinite;
        }
        
        @keyframes navyButtonGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 
                0 25px 50px rgba(15, 23, 42, 0.35),
                0 15px 30px rgba(30, 41, 59, 0.25),
                0 8px 16px rgba(51, 65, 85, 0.2);
            background-size: 300% 300%;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid rgba(15, 23, 42, 0.1);
            padding: 14px 18px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        
        .form-control:focus {
            border-color: #64748b;
            box-shadow: 0 0 0 0.25rem rgba(100, 116, 139, 0.15);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .input-group-text {
            background: rgba(15, 23, 42, 0.05);
            border: 2px solid rgba(15, 23, 42, 0.1);
            color: #0f172a;
        }
        
        .brand-icon {
            font-size: 4.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.2);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .welcome-title {
            color: #0f172a;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .welcome-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
        }
        
        .form-label {
            color: #0f172a;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }
        
        .login-info {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.05) 0%, rgba(100, 116, 139, 0.05) 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(15, 23, 42, 0.1);
        }
        
        .logo-container {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.1);
            transition: all 0.3s ease;
        }
        
        .logo-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.15);
        }
        
        .logo-image {
            max-width: 120px;
            max-height: 120px;
            width: auto;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(15, 23, 42, 0.3));
        }
        
        .logo-image:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 0 1rem;
                max-width: none;
                width: calc(100% - 2rem);
            }
            
            .login-form {
                padding: 2rem;
            }
            
            .login-image {
                padding: 2rem;
            }
            
            .brand-icon {
                font-size: 3rem;
            }
            
            .welcome-title {
                font-size: 1.5rem;
            }
            
            .logo-container {
                margin-top: 1.5rem;
                padding: 1rem;
            }
            
            .logo-image {
                max-width: 80px;
                max-height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-container">
            <div class="row g-0 h-100 flex-fill">
                <!-- Bagian Kiri - Form Login -->
                <div class="col-md-6 d-flex">
                    <div class="login-form flex-fill d-flex flex-column justify-content-center">
                        <div class="text-center mb-4">
                            <h2 class="welcome-title">Selamat Datang</h2>
                            <p class="welcome-subtitle">Silakan login ke akun eMajelis Anda</p>
                        </div>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= htmlspecialchars($error_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?= htmlspecialchars($success_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="username" name="username" 
                                           placeholder="Masukkan username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                           style="border-radius: 0 10px 10px 0;">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="password" name="password" 
                                           placeholder="Masukkan password" required
                                           style="border-radius: 0 10px 10px 0;">
                                </div>
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-primary btn-login w-100 text-white">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Masuk
                            </button>
                        </form>
                        
                        <div class="login-info mt-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                <strong class="text-primary">Info Login Default</strong>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <small class="d-block"><strong>Admin:</strong></small>
                                    <small class="text-muted">admin / admin123</small>
                                </div>
                                <div class="col-6">
                                    <small class="d-block"><strong>Operator:</strong></small>
                                    <small class="text-muted">operator / operator123</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bagian Kanan - Branding -->
                <div class="col-md-6 d-flex">
                    <div class="login-image flex-fill d-flex flex-column justify-content-center">
                        <div class="text-center">
                            <i class="bi bi-moon-stars-fill brand-icon"></i>
                            <h3 class="fw-bold">eMajelis</h3>
                            <p class="opacity-75 mb-0">Sistem Informasi Majelis Digital</p>
                            <small class="opacity-50">Versi 2.0 dengan Multi User</small>
                            
                            <!-- Logo Section -->
                            <div class="logo-container">
                                <img src="./img/logo.jpg" alt="Logo Majelis" class="logo-image" 
                                     onerror="this.style.display='none'">
                            </div>
                            <br/>
                            <h3>Mejelis Ta'lim</h3>
                            <h2>AL BALADUL AMIN</h2>
                            <h5>Telaga Langsat</h5>
                            <br/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>