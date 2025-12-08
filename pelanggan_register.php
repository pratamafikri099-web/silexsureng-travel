<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['pelanggan_id'])) {
    header("Location: pelanggan_dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim(strtolower($_POST['email'] ?? ''));
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // 1. Validasi Input
    if (empty($nama) || empty($username) || empty($email) || empty($no_hp) || empty($alamat) || empty($password) || empty($konfirmasi_password)) {
        $error = "Semua field wajib diisi.";
    } elseif ($password !== $konfirmasi_password) {
        $error = "Password dan Konfirmasi Password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format Email tidak valid.";
    } else {
        // 2. Cek duplikasi
        $stmt_check = mysqli_prepare($koneksi, "SELECT id_pelanggan FROM pelanggan WHERE email = ? OR no_hp = ? OR username = ?");
        mysqli_stmt_bind_param($stmt_check, "sss", $email, $no_hp, $username);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Username, Email, atau Nomor HP sudah terdaftar.";
        } else {
            // 3. Masukkan data
            $sql_insert = "INSERT INTO pelanggan (nama, username, email, no_hp, alamat, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $nama, $username, $email, $no_hp, $alamat, $password);

            if (mysqli_stmt_execute($stmt_insert)) {
                header("Location: pelanggan_login.php?registered=true");
                exit;
            } else {
                $error = "Pendaftaran gagal: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt_check);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun - Silexsureng Travel</title>
    
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
            /* Background Senada Login */
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), url('https://source.unsplash.com/1600x900/?night,road,trip');
            background-size: cover;
            background-position: center;
            background-attachment: fixed; /* Agar background diam saat scroll */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* --- GLASSMORPHISM CARD --- */
        .register-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 650px; /* Lebih lebar dari login */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            color: white;
            position: relative;
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
            font-size: 0.85rem;
            margin-bottom: 6px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
        }

        .form-control {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.5);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 3px rgba(255, 123, 0, 0.2);
        }

        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }

        textarea.form-control { resize: none; }

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
            margin-top: 15px;
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
        .link-login { color: var(--primary-color); font-weight: 600; }
        .link-login:hover { color: #ff9e42; text-decoration: underline; }
        
        .link-home { color: rgba(255, 255, 255, 0.6); font-size: 0.85rem; }
        .link-home:hover { color: white; }

        .alert-glass {
            background: rgba(220, 53, 69, 0.25);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ffcccc;
            border-radius: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="text-center mb-4">
        <div class="brand-title">Sile<span class="text-orange">X</span>sureng</div>
        <p class="small text-white-50">Bergabung sekarang untuk perjalanan yang lebih mudah</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-glass p-2 mb-3 text-center">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Min. 4 karakter" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Nomor HP (WhatsApp)</label>
                <input type="tel" name="no_hp" class="form-control" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="off">
            </div>

            <div class="col-12">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat" class="form-control" rows="2" placeholder="Jalan, Nomor Rumah, Kelurahan..." required><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="passwordInputReg" class="form-control border-end-0" required autocomplete="new-password">
                    <span class="input-group-text rounded-end" id="togglePasswordReg">
                        <i class="fas fa-eye" id="eyeIconReg"></i>
                    </span>
                </div>
                <small class="text-white-50" style="font-size: 0.7rem;">Minimal 6 karakter</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="konfirmasi_password" class="form-control" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-modern">DAFTAR SEKARANG</button>
    </form>

    <div class="text-center mt-4">
        <p class="small text-white-50 mb-2">Sudah punya akun?</p>
        <a href="pelanggan_login.php" class="link-login">Login disini</a>
    </div>
    
    <div class="text-center mt-3 pt-3 border-top border-secondary border-opacity-50">
        <a href="index.php" class="link-home"><i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('passwordInputReg');
        const togglePassword = document.getElementById('togglePasswordReg');
        const eyeIcon = document.getElementById('eyeIconReg');

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