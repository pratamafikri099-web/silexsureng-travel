<?php
session_start();
include 'koneksi.php';

// Cek keamanan
if (!isset($_SESSION['sopir_id'])) {
    header("Location: login_sopir.php");
    exit;
}

$id_sopir = $_SESSION['sopir_id'];
$nama_sopir = $_SESSION['sopir_nama'] ?? 'Sopir';

// QUERY TUGAS
$query = "
    SELECT 
        p.id_pemesanan, 
        p.tanggal_berangkat, 
        p.jam_berangkat, 
        p.jumlah_penumpang, 
        p.status,
        pel.alamat AS alamat_jemput,
        pel.nama AS nama_pelanggan, 
        pel.no_hp AS hp_pelanggan,
        r.asal, 
        r.tujuan
    FROM pemesanan p
    JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    JOIN rute r ON p.id_rute = r.id_rute
    WHERE p.id_sopir = '$id_sopir' 
    AND p.status NOT IN ('selesai', 'dibatalkan') 
    ORDER BY p.tanggal_berangkat ASC, p.jam_berangkat ASC
";

$result = mysqli_query($koneksi, $query);
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Sopir - Silexsureng</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-yellow: #ffc107;
            --dark-bg: #212529;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; color: #333; }

        /* NAVBAR */
        .navbar-custom { background-color: var(--dark-bg); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 700; letter-spacing: -0.5px; }

        /* CARD TUGAS */
        .task-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: transform 0.2s;
            position: relative;
            overflow: hidden;
        }
        .task-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        
        /* Garis Warna Status di Kiri */
        .status-line {
            position: absolute; left: 0; top: 0; bottom: 0; width: 5px;
        }
        .line-pending { background-color: #ffc107; }      /* Kuning */
        .line-process { background-color: #0d6efd; }      /* Biru */
        .line-pickup { background-color: #0dcaf0; }       /* Biru Muda */

        .card-body { padding: 20px; }

        .route-title { font-size: 1.1rem; font-weight: 700; color: #333; }
        .passenger-info { background-color: #f8f9fa; border-radius: 8px; padding: 12px; margin: 15px 0; border: 1px solid #e9ecef; }
        
        .badge-status { 
            font-size: 0.75rem; padding: 5px 10px; border-radius: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; 
        }
        .bg-soft-warning { background: #fff8e1; color: #d39e00; }
        .bg-soft-info { background: #cff4fc; color: #055160; }
        .bg-soft-primary { background: #cfe2ff; color: #084298; }

        .btn-action { border-radius: 8px; font-weight: 600; font-size: 0.9rem; padding: 10px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top mb-4">
    <div class="container">
        <a class="navbar-brand text-warning" href="#">
            <i class="fas fa-steering-wheel me-2"></i> Area Sopir
        </a>
        <div class="d-flex align-items-center">
            <span class="text-white-50 me-3 small d-none d-md-inline">Halo, <?= htmlspecialchars($nama_sopir) ?></span>
            <a href="sopir_logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Keluar</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Jadwal Tugas</h4>
            <p class="text-muted small mb-0">Daftar penumpang yang harus dijemput.</p>
        </div>
        <button class="btn btn-sm btn-light border shadow-sm" onclick="location.reload()">
            <i class="fas fa-sync-alt text-muted"></i>
        </button>
    </div>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <div class="row g-3">
            <?php while ($row = mysqli_fetch_assoc($result)): 
                // Tentukan Warna & Label Status
                $st = $row['status'];
                $lineClass = 'line-pending';
                $badgeClass = 'bg-soft-warning';
                $labelStatus = 'Tugas Baru';

                if($st == 'dialokasikan') { $lineClass='line-pending'; $badgeClass='bg-soft-warning'; $labelStatus='Menunggu Jemput'; }
                if($st == 'dijemput') { $lineClass='line-pickup'; $badgeClass='bg-soft-info'; $labelStatus='Sedang Menjemput'; }
                if($st == 'dalam_perjalanan' || $st == 'otw') { $lineClass='line-process'; $badgeClass='bg-soft-primary'; $labelStatus='Dalam Perjalanan'; }
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="task-card">
                    <div class="status-line <?= $lineClass ?>"></div>
                    <div class="card-body ps-4"> <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge <?= $badgeClass ?> badge-status"><?= $labelStatus ?></span>
                            <small class="text-muted fw-bold">#<?= $row['id_pemesanan'] ?></small>
                        </div>

                        <div class="mb-3">
                            <div class="route-title">
                                <?= htmlspecialchars($row['asal']) ?> <i class="fas fa-arrow-right text-muted mx-2 small"></i> <?= htmlspecialchars($row['tujuan']) ?>
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="far fa-calendar me-1"></i> <?= date('d M Y', strtotime($row['tanggal_berangkat'])) ?> 
                                <span class="mx-1">•</span> 
                                <i class="far fa-clock me-1"></i> <?= date('H:i', strtotime($row['jam_berangkat'])) ?>
                            </div>
                        </div>

                        <div class="passenger-info">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($row['nama_pelanggan']) ?></span>
                                <span class="badge bg-secondary rounded-pill"><?= $row['jumlah_penumpang'] ?> Org</span>
                            </div>
                            
                            <div class="mb-2 text-muted small">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> 
                                <?= htmlspecialchars($row['alamat_jemput']) ?>
                            </div>

                            <a href="https://wa.me/<?= preg_replace('/^0/', '62', $row['hp_pelanggan']) ?>" target="_blank" class="btn btn-success btn-sm w-100 rounded-3">
                                <i class="fab fa-whatsapp me-1"></i> Chat Penumpang
                            </a>
                        </div>

                        <div class="d-grid">
                            <a href="sopir_detail_tugas.php?id=<?= $row['id_pemesanan'] ?>" class="btn btn-warning btn-action text-dark">
                                <i class="fas fa-edit me-1"></i> Update Status
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-light">
            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-25">
            <h5 class="text-dark fw-bold">Belum Ada Tugas</h5>
            <p class="text-muted small mb-0">Istirahat dulu, Pak Sopir! Belum ada penumpang yang ditugaskan.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>