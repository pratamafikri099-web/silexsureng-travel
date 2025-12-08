<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// ===================================
// 1. PROSES UBAH STATUS
// ===================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'ubah_status_bayar') {
    $id_pemesanan = (int) ($_GET['id'] ?? 0);
    $status_baru  = $_GET['status'] ?? '';

    $status_valid = ['Menunggu Konfirmasi', 'Lunas', 'Ditolak'];

    if ($id_pemesanan > 0 && in_array($status_baru, $status_valid)) {
        $stmt = mysqli_prepare($koneksi, "UPDATE pemesanan SET status_pembayaran = ? WHERE id_pemesanan = ?");
        mysqli_stmt_bind_param($stmt, "si", $status_baru, $id_pemesanan);
        
        if(mysqli_stmt_execute($stmt)) {
            // Sinkronisasi status perjalanan otomatis
            if ($status_baru === 'Lunas') {
                mysqli_query($koneksi, "UPDATE pemesanan SET status = 'dialokasikan' WHERE id_pemesanan = $id_pemesanan");
            } elseif ($status_baru === 'Ditolak') {
                mysqli_query($koneksi, "UPDATE pemesanan SET status = 'pending' WHERE id_pemesanan = $id_pemesanan");
            }
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: admin_pembayaran.php");
    exit;
}

// ===================================
// 2. QUERY DATA
// ===================================
$sql = "
    SELECT 
        p.id_pemesanan,
        p.total_harga,
        p.status_pembayaran,
        p.bukti_pembayaran,
        p.dibuat_pada,
        pel.nama AS nama_pelanggan
    FROM pemesanan p
    JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    WHERE p.status_pembayaran != 'Belum Bayar'
    ORDER BY p.dibuat_pada DESC
";
$result = mysqli_query($koneksi, $sql);

// Fungsi Badge
function get_bayar_badge($status) {
    if ($status == 'Menunggu Konfirmasi') return '<span class="badge bg-warning text-dark rounded-pill px-3"><i class="fas fa-clock me-1"></i> Menunggu</span>';
    if ($status == 'Lunas') return '<span class="badge bg-success rounded-pill px-3"><i class="fas fa-check-circle me-1"></i> LUNAS</span>';
    if ($status == 'Ditolak') return '<span class="badge bg-danger rounded-pill px-3"><i class="fas fa-times-circle me-1"></i> Ditolak</span>';
    return '<span class="badge bg-secondary rounded-pill px-3">'.$status.'</span>';
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Data Pembayaran | Silexsureng Travel</title>
    
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
        
        .avatar-circle {
            width: 35px; height: 35px;
            background-color: #e9ecef;
            color: var(--primary-color);
            border-radius: 50%;
            display: inline-flex;
            align-items: center; justify-content: center;
            font-weight: bold; font-size: 0.9rem;
            margin-right: 10px;
        }

        .btn-view {
            padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;
            background-color: #e7f1ff; color: #0d6efd; border: none; transition: 0.2s;
        }
        .btn-view:hover { background-color: #cfe2ff; color: #0a58ca; }

        .modal-img-preview { max-height: 70vh; max-width: 100%; object-fit: contain; border-radius: 8px; }
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
                
                <li class="nav-item"><a class="nav-link" href="admin_pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin_pembayaran.php">Pembayaran</a></li>
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
            <h4 class="mb-0 fw-bold">Konfirmasi Pembayaran</h4>
            <p class="text-muted small mb-0">Cek bukti transfer dan verifikasi pembayaran pelanggan.</p>
        </div>
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
                                <th>Metode</th>
                                <th>Nominal</th>
                                <th>Bukti Transfer</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="ps-4 text-muted small fw-bold">#<?= $row['id_pemesanan'] ?></td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle">
                                            <?= strtoupper(substr($row['nama_pelanggan'], 0, 1)) ?>
                                        </div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_pelanggan']) ?></div>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-university text-secondary me-1"></i> Transfer
                                    </span>
                                </td>
                                
                                <td class="fw-bold text-success">
                                    Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                </td>
                                
                                <td>
                                    <?php if (!empty($row['bukti_pembayaran'])): ?>
                                        <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $row['id_pemesanan'] ?>">
                                            <i class="far fa-image me-1"></i> Lihat Bukti
                                        </button>

                                        <div class="modal fade" id="modalBukti<?= $row['id_pemesanan'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header border-0">
                                                        <h6 class="modal-title fw-bold">Bukti Transfer #<?= $row['id_pemesanan'] ?></h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center bg-dark p-4 rounded-3 m-3">
                                                        <img src="uploads/<?= $row['bukti_pembayaran'] ?>" class="modal-img-preview shadow-lg">
                                                    </div>
                                                    <div class="modal-footer border-0 justify-content-center">
                                                        <a href="uploads/<?= $row['bukti_pembayaran'] ?>" download class="btn btn-primary rounded-pill px-4">
                                                            <i class="fas fa-download me-2"></i> Download Gambar
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Belum upload</span>
                                    <?php endif; ?>
                                </td>

                                <td class="small text-muted">
                                    <?= date('d M H:i', strtotime($row['dibuat_pada'])) ?>
                                </td>
                                
                                <td>
                                    <?= get_bayar_badge($row['status_pembayaran']) ?>
                                </td>
                                
                                <td class="text-end pe-4">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-light btn-sm border" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><h6 class="dropdown-header">Verifikasi Pembayaran</h6></li>
                                            <li>
                                                <a class="dropdown-item" href="admin_pembayaran.php?aksi=ubah_status_bayar&id=<?= $row['id_pemesanan'] ?>&status=Lunas">
                                                    <i class="fas fa-check text-success me-2"></i> Terima (Lunas)
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="admin_pembayaran.php?aksi=ubah_status_bayar&id=<?= $row['id_pemesanan'] ?>&status=Ditolak">
                                                    <i class="fas fa-times text-danger me-2"></i> Tolak (Salah Transfer)
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-muted" href="admin_pembayaran.php?aksi=ubah_status_bayar&id=<?= $row['id_pemesanan'] ?>&status=Menunggu Konfirmasi">
                                                    <i class="fas fa-undo me-2"></i> Reset Status
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-25">
                    <p class="text-muted mb-0">Belum ada pembayaran baru yang masuk.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>