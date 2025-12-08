<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung ke dashboard sopir
if (isset($_SESSION['sopir_id'])) {
    header("Location: sopir_dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        // Cek username di tabel sopir
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM sopir WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);

        // Cek apakah data ditemukan
        if ($data) {
            // Verifikasi password
            if ($password === $data['password']) {
                
                // Cek kolom 'aktif'
                if ($data['aktif'] != 1) {
                    $error = "Akun Anda sedang dinonaktifkan. Hubungi Admin.";
                } else {
                    // Buat Session
                    $_SESSION['sopir_id']   = $data['id_sopir'];
                    $_SESSION['sopir_nama'] = $data['nama']; 
                    $_SESSION['role']       = 'sopir';

                    header("Location: sopir_dashboard.php");
                    exit;
                }

            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Sopir - Silexsureng Travel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-yellow: #ffc107;
            --primary-gradient: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
        }

        body {
            font-family: 'Poppins', sans-serif;
            /* Background Jalan Tol / Mobil agar relevan */
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), url('https://source.unsplash.com/1600x900/?highway,car,driver');
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
            background: rgba(40, 40, 40, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Aksen garis atas kuning */
        .login-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 4px;
            background: var(--primary-yellow);
        }

        .brand-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        .text-yellow { color: var(--primary-yellow); }

        /* --- INPUT FIELDS --- */
        .form-label {
            font-size: 0.85rem;
            margin-bottom: 8px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
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
            border-color: var(--primary-yellow);
            color: white;
            box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.2);
        }

        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-left: none;
            color: var(--primary-yellow); /* Icon warna kuning */
            cursor: pointer;
        }

        /* --- BUTTON --- */
        .btn-sopir {
            background: var(--primary-yellow);
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            color: #212529; /* Teks hitam agar kontras */
            letter-spacing: 0.5px;
            margin-top: 15px;
            transition: 0.3s;
        }
        .btn-sopir:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 193, 7, 0.3);
            background-color: #e0a800;
        }

        /* --- ALERTS & LINKS --- */
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
        <div class="brand-title">Sile<span class="text-yellow">X</span>sureng</div>
        <span class="badge bg-warning bg-opacity-10 border border-warning text-warning px-3 py-2 rounded-pill">
            <i class="fas fa-steering-wheel me-1"></i> DRIVER AREA
        </span>
    </div>

    <?php if($error): ?>
        <div class="alert alert-glass p-2 mb-3 text-center">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <input type="text" name="username" class="form-control border-end-0" placeholder="Username..." required autocomplete="off">
                <span class="input-group-text rounded-end"><i class="fas fa-user"></i></span>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <div class="input-group">
                <input type="password" name="password" class="form-control border-end-0" placeholder="Password..." required>
                <span class="input-group-text rounded-end"><i class="fas fa-lock"></i></span>
            </div>
        </div>

        <button type="submit" name="login" class="btn btn-sopir">MASUK SEKARANG</button>
    </form>

    <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-50">
        <a href="index.php" class="link-home"><i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>