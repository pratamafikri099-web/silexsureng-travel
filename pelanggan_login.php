<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['pelanggan_id'])) {
    header("Location: pelanggan_dashboard.php");
    exit;
}

$error = "";
$next_url = htmlspecialchars($_GET['next'] ?? 'pelanggan_dashboard.php'); // URL tujuan setelah login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_hp = trim($_POST['email_hp'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input wajib diisi
    if ($email_hp === '' || $password === '') {
        $error = "Email/No. HP dan password wajib diisi.";
    } else {
        // Logika login: cari berdasarkan email ATAU no_hp
        $stmt = mysqli_prepare($koneksi, "SELECT id_pelanggan, nama, password FROM pelanggan WHERE email = ? OR no_hp = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email_hp, $email_hp);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_bind_result($stmt, $id_pelanggan, $nama, $db_password);
            mysqli_stmt_fetch($stmt);
            
            // Verifikasi Password (Jika menggunakan password_hash, gunakan password_verify)
            if ($password === $db_password) { 
                // Login berhasil
                $_SESSION['pelanggan_id'] = $id_pelanggan;
                $_SESSION['pelanggan_nama'] = $nama;

                // Arahkan ke URL tujuan atau dashboard default
                header("Location: " . $next_url);
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Email atau Nomor HP tidak terdaftar.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Silexsureng Travel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #ff7b00;
            --primary-gradient: linear-gradient(135deg, #ff9100 0%, #ff5e00 100%);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('https://source.unsplash.com/1600x900/?night,road,travel');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        /* --- GLASSMORPHISM CARD --- */
        .login-card {
            background: rgba(255, 255, 255, 0.1); /* Transparan Putih */
            backdrop-filter: blur(20px);           /* Efek Blur Kaca */
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Efek cahaya di atas kartu */
        .login-card::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .brand-title {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        .text-orange { color: var(--primary-color); }

        /* --- INPUT FIELDS --- */
        .form-label {
            font-size: 0.9rem;
            margin-bottom: 8px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
        }

        .form-control {
            background: rgba(0, 0, 0, 0.3); /* Input transparan gelap */
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 15px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.5);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 4px rgba(255, 123, 0, 0.2);
        }

        /* Input Placeholder Color */
        .form-control::placeholder { color: rgba(255, 255, 255, 0.5); }

        .input-group-text {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-left: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
        }

        /* --- BUTTON --- */
        .btn-modern {
            background: var(--primary-gradient);
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-top: 10px;
            transition: 0.3s;
            color: white;
        }
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 123, 0, 0.4);
            color: white;
        }

        /* --- LINKS --- */
        a { text-decoration: none; transition: 0.2s; }
        .link-register { color: var(--primary-color); font-weight: 600; }
        .link-register:hover { color: #ff9e42; text-decoration: underline; }
        
        .link-home { color: rgba(255, 255, 255, 0.6); font-size: 0.85rem; }
        .link-home:hover { color: white; }

        .alert-glass {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ffb3b3;
            border-radius: 10px;
            font-size: 0.9rem;
        }
        .alert-success-glass {
            background: rgba(25, 135, 84, 0.2);
            border: 1px solid rgba(25, 135, 84, 0.5);
            color: #b3ffcc;
            border-radius: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-title">Sile<span class="text-orange">X</span>sureng</div>
        <p class="small text-white-50">Silakan login untuk melanjutkan</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-glass p-2 mb-3 text-center">
            <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success-glass p-2 mb-3 text-center">
            <i class="fas fa-check-circle me-1"></i> Pendaftaran berhasil! Silakan Login.
        </div>
    <?php endif; ?>

    <form method="post" action="pelanggan_login.php?next=<?= urlencode($next_url) ?>">
        <div class="mb-3">
            <label class="form-label">Email atau Nomor HP</label>
            <div class="input-group">
                <input type="text" name="email_hp" class="form-control" 
                       value="<?= htmlspecialchars($_POST['email_hp'] ?? '') ?>" 
                       placeholder="Contoh: 0812xxx atau email@gmail.com" required autocomplete="off">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="passwordInput" class="form-control border-end-0" placeholder="Masukkan password" required>
                <span class="input-group-text rounded-end" id="togglePassword">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-modern">MASUK SEKARANG</button>
    </form>

    <div class="text-center mt-4">
        <p class="small text-white-50 mb-2">Belum punya akun?</p>
        <a href="pelanggan_register.php" class="link-register">Daftar Member Baru</a>
    </div>
    
    <div class="text-center mt-3 pt-3 border-top border-secondary border-opacity-50">
        <a href="index.php" class="link-home"><i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('passwordInput');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        if (togglePassword) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                eyeIcon.classList.toggle('fa-eye');
                eyeIcon.classList.toggle('fa-eye-slash');
            });
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>