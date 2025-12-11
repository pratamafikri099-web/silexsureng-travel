<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php ada dan berfungsi
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Silexsureng Travel - Perjalanan Nyaman & Aman</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* ====== STYLE UMUM ====== */
        :root {
            --primary-color: #ff7b00;
            --secondary-color: #212529;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* --- NAVBAR (Supaya selalu di paling atas) --- */
        .navbar {
            background: rgba(33, 37, 41, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            z-index: 1000; /* Paling atas */
        }
        .brand-title {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
        }
        .orange { color: var(--primary-color); }

        /* --- HERO SECTION (Area Gambar Utama) --- */
        .hero-wrapper {
            position: relative;
            height: 600px; /* Tinggi Laptop */
            width: 100%;
            overflow: hidden;
            background-color: #333; /* Warna cadangan kalau gambar gagal load */
        }
        
        /* LAPISAN HITAM (Overlay) - Dibuat lebih tipis (0.5) agar gambar terang */
        .hero-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to right, rgba(0,0,0,0.5), rgba(0,0,0,0.2)); 
            z-index: 2;
        }

        .carousel-item {
            height: 600px;
            background-position: center;
            background-size: cover;
            transition: transform 1.5s ease-in-out; 
        }
        
        /* Container untuk Tulisan & Form */
        .hero-content {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 3;
            display: flex;
            align-items: center;
        }
        .hero-content .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
            width: 100%;
        }

        /* --- GLASSMORPHISM SEARCH BOX (Kotak Cari Tiket) --- */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            color: white;
        }
        .glass-card label { font-weight: 500; color: #f8f9fa; margin-bottom: 5px; }
        .glass-card .form-control, .glass-card .form-select {
            background: rgba(255, 255, 255, 0.9); border: none; border-radius: 8px; padding: 10px;
        }
        .btn-orange {
            background: var(--primary-color); border: none; color: white; padding: 12px; font-weight: 700; border-radius: 8px; transition: 0.3s; box-shadow: 0 4px 15px rgba(255, 123, 0, 0.4);
        }
        .btn-orange:hover { background: #e66e00; color: white; transform: translateY(-2px); }

        /* --- ITEM LAINNYA --- */
        .feature-box { background: white; padding: 30px 20px; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; height: 100%; }
        .feature-box:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .icon-circle { width: 70px; height: 70px; background: rgba(255, 123, 0, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: var(--primary-color); font-size: 28px; }
        .route-card { border: none; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; background: white; }
        .route-card:hover { transform: translateY(-5px); box-shadow: 0 15px 25px rgba(0,0,0,0.1); }
        .route-price { color: var(--primary-color); font-weight: 700; font-size: 1.2rem; }
        .service-section { background-color: #212529; color: white; padding: 60px 0; margin-top: 50px; }
        .footer-dark { background-color: #1a1d20; color: #adb5bd; padding-top: 50px; padding-bottom: 20px; }
        .footer-title { color: var(--primary-color); font-weight: 700; margin-bottom: 20px; }
        .footer-dark a { color: #adb5bd; text-decoration: none; transition: 0.3s; }
        .footer-dark a:hover { color: var(--primary-color); padding-left: 5px; }

        /* =========================================
           FIX TAMPILAN HP (WAJIB JALAN)
           ========================================= */
        /* =========================================
           FIX TAMPILAN HP (MOBILE RESPONSIVE) - UPDATE
           ========================================= */
        @media (max-width: 991px) {
            .hero-wrapper {
                height: auto !important; 
                min-height: 100vh; 
                padding-bottom: 50px;
                background-color: #222; /* Warna dasar hitam jika gambar belum load */
            }

            /* --- TAMBAHAN PENTING (Agar Gambar Muncul) --- */
            /* Kita paksa pembungkus carousel untuk menempel penuh ke background */
            #heroCarousel, .carousel-inner {
                position: absolute !important;
                top: 0;
                left: 0;
                width: 100%;
                height: 100% !important;
                z-index: 1;
            }

            .carousel-item {
                height: 100% !important;
                min-height: 100vh; /* Paksa setinggi layar */
                background-size: cover;
                background-position: center;
                width: 100%;
                z-index: 1;
            }
            /* --------------------------------------------- */

            .hero-content {
                position: relative !important; 
                display: block !important; 
                top: auto !important;
                left: auto !important;
                height: auto !important;
                padding-top: 180px !important; /* Jarak aman dari Navbar */
                z-index: 5; /* Konten paling atas */
            }
            
            .hero-content h1 {
                margin-top: 0px !important; 
                font-size: 2rem !important;
                text-align: center;
                line-height: 1.3;
                margin-bottom: 15px;
            }

            .hero-content p.lead {
                font-size: 1rem !important;
                text-align: center;
                padding: 0 15px;
            }

            .hero-content .d-flex {
                justify-content: center;
                margin-bottom: 30px;
            }
            
            .glass-card {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand brand-title" href="index.php">
            Sile<span class="orange">X</span>sureng <span class="orange">Travel</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="ms-auto">
                <?php if (isset($_SESSION['pelanggan_id'])): ?>
                    <span class="text-white me-3 d-none d-lg-inline">Halo, <?= htmlspecialchars($_SESSION['pelanggan_nama']) ?></span>
                    <a href="pelanggan_dashboard.php" class="btn btn-orange btn-sm me-2 rounded-pill px-4">Dashboard</a>
                    <a href="pelanggan_logout.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Logout</a>
                <?php else: ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm dropdown-toggle rounded-pill px-4" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> Masuk / Daftar
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="pelanggan_login.php">Login Pelanggan</a></li>
                            <li><a class="dropdown-item" href="pelanggan_register.php">Daftar Akun Baru</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="login_sopir.php">Login Sopir</a></li>
                            <li><a class="dropdown-item" href="login.php">Login Admin</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="hero-wrapper">
    <div class="hero-overlay"></div>
    
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('images/bg1.jpg');"></div>
            <div class="carousel-item" style="background-image: url('images/bg2.jpg');"></div>
            <div class="carousel-item" style="background-image: url('images/bg3.jpg');"></div>
        </div>
    </div>

    <div class="container hero-content">
        <div class="row align-items-center w-100 mx-0">
            <div class="col-lg-6 text-white mb-5 mb-lg-0" data-aos="fade-right"> 
                <h1 class="display-4 fw-bold mb-3">Partner Perjalanan <br><span class="orange">Terpercaya</span> Anda</h1>
                <p class="lead mb-4 text-white-50">Nikmati perjalanan antar kota dengan armada nyaman, sopir berpengalaman, dan harga terbaik. Kami siap mengantar Anda sampai tujuan.</p>
                <div class="d-flex gap-3">
                    <a href="#rute" class="btn btn-outline-light rounded-pill px-4 py-2">Lihat Rute</a>
                    <a href="https://wa.me/082237015387" class="btn btn-success rounded-pill px-4 py-2"><i class="fab fa-whatsapp me-2"></i> WhatsApp</a>
                </div>
            </div>

            <div class="col-lg-5 ms-auto" data-aos="fade-left">
                <div class="glass-card">
                    <h4 class="mb-4"><i class="fas fa-search me-2"></i> Cari Tiket Travel</h4>
                    
                    <?php if (!isset($_SESSION['pelanggan_id'])): ?>
                    <form method="get" action="pelanggan_login.php">
                        <div class="row g-2">
                            <div class="col-6 mb-2">
                                <label>Dari</label>
                                <select name="asal" class="form-select">
                                    <option value="Popalia">Popalia</option>
                                    <option value="Kolaka">Kolaka</option>
                                    <option value="Kendari">Kendari</option>
                                    <option value="Morowali">Morowali</option>
                                    <option value="Kolut">Kolut</option>
                                    <option value="Bombana">Bombana</option>
                                </select>
                            </div>
                            <div class="col-6 mb-2">
                                <label>Ke</label>
                                <select name="tujuan" class="form-select">
                                    <option value="Kendari">Kendari</option>
                                    <option value="Kolaka">Kolaka</option>
                                    <option value="Morowali">Morowali</option>
                                    <option value="Morosi">Morosi</option>
                                    <option value="Kolut">Kolut</option>
                                    <option value="Popalia">Popalia</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label>Tanggal Berangkat</label>
                                <input type="date" name="tanggal" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-orange w-100">
                                    Pesan Sekarang <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <h5 class="mb-3">Halo, <?= htmlspecialchars($_SESSION['pelanggan_nama']) ?>!</h5>
                            <p class="small text-white-50 mb-4">Akun Anda sedang aktif. Silakan masuk ke dashboard untuk memesan tiket.</p>
                            <a href="pelanggan_dashboard.php" class="btn btn-orange w-100">Ke Dashboard</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5 mt-4">
    <div class="text-center mb-5" data-aos="fade-up">
        <h6 class="text-uppercase text-muted fw-bold ls-2">Keunggulan Kami</h6>
        <h2 class="fw-bold">Mengapa Memilih Silexsureng?</h2>
    </div>
    
    <div class="row g-4">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-box">
                <div class="icon-circle"><i class="fas fa-door-open"></i></div>
                <h5>Antar Jemput</h5>
                <p class="text-muted small">Layanan door-to-door. Kami jemput di rumah dan antar sampai ke alamat tujuan.</p>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-box">
                <div class="icon-circle"><i class="fas fa-snowflake"></i></div>
                <h5>AC Dingin</h5>
                <p class="text-muted small">Armada terawat dengan AC yang sejuk menjamin kenyamanan Anda sepanjang jalan.</p>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="feature-box">
                <div class="icon-circle"><i class="fas fa-user-shield"></i></div>
                <h5>Sopir Profesional</h5>
                <p class="text-muted small">Sopir berpengalaman, ramah, dan mengutamakan keselamatan penumpang.</p>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <div class="feature-box">
                <div class="icon-circle"><i class="fas fa-tags"></i></div>
                <h5>Harga Terbaik</h5>
                <p class="text-muted small">Tarif kompetitif tanpa biaya tersembunyi. Hemat dan berkualitas.</p>
            </div>
        </div>
    </div>
</div>

<div id="rute" class="container py-5 mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-up">
        <div>
            <h6 class="text-uppercase text-warning fw-bold">Jadwal & Tarif</h6>
            <h2 class="fw-bold">Rute Populer</h2>
        </div>
        <a href="pelanggan_login.php" class="btn btn-outline-dark rounded-pill px-4 d-none d-md-block">Lihat Semua</a>
    </div>

    <div class="row g-3">
        <?php
        $sqlRute    = "SELECT id_rute, asal, tujuan, harga, jadwal_keberangkatan FROM rute ORDER BY asal, tujuan LIMIT 6";
        $resultRute = mysqli_query($koneksi, $sqlRute);

        while ($row = mysqli_fetch_assoc($resultRute)):
            $hargaText = number_format($row['harga'], 0, ',', '.');
            $jams = array_filter(array_map('trim', explode(',', $row['jadwal_keberangkatan'] ?? '')));
            $jam_awal = !empty($jams) ? substr(reset($jams), 0, 5) : '-';
        ?>
            <div class="col-md-4" data-aos="fade-up">
                <div class="card route-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light text-dark border"><i class="fas fa-clock me-1"></i> <?= $jam_awal ?> WITA</span>
                            <span class="route-price">Rp <?= $hargaText ?></span>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($row['asal']) ?></h5>
                        <div class="text-muted small mb-1"><i class="fas fa-arrow-down text-warning"></i></div>
                        <h5 class="fw-bold mb-3"><?= htmlspecialchars($row['tujuan']) ?></h5>
                        
                        <p class="small text-muted mb-3">
                            <i class="fas fa-info-circle me-1"></i> Tersedia <?= count($jams) ?> jadwal keberangkatan.
                        </p>
                        
                        <div class="d-grid">
                            <a href="<?= (isset($_SESSION['pelanggan_id']) ? "pelanggan_dashboard.php?rute_id=".$row['id_rute'] : "pelanggan_login.php") ?>" 
                               class="btn btn-orange rounded-pill">Pesan Tiket</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="service-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0" data-aos="fade-right">
                <h2 class="fw-bold mb-3">Butuh Kirim Paket Cepat?</h2>
                <p class="lead text-white-50">Selain penumpang, kami juga melayani pengiriman barang dan dokumen kilat antar kota. Sampai di hari yang sama!</p>
                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-box-open fs-3 text-warning me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">Barang</h6>
                                <small class="text-white-50">Elektronik, pakaian, dll</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-alt fs-3 text-warning me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">Dokumen</h6>
                                <small class="text-white-50">Berkas penting & surat</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center" data-aos="fade-left">
                <div class="bg-white text-dark p-4 rounded-3 d-inline-block shadow">
                    <h5 class="fw-bold mb-3">Hubungi Admin Paket</h5>
                    <a href="https://wa.me/082237015387" class="btn btn-success btn-lg w-100">
                        <i class="fab fa-whatsapp me-2"></i> Chat WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer-dark">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h3 class="brand-title mb-3">Sile<span class="orange">X</span>sureng</h3>
                <p class="small">Layanan transportasi darat terpercaya di Sulawesi Tenggara. Mengutamakan kenyamanan, keamanan, dan ketepatan waktu.</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h6 class="footer-title">Menu</h6>
                <ul class="list-unstyled small d-grid gap-2">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="pelanggan_login.php">Cek Jadwal</a></li>
                    <li><a href="login.php">Login Admin</a></li>
                    <li><a href="login_sopir.php">Login Sopir</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="footer-title">Area Layanan</h6>
                <ul class="list-unstyled small d-grid gap-2">
                    <li><i class="fas fa-map-marker-alt me-2 text-secondary"></i> Kendari</li>
                    <li><i class="fas fa-map-marker-alt me-2 text-secondary"></i> Kolaka</li>
                    <li><i class="fas fa-map-marker-alt me-2 text-secondary"></i> Bombana</li>
                    <li><i class="fas fa-map-marker-alt me-2 text-secondary"></i> Morowali</li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="footer-title">Kontak</h6>
                <ul class="list-unstyled small d-grid gap-2">
                    <li><i class="fas fa-phone me-2 text-warning"></i> 0821-5230-1265</li>
                    <li><i class="fab fa-whatsapp me-2 text-success"></i> 0822-3701-5387</li>
                    <li><i class="fas fa-envelope me-2 text-secondary"></i> admin@silexsureng.com</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <div class="text-center small text-muted">
            &copy; <?php echo date('Y'); ?> Silexsureng Travel. All rights reserved.
        </div>
    </div>
</footer>

<div class="modal fade" id="promoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal"></button>
                <img src="images/promo-travel.jpg" alt="Promo" class="img-fluid rounded-3 shadow-lg w-100">
                <div class="position-absolute bottom-0 start-0 w-100 p-3 text-center" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                    <button class="btn btn-orange rounded-pill px-4 shadow" data-bs-dismiss="modal">Pesan Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Init Animation
    AOS.init({
        duration: 800,
        once: true
    });

    // Script Popup Promo
    document.addEventListener('DOMContentLoaded', function () {
        var sudahPernah = sessionStorage.getItem('promo_silexsureng_shown');
        if (!sudahPernah) { 
            var modalEl = document.getElementById('promoModal');
            if (modalEl) {
                var myModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
                myModal.show();
                modalEl.addEventListener('hidden.bs.modal', function () {
                    sessionStorage.setItem('promo_silexsureng_shown', 'yes');
                });
            }
        }
    });
</script>

</body>
</html>