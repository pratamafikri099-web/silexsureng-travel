<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id_pemesanan = (int) ($_GET['id_pemesanan'] ?? 0);
if ($id_pemesanan <= 0) {
    header("Location: admin_pemesanan.php");
    exit;
}

// Ambil data pemesanan (untuk info di form)
$sql_p = "
    SELECT p.id_pemesanan,
           pel.nama AS nama_pelanggan,
           r.asal,
           r.tujuan,
           p.tanggal_berangkat,
           p.jam_berangkat,
           p.jumlah_penumpang
    FROM pemesanan p
    JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    JOIN rute r      ON p.id_rute = r.id_rute
    WHERE p.id_pemesanan = ?
";
$stmt_p = mysqli_prepare($koneksi, $sql_p);
mysqli_stmt_bind_param($stmt_p, "i", $id_pemesanan);
mysqli_stmt_execute($stmt_p);
$res_p = mysqli_stmt_get_result($stmt_p);
$data_p = mysqli_fetch_assoc($res_p);
mysqli_stmt_close($stmt_p);

if (!$data_p) {
    header("Location: admin_pemesanan.php");
    exit;
}

// Ambil daftar sopir aktif
$sopir_list = [];
$sql_s = "SELECT id_sopir, nama, no_hp FROM sopir WHERE aktif = 1 ORDER BY nama";
$res_s = mysqli_query($koneksi, $sql_s);
while ($row = mysqli_fetch_assoc($res_s)) {
    $sopir_list[] = $row;
}

// Proses ketika form disubmit
$pesan_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sopir = (int) ($_POST['id_sopir'] ?? 0);

    if ($id_sopir <= 0) {
        $pesan_error = "Silakan pilih sopir terlebih dahulu.";
    } else {
        // Update pemesanan: set id_sopir dan status jadi 'dialokasikan'
        $stmt_u = mysqli_prepare($koneksi,
            "UPDATE pemesanan SET id_sopir = ?, status = 'dialokasikan' WHERE id_pemesanan = ?"
        );
        mysqli_stmt_bind_param($stmt_u, "ii", $id_sopir, $id_pemesanan);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);

        header("Location: admin_pemesanan.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pilih Sopir - Silexsureng Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .orange-text { color: #ff7b00; }
        .nav-link.active {
            color: #ff7b00 !important;
            border-bottom: 3px solid #ff7b00;
        }
        .summary-box {
            background-color: #e9ecef; /* Warna abu-abu terang */
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #ced4da;
        }
        .summary-box strong {
            color: #343a40; /* Warna gelap untuk penekanan */
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand me-5" href="admin_pemesanan.php">
            <i class="fas fa-tools me-2"></i> Admin Sile<span class="orange">X</span>sureng <span class="orange">Travel</span>
        </a>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="admin_pemesanan.php">
                        <i class="fas fa-clipboard-list me-1"></i> Pemesanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_pembayaran.php">
                        <i class="fas fa-money-check-alt me-1"></i> Pembayaran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active fw-bold" href="admin_sopir.php">
                        <i class="fas fa-users me-1"></i> Kelola Sopir
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_rute.php">
                        <i class="fas fa-road me-1"></i> Kelola Rute
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_pelanggan.php">
                        <i class="fas fa-user-friends me-1"></i> Pelanggan
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="d-flex align-items-center">
            <span class="text-white small me-3 d-none d-lg-inline">
                Login sebagai: <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
            </span>
            <a href="logout_admin.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-center">
                        <i class="fas fa-car-side me-2 orange-text"></i> Penugasan Sopir
                    </h4>
                    
                    <p class="text-center text-muted small">
                        Pilih sopir yang akan ditugaskan untuk pesanan #<?= $data_p['id_pemesanan'] ?>.
                    </p>

                    <h6 class="mb-3 mt-4">Detail Pesanan:</h6>
                    <div class="summary-box mb-4">
                        <div class="row small">
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-user"></i> Pelanggan: <strong><?= htmlspecialchars($data_p['nama_pelanggan']) ?></strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-users"></i> Penumpang: <strong><?= (int)$data_p['jumlah_penumpang'] ?> orang</strong>
                            </div>
                            <div class="col-12 mb-2">
                                <i class="fas fa-route"></i> Rute: 
                                <strong><?= htmlspecialchars($data_p['asal']) ?> &rarr; <?= htmlspecialchars($data_p['tujuan']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <i class="far fa-calendar-alt"></i> Tgl: 
                                <strong><?= htmlspecialchars($data_p['tanggal_berangkat']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <i class="far fa-clock"></i> Jam: 
                                <strong><?= htmlspecialchars(substr($data_p['jam_berangkat'],0,5)) ?></strong>
                            </div>
                        </div>
                    </div>
                    <?php if ($pesan_error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-1"></i> <?= $pesan_error ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-4">
                            <label class="form-label fs-5">
                                <i class="fas fa-id-card me-1"></i> Pilih Sopir yang Tersedia
                            </label>
                            
                            <?php if (empty($sopir_list)): ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-info-circle"></i> Tidak ada sopir aktif. Silakan tambahkan sopir terlebih dahulu.
                                </div>
                            <?php else: ?>
                                <select name="id_sopir" class="form-select form-select-lg" required>
                                    <option value="">-- Pilih Sopir Aktif --</option>
                                    <?php foreach ($sopir_list as $s): ?>
                                        <option value="<?= $s['id_sopir'] ?>">
                                            <?= htmlspecialchars($s['nama']) ?> (HP: <?= htmlspecialchars($s['no_hp']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between pt-3">
                            <a href="admin_pemesanan.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-orange">
                                <i class="fas fa-save me-1"></i> Simpan Penugasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>