<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';
$pesan_tipe = '';

// ===================================
// 1. PROSES HAPUS PELANGGAN
// ===================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id_pelanggan = (int) ($_GET['id'] ?? 0);

    if ($id_pelanggan > 0) {
        // Ambil ID Pemesanan yang terkait
        $res_pemesanan = mysqli_query($koneksi, "SELECT id_pemesanan FROM pemesanan WHERE id_pelanggan = $id_pelanggan");
        $id_pemesanan_list = [];
        while($row = mysqli_fetch_assoc($res_pemesanan)) {
            $id_pemesanan_list[] = $row['id_pemesanan'];
        }

        // Hapus Pembayaran
        if (!empty($id_pemesanan_list)) {
            $ids_p = implode(',', $id_pemesanan_list);
            mysqli_query($koneksi, "DELETE FROM pembayaran WHERE id_pemesanan IN ($ids_p)");
        }
        
        // Hapus Pemesanan
        mysqli_query($koneksi, "DELETE FROM pemesanan WHERE id_pelanggan = $id_pelanggan");
        
        // Hapus Pelanggan
        $stmt = mysqli_prepare($koneksi, "DELETE FROM pelanggan WHERE id_pelanggan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_pelanggan);
        
        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Data pelanggan beserta riwayatnya berhasil dihapus.";
            $pesan_tipe = 'success';
        } else {
            $pesan = "Gagal menghapus pelanggan: " . mysqli_error($koneksi);
            $pesan_tipe = 'danger';
        }
        mysqli_stmt_close($stmt);

        header("Location: admin_pelanggan.php?status=$pesan_tipe&msg=" . urlencode($pesan));
        exit;
    }
}

// Ambil data pelanggan
$sql_list = "SELECT id_pelanggan, nama, email, no_hp, alamat, dibuat_pada FROM pelanggan ORDER BY dibuat_pada DESC";
$result_list = mysqli_query($koneksi, $sql_list);

// Notifikasi
if(isset($_GET['status']) && isset($_GET['msg'])) {
    $pesan = htmlspecialchars($_GET['msg']);
    $pesan_tipe = htmlspecialchars($_GET['status']);
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Admin - Data Pelanggan | Silexsureng Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff7b00;
            --dark-bg: #212529;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; color: #333; }

        /* NAVBAR KONSISTEN */
        .navbar-custom { background-color: var(--dark-bg); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .nav-link { color: rgba(255,255,255,0.7) !important; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: var(--primary-color) !important; }
        .nav-link.active { font-weight: 600; border-bottom: 2px solid var(--primary-color); }

        .card-modern { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        
        .table thead th { 
            background-color: #f8f9fa; 
            border-bottom: 2px solid #e9ecef;
            font-weight: 600; 
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .avatar-circle {
            width: 40px; height: 40px;
            background-color: #e9ecef;
            color: var(--primary-color);
            border-radius: 50%;
            display: inline-flex;
            align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.1rem;
            margin-right: 12px;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand me-5 fw-bold" href="admin_dashboard.php">
            Sile<span style="color: var(--primary-color);">X</span>sureng <span class="text-white-50 small fw-normal">Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                
                <li class="nav-item"><a class="nav-link" href="admin_pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_pembayaran.php">Pembayaran</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_sopir.php">Sopir</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_rute.php">Rute</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin_pelanggan.php">Pelanggan</a></li>
            </ul>
            
            <div class="d-flex align-items-center">
                <span class="text-white-50 me-3 small d-none d-lg-inline">Hi, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                <a href="logout_admin.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Data Pelanggan</h4>
            <p class="text-muted small mb-0">Daftar pengguna yang telah mendaftar di sistem.</p>
        </div>
    </div>
    
    <?php if ($pesan): ?>
        <div class="alert alert-<?= $pesan_tipe ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-info-circle me-2"></i> <?= $pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-modern">
        <div class="card-body p-0">
            <?php if ($result_list && mysqli_num_rows($result_list) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Pelanggan</th>
                                <th>Kontak</th>
                                <th>Alamat</th>
                                <th>Bergabung Sejak</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_list)): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle">
                                            <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama']) ?></div>
                                            <div class="text-muted small">ID: #<?= $row['id_pelanggan'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="small mb-1"><i class="fas fa-phone-alt text-success me-2"></i> <?= htmlspecialchars($row['no_hp']) ?></div>
                                    <div class="small text-muted"><i class="fas fa-envelope text-secondary me-2"></i> <?= htmlspecialchars($row['email']) ?></div>
                                </td>
                                
                                <td style="max-width: 300px;">
                                    <span class="d-inline-block text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($row['alamat']) ?>">
                                        <?= htmlspecialchars($row['alamat']) ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= date('d M Y', strtotime($row['dibuat_pada'])) ?>
                                    </span>
                                </td>
                                
                                <td class="text-end pe-4">
                                    <a href="admin_pelanggan.php?aksi=hapus&id=<?= $row['id_pelanggan'] ?>"
                                       class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                       onclick="return confirm('PERINGATAN KERAS!\n\nMenghapus pelanggan ini akan menghapus SEMUA RIWAYAT TRANSAKSI mereka.\n\nLanjutkan?');">
                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-25">
                    <p class="text-muted mb-0">Belum ada pelanggan yang mendaftar.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>