<?php
session_start();
include 'koneksi.php';

// Cek keamanan
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// 1. DATA RINGKASAN (KARTU ATAS)
// ==========================================

// Total Pendapatan (Hanya yang status bayarnya Lunas/Valid)
$q_omzet = mysqli_query($koneksi, "SELECT SUM(total_harga) AS total FROM pemesanan WHERE status_pembayaran = 'Lunas' OR status_pembayaran = 'Valid'");
$d_omzet = mysqli_fetch_assoc($q_omzet);
$total_omzet = $d_omzet['total'] ?? 0;

// Total Pesanan Masuk
$q_order = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pemesanan");
$total_order = mysqli_fetch_assoc($q_order)['total'];

// Total Pelanggan
$q_user = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pelanggan");
$total_user = mysqli_fetch_assoc($q_user)['total'];

// Total Sopir Aktif
$q_sopir = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM sopir WHERE aktif = 1");
$total_sopir = mysqli_fetch_assoc($q_sopir)['total'];


// ==========================================
// 2. DATA UNTUK GRAFIK (CHART.JS)
// ==========================================

// A. Grafik Pendapatan per Bulan (6 Bulan Terakhir)
$bulan_labels = [];
$pendapatan_data = [];

for ($i = 5; $i >= 0; $i--) {
    $bulan_lalu = date('Y-m', strtotime("-$i months")); 
    $nama_bulan = date('M Y', strtotime("-$i months")); 
    
    // Query sum per bulan
    $q_chart1 = mysqli_query($koneksi, "SELECT SUM(total_harga) AS total FROM pemesanan 
                                        WHERE DATE_FORMAT(dibuat_pada, '%Y-%m') = '$bulan_lalu' 
                                        AND (status_pembayaran = 'Lunas' OR status_pembayaran = 'Valid')");
    $d_chart1 = mysqli_fetch_assoc($q_chart1);
    
    $bulan_labels[] = $nama_bulan;
    $pendapatan_data[] = $d_chart1['total'] ?? 0;
}

// B. Grafik Rute Terlaris
$rute_labels = [];
$rute_data = [];

$q_chart2 = mysqli_query($koneksi, "SELECT r.asal, r.tujuan, COUNT(p.id_pemesanan) as jumlah 
                                    FROM pemesanan p
                                    JOIN rute r ON p.id_rute = r.id_rute
                                    GROUP BY p.id_rute
                                    ORDER BY jumlah DESC LIMIT 5");

while($row = mysqli_fetch_assoc($q_chart2)) {
    $rute_labels[] = $row['asal'] . " - " . $row['tujuan'];
    $rute_data[] = $row['jumlah'];
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - Statistik</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #ff7b00;
            --dark-bg: #212529;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; color: #333; }

        /* Navbar Konsisten */
        .navbar-custom { background-color: var(--dark-bg); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .nav-link { color: rgba(255,255,255,0.7) !important; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: var(--primary-color) !important; }
        .nav-link.active { font-weight: 600; border-bottom: 2px solid var(--primary-color); }

        /* Card Statistik */
        .stat-card {
            border: none; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            background: white;
            overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-5px); }
        
        .icon-box {
            width: 50px; height: 50px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .bg-icon-orange { background: rgba(255, 123, 0, 0.1); color: #ff7b00; }
        .bg-icon-green { background: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-icon-blue { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .bg-icon-info { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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
                <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_pemesanan.php">Pemesanan</a></li>
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

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Dashboard Statistik</h4>
            <p class="text-muted small mb-0">Ringkasan performa bisnis travel Anda.</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-print me-1"></i> Cetak Laporan
        </button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-icon-green me-3"><i class="fas fa-money-bill-wave"></i></div>
                    <div>
                        <small class="text-muted d-block">Total Omzet</small>
                        <h5 class="mb-0 fw-bold">Rp <?= number_format($total_omzet, 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-icon-orange me-3"><i class="fas fa-shopping-cart"></i></div>
                    <div>
                        <small class="text-muted d-block">Total Pesanan</small>
                        <h5 class="mb-0 fw-bold"><?= $total_order ?> Transaksi</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-icon-blue me-3"><i class="fas fa-users"></i></div>
                    <div>
                        <small class="text-muted d-block">Pelanggan</small>
                        <h5 class="mb-0 fw-bold"><?= $total_user ?> Orang</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-icon-info me-3"><i class="fas fa-steering-wheel"></i></div>
                    <div>
                        <small class="text-muted d-block">Sopir Aktif</small>
                        <h5 class="mb-0 fw-bold"><?= $total_sopir ?> Orang</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card stat-card h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Grafik Pendapatan (6 Bulan Terakhir)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartPendapatan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card stat-card h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-warning"></i>Rute Terpopuler</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 280px;">
                        <canvas id="chartRute"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. DATA DARI PHP KE JS
    const labelBulan = <?= json_encode($bulan_labels) ?>;
    const dataPendapatan = <?= json_encode($pendapatan_data) ?>;
    
    const labelRute = <?= json_encode($rute_labels) ?>;
    const dataRute = <?= json_encode($rute_data) ?>;

    // 2. KONFIGURASI CHART PENDAPATAN (LINE)
    const ctx1 = document.getElementById('chartPendapatan').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: labelBulan,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataPendapatan,
                borderColor: '#ff7b00',
                backgroundColor: 'rgba(255, 123, 0, 0.1)',
                borderWidth: 3,
                tension: 0.4, 
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 3. KONFIGURASI CHART RUTE (DOUGHNUT)
    const ctx2 = document.getElementById('chartRute').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: labelRute,
            datasets: [{
                data: dataRute,
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
</script>

</body>
</html>