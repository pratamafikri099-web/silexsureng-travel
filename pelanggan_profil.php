<?php
session_start();
include 'koneksi.php';

// Cek sesi login
if (!isset($_SESSION['pelanggan_id'])) {
    header("Location: pelanggan_login.php");
    exit;
}

$id = $_SESSION['pelanggan_id'];
$msg = "";

// PROSES UPDATE
if (isset($_POST['update'])) {
    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['telepon']); 
    $alamat  = mysqli_real_escape_string($koneksi, $_POST['alamat']); 
    $pass    = $_POST['password'];

    if (!empty($pass)) {
        $pass = mysqli_real_escape_string($koneksi, $pass);
        $sql = "UPDATE pelanggan SET nama='$nama', no_hp='$telepon', alamat='$alamat', password='$pass' WHERE id_pelanggan='$id'";
    } else {
        $sql = "UPDATE pelanggan SET nama='$nama', no_hp='$telepon', alamat='$alamat' WHERE id_pelanggan='$id'";
    }

    if (mysqli_query($koneksi, $sql)) {
        $_SESSION['pelanggan_nama'] = $nama; // Update sesi nama agar navbar langsung berubah
        $msg = "<div class='alert alert-glass-success'><i class='fas fa-check-circle me-2'></i> Profil berhasil diperbarui!</div>";
    } else {
        $msg = "<div class='alert alert-glass-danger'><i class='fas fa-exclamation-circle me-2'></i> Gagal update: " . mysqli_error($koneksi) . "</div>";
    }
}

// AMBIL DATA TERBARU
$query = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan='$id'");
$d = mysqli_fetch_assoc($query);
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya - Silexsureng Travel</title>
    
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
            /* Background Senada dengan Login/Register */
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), url('https://source.unsplash.com/1600x900/?night,road,trip');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            padding: 40px 0;
            color: white;
        }

        /* --- GLASS CARD --- */
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            color: white;
            position: relative;
        }

        /* Avatar Circle */
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: bold;
            margin: 0 auto 20px auto;
            border: 4px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 20px rgba(255, 123, 0, 0.5);
        }

        .form-label {
            font-size: 0.9rem;
            margin-bottom: 8px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Custom Input Fields */
        .form-control {
            background: rgba(0, 0, 0, 0.3);
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
        
        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }
        
        .input-group-text {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-right: none;
            color: var(--primary-color);
        }
        
        /* Fix input border radius when using input group */
        .input-group .form-control { border-left: none; }

        /* Custom Alerts */
        .alert-glass-success {
            background: rgba(25, 135, 84, 0.2);
            border: 1px solid rgba(25, 135, 84, 0.5);
            color: #b3ffcc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .alert-glass-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ffb3b3;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        /* Buttons */
        .btn-save {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: 0.3s;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(255, 123, 0, 0.3); color: white; }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 500;
            padding: 12px;
            border-radius: 10px;
            transition: 0.3s;
        }
        .btn-back:hover { background: rgba(255, 255, 255, 0.2); color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            
            <div class="glass-card">
                <div class="profile-avatar">
                    <?= strtoupper(substr($d['nama'], 0, 1)) ?>
                </div>
                
                <h3 class="text-center fw-bold mb-1">Profil Saya</h3>
                <p class="text-center text-white-50 mb-4 small">Kelola informasi akun Anda</p>

                <?= $msg ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($d['nama']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon / WA</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($d['no_hp']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Penjemputan (Default)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($d['alamat'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <hr style="border-color: rgba(255,255,255,0.2); margin: 25px 0;">
                    
                    <div class="mb-4">
                        <label class="form-label text-warning small">
                            <i class="fas fa-lock me-1"></i> Ganti Password (Opsional)
                        </label>
                        <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengganti">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="update" class="btn btn-save">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                        <a href="pelanggan_dashboard.php" class="btn btn-back text-center">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>