<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// ===================================
// 1. PROSES HAPUS SATU BARIS
// ===================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus_pesanan') {
    $id_pemesanan = (int) ($_GET['id'] ?? 0);
    
    if ($id_pemesanan > 0) {
        // Hapus pembayaran terkait
        mysqli_query($koneksi, "DELETE FROM pembayaran WHERE id_pemesanan = $id_pemesanan");
        
        // Hapus pemesanan
        $stmt = mysqli_prepare($koneksi, "DELETE FROM pemesanan WHERE id_pemesanan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_pemesanan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: admin_pemesanan.php");
    exit;
}

// ===================================
// 2. PROSES HAPUS SEMUA DATA
// ===================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus_semua') {
    mysqli_query($koneksi, "TRUNCATE TABLE pembayaran");
    mysqli_query($koneksi, "TRUNCATE TABLE pemesanan");

    header("Location: admin_pemesanan.php");
    exit;
}


// Proses ubah status pemesanan
if (isset($_GET['aksi']) && $_GET['aksi'] === 'ubah_status') {
    $id_pemesanan = (int) ($_GET['id'] ?? 0);
    $status_baru  = $_GET['status'] ?? '';

    $status_valid = ['pending','dialokasikan','dalam_perjalanan','selesai','dibatalkan'];

    if ($id_pemesanan > 0 && in_array($status_baru, $status_valid)) {
        $stmt = mysqli_prepare($koneksi, "UPDATE pemesanan SET status = ? WHERE id_pemesanan = ?");
        mysqli_stmt_bind_param($stmt, "si", $status_baru, $id_pemesanan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: admin_pemesanan.php");
    exit;
}

// Ambil data pemesanan
$sql = "
    SELECT 
        p.id_pemesanan,
        pel.nama AS nama_pelanggan,
        r.asal,
        r.tujuan,
        p.tanggal_berangkat,
        p.jam_berangkat,
        p.jumlah_penumpang,
        p.total_harga,
        p.status,
        s.nama AS nama_sopir
    FROM pemesanan p
    JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    JOIN rute r      ON p.id_rute = r.id_rute
    LEFT JOIN sopir s ON p.id_sopir = s.id_sopir
    ORDER BY p.dibuat_pada DESC
";
$result = mysqli_query($koneksi, $sql);

// Fungsi Badge Status
function get_status_badge($status) {
    $status_map = [
        'pending'           => ['Baru', 'warning'],
        'dialokasikan'      => ['Siap Jemput', 'info'],
        'dalam_perjalanan'  => ['OTW', 'primary'],
        'selesai'           => ['Selesai', 'success'],
        'dibatalkan'        => ['Batal', 'danger'],
    ];

    $label = $status_map[$status][0] ?? ucwords(str_replace('_', ' ', $status));
    $color = $status_map[$status][1] ?? 'secondary';

    return "<span class=\"badge rounded-pill bg-{$color} px-3 py-2\">{$label}</span>";
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Data Pemesanan | Silexsureng Travel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #ff7b00;
            --dark-bg: #212529;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; color: #333; }

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
        
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; transition: 0.2s; }
        .btn-action:hover { transform: translateY(-2px); }

        .sopir-badge { background-color: #e3f2fd; color: #0d6efd; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 500; }
    </style>
</head>
<body>

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
                
                <li class="nav-item"><a class="nav-link active" href="admin_pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_pembayaran.php">Pembayaran</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_sopir.php">Sopir</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_rute.php">Rute</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_pelanggan.php">Pelanggan</a></li>
            </ul>
            
            <div class="d-flex align-items-center">
                <span class="text-white-50 me-3 small d-none d-lg-inline">Hi, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                <a href="logout_admin.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Data Pemesanan</h4>
            <p class="text-muted small mb-0">Monitor semua transaksi perjalanan yang masuk.</p>
        </div>
        <a href="admin_pemesanan.php?aksi=hapus_semua"
           class="btn btn-danger btn-sm rounded-pill px-3"
           onclick="return confirm('PERINGATAN KERAS!\n\nAnda yakin ingin MENGHAPUS SEMUA DATA PEMESANAN dan PEMBAYARAN?\n\nData yang dihapus TIDAK BISA DIKEMBALIKAN!');">
            <i class="fas fa-trash-alt me-1"></i> Reset Data
        </a>
    </div>

    <div class="card card-modern">
        <div class="card-body p-0">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Pelanggan</th>
                                <th>Rute Perjalanan</th>
                                <th>Seat</th>
                                <th>Total Bayar</th>
                                <th>Sopir</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="ps-4 text-muted small fw-bold">#<?= $row['id_pemesanan'] ?></td>
                                
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_pelanggan']) ?></div>
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($row['asal']) ?></span>
                                        <i class="fas fa-arrow-right mx-2 text-muted small"></i>
                                        <span class="fw-bold text-primary"><?= htmlspecialchars($row['tujuan']) ?></span>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="far fa-calendar me-1"></i> <?= date('d M', strtotime($row['tanggal_berangkat'])) ?> 
                                        <span class="mx-1">•</span> 
                                        <i class="far fa-clock me-1"></i> <?= substr($row['jam_berangkat'], 0, 5) ?>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border"><?= (int)$row['jumlah_penumpang'] ?> Org</span>
                                </td>
                                
                                <td class="fw-bold text-success">
                                    Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                </td>
                                
                                <td>
                                    <?php if (!empty($row['nama_sopir'])): ?>
                                        <div class="sopir-badge mb-1">
                                            <i class="fas fa-steering-wheel me-1"></i> <?= htmlspecialchars($row['nama_sopir']) ?>
                                        </div>
                                        <a href="admin_pilih_sopir.php?id_pemesanan=<?= $row['id_pemesanan'] ?>" class="small text-muted text-decoration-none">Ubah</a>
                                    <?php else: ?>
                                        <a href="admin_pilih_sopir.php?id_pemesanan=<?= $row['id_pemesanan'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                            <i class="fas fa-plus me-1"></i> Pilih Sopir
                                        </a>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <?= get_status_badge($row['status']) ?>
                                </td>
                                
                                <td class="text-end pe-4">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-light btn-action border" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><h6 class="dropdown-header">Ubah Status</h6></li>
                                            <li><a class="dropdown-item" href="admin_pemesanan.php?aksi=ubah_status&id=<?= $row['id_pemesanan'] ?>&status=pending"><span class="badge bg-warning w-100">Baru</span></a></li>
                                            <li><a class="dropdown-item" href="admin_pemesanan.php?aksi=ubah_status&id=<?= $row['id_pemesanan'] ?>&status=dialokasikan"><span class="badge bg-info w-100">Siap Jemput</span></a></li>
                                            <li><a class="dropdown-item" href="admin_pemesanan.php?aksi=ubah_status&id=<?= $row['id_pemesanan'] ?>&status=dalam_perjalanan"><span class="badge bg-primary w-100">Sedang Jalan</span></a></li>
                                            <li><a class="dropdown-item" href="admin_pemesanan.php?aksi=ubah_status&id=<?= $row['id_pemesanan'] ?>&status=selesai"><span class="badge bg-success w-100">Selesai</span></a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="admin_pemesanan.php?aksi=ubah_status&id=<?= $row['id_pemesanan'] ?>&status=dibatalkan"><i class="fas fa-ban me-2"></i> Batalkan</a></li>
                                        </ul>
                                    </div>
                                    
                                    <a href="admin_pemesanan.php?aksi=hapus_pesanan&id=<?= $row['id_pemesanan'] ?>"
                                       class="btn btn-danger btn-action ms-1"
                                       onclick="return confirm('Yakin ingin menghapus data ini?');"
                                       title="Hapus Permanen">
                                        <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
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
                    <p class="text-muted">Belum ada data pemesanan yang masuk.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-4 text-center">
        <a href="index.php" class="text-decoration-none text-muted small hover-primary">
            <i class="fas fa-arrow-left me-1"></i> Lihat Halaman Utama Website
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>