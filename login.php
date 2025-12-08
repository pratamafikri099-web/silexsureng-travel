<?php
session_start();
include 'koneksi.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input wajib diisi
    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        // Logika login
        $stmt = mysqli_prepare($koneksi, "SELECT id_admin, username, password FROM admin WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $id_admin, $db_username, $db_password);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_fetch($stmt);
            
            // Perbandingan password
            if ($password === $db_password) { 
                // login berhasil
                $_SESSION['admin_id'] = $id_admin;
                $_SESSION['admin_username'] = $db_username;
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
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
    <title>Login Admin - Silexsureng Travel</title>
    
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
            /* Background berbeda untuk Admin (Nuansa Kota Malam/Gedung) */
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), url('https://source.unsplash.com/1600x900/?building,night,city');
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
            background: rgba(30, 30, 30, 0.6); /* Lebih gelap dari pelanggan */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Aksen garis atas oranye */
        .login-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 4px;
            background: var(--primary-gradient);
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
            margin-bottom: 8px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 4px rgba(255, 123, 0, 0.2);
        }

        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-left: none;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
        }

        /* --- BUTTON --- */
        .btn-modern {
            background: var(--primary-gradient);
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
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

        /* --- ALERTS --- */
        .alert-glass {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ffb3b3;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .link-home { color: rgba(255, 255, 255, 0.5); font-size: 0.85rem; text-decoration: none; transition: 0.3s;}
        .link-home:hover { color: white; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-5">
        <div class="brand-title">Sile<span class="text-orange">X</span>sureng</div>
        <span class="badge bg-secondary bg-opacity-50 border border-secondary text-light px-3 py-2 rounded-pill">
            <i class="fas fa-user-shield me-1"></i> ADMINISTRATOR AREA
        </span>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-glass p-2 mb-3 text-center">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <input type="text" name="username" class="form-control" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                       placeholder="Masukkan Username Admin" required autocomplete="off">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="passwordInputAdmin" class="form-control border-end-0" placeholder="Masukkan Password" required>
                <span class="input-group-text rounded-end" id="togglePasswordAdmin">
                    <i class="fas fa-eye" id="eyeIconAdmin"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-modern">LOGIN ADMIN</button>
    </form>

    <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-50">
        <a href="index.php" class="link-home"><i class="fas fa-arrow-left me-1"></i> Kembali ke Website Utama</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('passwordInputAdmin');
        const togglePassword = document.getElementById('togglePasswordAdmin');
        const eyeIcon = document.getElementById('eyeIconAdmin');

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