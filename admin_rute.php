<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$mode = 'tambah'; 
$data_rute = []; 
$pesan = '';
$pesan_tipe = '';
$error = '';


// ===================================
// 1. PROSES AMBIL DATA UNTUK EDIT
// ===================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'edit') {
    $mode = 'edit';
    $id_rute = (int) ($_GET['id'] ?? 0);

    if ($id_rute > 0) {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM rute WHERE id_rute = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_rute);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data_rute = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$data_rute) {
            $error = "Data rute tidak ditemukan.";
            $mode = 'tambah';
        }
    }
}


// ===================================
// 2. PROSES TAMBAH / UPDATE DATA
// ===================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asal    = trim($_POST['asal'] ?? '');
    $tujuan  = trim($_POST['tujuan'] ?? '');
    // Membersihkan format rupiah (titik/koma) sebelum simpan ke DB
    $harga   = (int) str_replace(['.', ','], '', trim($_POST['harga'] ?? 0));
    $jadwal  = trim($_POST['jadwal'] ?? ''); 
    $id_rute_post = (int) ($_POST['id_rute'] ?? 0);

    if (empty($asal) || empty($tujuan) || $harga <= 0 || empty($jadwal)) {
        $error = "Semua field (Asal, Tujuan, Harga, dan Jadwal) wajib diisi dengan benar.";
    } else {
        if ($id_rute_post > 0) {
            // MODE UPDATE
            $stmt = mysqli_prepare($koneksi, "UPDATE rute SET asal = ?, tujuan = ?, harga = ?, jadwal_keberangkatan = ? WHERE id_rute = ?");
            mysqli_stmt_bind_param($stmt, "ssisi", $asal, $tujuan, $harga, $jadwal, $id_rute_post);
            $pesan = "Data rute berhasil diperbarui.";
        } else {
            // MODE TAMBAH
            $stmt = mysqli_prepare($koneksi, "INSERT INTO rute (asal, tujuan, harga, jadwal_keberangkatan) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssis", $asal, $tujuan, $harga, $jadwal);
            $pesan = "Rute baru berhasil ditambahkan.";
        }

        if (mysqli_stmt_execute($stmt)) {
            // Redirect agar form bersih (PRG Pattern)
            header("Location: admin_rute.php?status=success&msg=" . urlencode($pesan));
            exit;
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
        mysqli_stmt_close($stmt);
    }
}


// ===================================
// 3. PROSES HAPUS DATA
// ===================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id_rute = (int) ($_GET['id'] ?? 0);

    if ($id_rute > 0) {
        // Cek apakah rute ini masih terikat dengan pemesanan
        $check = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pemesanan WHERE id_rute = $id_rute");
        $count = mysqli_fetch_assoc($check)['total'];

        if ($count > 0) {
            $error = "Gagal Hapus! Rute ini sedang digunakan dalam $count data pemesanan.";
        } else {
            $stmt = mysqli_prepare($koneksi, "DELETE FROM rute WHERE id_rute = ?");
            mysqli_stmt_bind_param($stmt, "i", $id_rute);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("Location: admin_rute.php?status=success&msg=" . urlencode("Rute berhasil dihapus."));
            exit;
        }
    }
}

// Ambil data rute
$sql_list = "SELECT id_rute, asal, tujuan, harga, jadwal_keberangkatan FROM rute ORDER BY asal ASC, tujuan ASC";
$result_list = mysqli_query($koneksi, $sql_list);

// Notifikasi
if(isset($_GET['status']) && isset($_GET['msg'])) {
    $pesan = htmlspecialchars($_GET['msg']);
    $pesan_tipe = htmlspecialchars($_GET['status']);
}

// Nilai form default
$asal_form   = $data_rute['asal'] ?? ($_POST['asal'] ?? '');
$tujuan_form = $data_rute['tujuan'] ?? ($_POST['tujuan'] ?? '');
$harga_form  = $data_rute['harga'] ?? ($_POST['harga'] ?? '');
$jadwal_form = $data_rute['jadwal_keberangkatan'] ?? ($_POST['jadwal'] ?? '');

// Format harga untuk input field (tambah titik ribuan saat edit)
$harga_input_val = '';
if ($mode === 'edit' && $harga_form > 0) {
    $harga_input_val = number_format($harga_form, 0, ',', '.');
} elseif (isset($_POST['harga'])) {
    $harga_input_val = htmlspecialchars($_POST['harga']);
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Kelola Rute | Silexsureng Travel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .badge-time {
            background-color: #e7f1ff; color: #0d6efd; 
            font-weight: 500; padding: 5px 10px; border-radius: 6px;
            margin-right: 5px; display: inline-block; margin-bottom: 3px;
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
                <li class="nav-item"><a class="nav-link active" href="admin_rute.php">Rute</a></li>
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
            <h4 class="mb-0 fw-bold">Kelola Rute Perjalanan</h4>
            <p class="text-muted small mb-0">Atur rute, harga tiket, dan jadwal keberangkatan.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-modern mb-4 sticky-top" style="top: 80px; z-index: 1;">
                <div class="card-header bg-dark text-white border-0 py-3">
                    <i class="fas fa-map-marked-alt me-1"></i> 
                    <?= ($mode === 'edit') ? 'Edit Rute' : 'Tambah Rute Baru' ?>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger small p-2"><i class="fas fa-exclamation-triangle me-1"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <?php if ($mode === 'edit'): ?>
                            <input type="hidden" name="id_rute" value="<?= $data_rute['id_rute'] ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Kota Asal</label>
                                <input type="text" name="asal" class="form-control" placeholder="Dari..." value="<?= htmlspecialchars($asal_form) ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Kota Tujuan</label>
                                <input type="text" name="tujuan" class="form-control" placeholder="Ke..." value="<?= htmlspecialchars($tujuan_form) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Harga Tiket (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-bold">Rp</span>
                                <input type="text" name="harga" id="inputHarga" class="form-control border-start-0" 
                                       placeholder="0" value="<?= $harga_input_val ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Jadwal (Jam:Menit)</label>
                            <input type="text" name="jadwal" class="form-control" 
                                   placeholder="Contoh: 08:00, 10:00, 15:30"
                                   value="<?= htmlspecialchars($jadwal_form) ?>" required>
                            <div class="form-text small">Pisahkan dengan koma (,) untuk banyak jadwal.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning text-dark fw-bold">
                                <i class="fas fa-save me-1"></i> <?= ($mode === 'edit') ? 'Simpan Perubahan' : 'Tambah Rute' ?>
                            </button>
                            <?php if ($mode === 'edit'): ?>
                                <a href="admin_rute.php" class="btn btn-outline-secondary btn-sm">Batal Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-modern">
                <div class="card-body p-4">
                    <?php if ($pesan): ?>
                        <div class="alert alert-<?= $pesan_tipe ?> small py-2"><i class="fas fa-check-circle me-1"></i> <?= $pesan ?></div>
                    <?php endif; ?>

                    <h5 class="mb-3 text-secondary">Daftar Rute Tersedia (<?= mysqli_num_rows($result_list) ?>)</h5>
                    
                    <?php if ($result_list && mysqli_num_rows($result_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Rute Perjalanan</th>
                                        <th>Harga</th>
                                        <th>Jadwal Keberangkatan</th>
                                        <th class="text-end pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_list)): 
                                    // Pecah jadwal jadi array untuk ditampilkan rapi
                                    $jams = explode(',', $row['jadwal_keberangkatan']);
                                ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['asal']) ?></div>
                                            <div class="small text-muted"><i class="fas fa-arrow-down text-warning"></i></div>
                                            <div class="fw-bold text-primary"><?= htmlspecialchars($row['tujuan']) ?></div>
                                        </td>
                                        <td class="fw-bold text-success fs-6">
                                            Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                        </td>
                                        <td style="max-width: 250px;">
                                            <?php foreach($jams as $jam): ?>
                                                <span class="badge-time">
                                                    <i class="far fa-clock me-1"></i> <?= trim($jam) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="admin_rute.php?aksi=edit&id=<?= $row['id_rute'] ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="admin_rute.php?aksi=hapus&id=<?= $row['id_rute'] ?>" 
                                               class="btn btn-sm btn-outline-danger rounded-circle ms-1"
                                               onclick="return confirm('Hapus rute <?= $row['asal'] ?> - <?= $row['tujuan'] ?>?');" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="60" class="mb-3 opacity-25">
                            <p class="text-muted mb-0">Belum ada rute perjalanan yang dibuat.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Format Rupiah Otomatis saat mengetik
    const inputHarga = document.getElementById('inputHarga');
    if(inputHarga){
        inputHarga.addEventListener('keyup', function(e){
            inputHarga.value = formatRupiah(this.value);
        });
    }

    function formatRupiah(angka, prefix){
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split   = number_string.split(','),
        sisa    = split[0].length % 3,
        rupiah  = split[0].substr(0, sisa),
        ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }
</script>
</body>
</html>