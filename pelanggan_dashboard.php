<?php
session_start();
include 'koneksi.php';

// Cek sudah login sebagai pelanggan
if (!isset($_SESSION['pelanggan_id'])) {
    header("Location: pelanggan_login.php");
    exit;
}

$id_pelanggan   = $_SESSION['pelanggan_id'];
$nama_pelanggan = $_SESSION['pelanggan_nama'] ?? 'Pelanggan';

// 1. AMBIL DAFTAR RUTE
$rute_list = [];
$sql_rute = "SELECT id_rute, asal, tujuan, harga, jadwal_keberangkatan FROM rute ORDER BY asal, tujuan";
$res_rute = mysqli_query($koneksi, $sql_rute);
while ($row = mysqli_fetch_assoc($res_rute)) {
    $rute_list[] = $row;
}

// 2. AMBIL DAFTAR SOPIR (YANG AKTIF)
$sopir_list = [];
// Asumsi kolom status sopir bernama 'aktif' (1=aktif, 0=nonaktif) sesuai screenshot tabel sopirmu
$sql_sopir = "SELECT id_sopir, nama FROM sopir WHERE aktif = 1 ORDER BY nama ASC";
$res_sopir = mysqli_query($koneksi, $sql_sopir);
if ($res_sopir) {
    while ($row = mysqli_fetch_assoc($res_sopir)) {
        $sopir_list[] = $row;
    }
}

// Data bank
$bank_list = [
    'BCA' => 'BCA (1234567890 a/n Silexsureng Travel)',
    'BRI' => 'BRI (0987654321 a/n Silexsureng Travel)',
    'Mandiri' => 'Mandiri (1122334455 a/n Silexsureng Travel)',
];

// JSON Schedule untuk JS
$schedule_map = [];
foreach ($rute_list as $r) {
    $jams_array = array_map('trim', explode(',', $r['jadwal_keberangkatan'] ?? ''));
    $schedule_map[$r['id_rute']] = $jams_array;
}
$schedule_json = json_encode($schedule_map);

$pesan_sukses = '';
$pesan_error  = '';

// ==========================================
// PROSES FORM PEMESANAN (CREATE ORDER)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'pesan') {
    $id_rute            = (int)($_POST['id_rute'] ?? 0);
    $tanggal            = $_POST['tanggal'] ?? '';
    $jam                = $_POST['jam'] ?? ''; 
    $jumlah_penumpang   = (int)($_POST['jumlah_penumpang'] ?? 1);
    
    // Ambil ID Sopir (Jika tidak pilih/kosong, set ke NULL atau 0)
    $id_sopir_pilihan   = !empty($_POST['id_sopir']) ? (int)$_POST['id_sopir'] : NULL;
    
    $metode_bayar       = $_POST['metode_bayar'] ?? '';
    $bank_tujuan        = $_POST['bank_tujuan'] ?? ''; 

    $today = date('Y-m-d');
    if ($tanggal < $today) {
        $pesan_error = "Tanggal berangkat tidak boleh kurang dari tanggal hari ini.";
    } elseif ($id_rute <= 0 || $tanggal === '' || $jam === '' || $jumlah_penumpang <= 0 || $metode_bayar === '') {
        $pesan_error = "Semua data pemesanan dan metode pembayaran wajib diisi.";
    } elseif ($metode_bayar === 'transfer' && $bank_tujuan === '') {
        $pesan_error = "Anda memilih Transfer Bank, silakan pilih Bank Tujuan.";
    } 
    else {
        // Cek Harga Rute
        $stmt = mysqli_prepare($koneksi, "SELECT asal, tujuan, harga FROM rute WHERE id_rute = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_rute);
        mysqli_stmt_execute($stmt);
        $hasil_rute = mysqli_stmt_get_result($stmt);
        $data_rute  = mysqli_fetch_assoc($hasil_rute);
        mysqli_stmt_close($stmt);

        if (!$data_rute) {
            $pesan_error = "Rute tidak ditemukan.";
        } else {
            $total_harga      = (float)$data_rute['harga'] * $jumlah_penumpang;
            $status_pemesanan = 'pending'; // Status awal
            
            // Jika user pilih sopir, status langsung 'dialokasikan' agar muncul di dashboard sopir
            if ($id_sopir_pilihan) {
                $status_pemesanan = 'dialokasikan';
            }

            $status_pembayaran_awal = ($metode_bayar === 'transfer') ? 'Belum Bayar' : 'Menunggu Konfirmasi';

            // PERBAIKAN QUERY INSERT: Menambahkan kolom id_sopir
            $query_insert = "INSERT INTO pemesanan 
                             (id_pelanggan, id_rute, id_sopir, tanggal_berangkat, jam_berangkat, jumlah_penumpang, status, total_harga, status_pembayaran) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt2 = mysqli_prepare($koneksi, $query_insert);
            
            // Binding: i (int), i (int), i (int/null), s, s, i, s, d, s
            // id_sopir_pilihan bisa NULL, jadi kita hati-hati
            mysqli_stmt_bind_param($stmt2, "iiissisds", 
                $id_pelanggan, 
                $id_rute, 
                $id_sopir_pilihan, 
                $tanggal, 
                $jam, 
                $jumlah_penumpang, 
                $status_pemesanan, 
                $total_harga, 
                $status_pembayaran_awal
            );
            
            if (mysqli_stmt_execute($stmt2)) {
                $pesan_sukses = "Pemesanan berhasil! " . ($id_sopir_pilihan ? "Sopir telah dipilih." : "Menunggu admin menunjuk sopir.");
                echo "<meta http-equiv='refresh' content='1'>";
            } else {
                $pesan_error = "Gagal menyimpan: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt2);
        }
    }
}

// AMBIL RIWAYAT
// Join juga ke tabel sopir agar nama sopir muncul di tiket
$riwayat = [];
$sql_riw = "
    SELECT p.*, r.asal, r.tujuan, s.nama AS nama_sopir 
    FROM pemesanan p 
    JOIN rute r ON p.id_rute = r.id_rute 
    LEFT JOIN sopir s ON p.id_sopir = s.id_sopir
    WHERE p.id_pelanggan = ? 
    ORDER BY p.dibuat_pada DESC";
    
$stmt_riw = mysqli_prepare($koneksi, $sql_riw);
mysqli_stmt_bind_param($stmt_riw, "i", $id_pelanggan);
mysqli_stmt_execute($stmt_riw);
$res_riw = mysqli_stmt_get_result($stmt_riw);
while ($row = mysqli_fetch_assoc($res_riw)) {
    $riwayat[] = $row;
}

$selected_rute_id = $_GET['rute_id'] ?? null;
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Silexsureng Travel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #ff7b00;
            --primary-gradient: linear-gradient(135deg, #ff9100 0%, #ff5e00 100%);
            --dark-bg: #212529;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; color: #333; }
        .navbar-custom { background-color: var(--dark-bg); padding: 15px 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; letter-spacing: -0.5px; }
        .booking-card { background: white; border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-top: 0; }
        .booking-header { background: var(--primary-gradient); color: white; padding: 15px 20px; font-weight: 600; border-top-left-radius: 12px; border-top-right-radius: 12px; }
        .form-control, .form-select { border-radius: 8px; padding: 10px 12px; font-size: 0.95rem; border: 1px solid #dee2e6; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(255, 123, 0, 0.15); }
        .btn-modern { background: var(--primary-gradient); border: none; color: white; padding: 12px; border-radius: 8px; font-weight: 600; font-size: 1rem; transition: all 0.2s; }
        .btn-modern:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(255, 123, 0, 0.3); color: white; }
        
        .ticket-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 15px; border: 1px solid #e9ecef; border-left: 5px solid #ddd; transition: transform 0.2s; }
        .ticket-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .ticket-pending { border-left-color: #ffc107; }
        .ticket-success { border-left-color: #198754; }
        .ticket-process { border-left-color: #0dcaf0; }
        .ticket-cancel { border-left-color: #dc3545; }
        .ticket-body { padding: 20px; }
        
        .badge-soft { padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.75rem; }
        .bg-soft-warning { background: #fff8e1; color: #d39e00; }
        .bg-soft-success { background: #d1e7dd; color: #0f5132; }
        .bg-soft-info { background: #cff4fc; color: #055160; }
        .bg-soft-primary { background: #cfe2ff; color: #084298; }
        .bg-soft-danger { background: #f8d7da; color: #842029; }
        .price-tag { color: var(--primary-color); font-weight: 700; font-size: 1.2rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Sile<span style="color: var(--primary-color);">X</span>sureng</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a href="index.php" class="nav-link">Beranda</a></li>
                <li class="nav-item"><a href="pelanggan_profil.php" class="nav-link">Profil Saya</a></li>
                <li class="nav-item ms-lg-2"><a href="pelanggan_logout.php" class="btn btn-sm btn-danger rounded-pill px-4">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark">Halo, <?= htmlspecialchars($nama_pelanggan) ?>! 👋</h2>
            <p class="text-muted fs-5">Mau bepergian kemana kita hari ini?</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="booking-card">
                <div class="booking-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-ticket-alt me-2"></i> Pesan Tiket</span>
                </div>
                <div class="p-4">
                    <?php if ($pesan_sukses): ?>
                        <div class="alert alert-success py-2 small rounded-3 mb-3"><?= $pesan_sukses ?></div>
                    <?php endif; ?>
                    <?php if ($pesan_error): ?>
                        <div class="alert alert-danger py-2 small rounded-3 mb-3"><?= $pesan_error ?></div>
                    <?php endif; ?>

                    <form method="post" id="formPemesanan">
                        <input type="hidden" name="aksi" value="pesan">
                        
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold mb-1">RUTE</label>
                            <select name="id_rute" class="form-select" id="id_rute" required>
                                <option value="">Pilih Tujuan...</option>
                                <?php foreach ($rute_list as $r): ?>
                                    <option value="<?= $r['id_rute'] ?>" data-harga="<?= $r['harga'] ?>" data-jadwal="<?= htmlspecialchars($r['jadwal_keberangkatan']) ?>" <?= ($r['id_rute'] == $selected_rute_id ? 'selected' : '') ?>>
                                        <?= htmlspecialchars($r['asal'] . ' -> ' . $r['tujuan']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small text-muted fw-bold mb-1">TANGGAL</label>
                                <input type="date" name="tanggal" class="form-control" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted fw-bold mb-1">JAM</label>
                                <select name="jam" class="form-select" id="jam_berangkat_select" required>
                                    <option value="">-</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold mb-1">PILIH SOPIR (Opsional)</label>
                            <select name="id_sopir" class="form-select">
                                <option value="">-- Pilihkan Admin Saja (Acak) --</option>
                                <?php foreach ($sopir_list as $s): ?>
                                    <option value="<?= $s['id_sopir'] ?>">
                                        <?= htmlspecialchars($s['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold mb-1">PENUMPANG</label>
                            <div class="d-flex align-items-center bg-light rounded-2 p-1 border">
                                <button type="button" class="btn btn-sm text-muted" onclick="ubahPenumpang(-1)"><i class="fas fa-minus"></i></button>
                                <input type="number" name="jumlah_penumpang" class="form-control border-0 bg-transparent text-center fw-bold p-0" id="jumlah_penumpang" min="1" value="1" readonly style="width: 40px;">
                                <button type="button" class="btn btn-sm text-muted" onclick="ubahPenumpang(1)"><i class="fas fa-plus"></i></button>
                                <div class="ms-auto pe-3 text-muted small">Orang</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold mb-1">PEMBAYARAN</label>
                            <select name="metode_bayar" class="form-select" id="metode_bayar" required>
                                <option value="">Pilih Metode...</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="cash">Cash (Bayar ke Sopir)</option>
                            </select>
                        </div>

                        <div class="mb-3" id="bank_tujuan_group" style="display: none;">
                            <select name="bank_tujuan" class="form-select" id="bank_tujuan_select">
                                <option value="">-- Pilih Bank --</option>
                                <?php foreach ($bank_list as $kode => $info): ?>
                                    <option value="<?= $kode ?>"><?= htmlspecialchars($info) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                            <small class="text-muted">Total:</small>
                            <span id="total_harga_display" class="fw-bold text-dark fs-5">Rp 0</span>
                        </div>

                        <button type="submit" class="btn btn-modern w-100">
                            Pesan Sekarang <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-history me-2 text-warning"></i> Riwayat Perjalanan</h5>
            
            <?php if (empty($riwayat)): ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-light">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="60" class="mb-3 opacity-25">
                    <p class="text-muted small mb-0">Belum ada riwayat.</p>
                </div>
            <?php else: ?>
                <?php foreach ($riwayat as $row): 
                    $st_bayar = strtolower($row['status_pembayaran']);
                    $st_jalan = $row['status'];
                    
                    $borderClass = 'ticket-pending';
                    $badgeBayarClass = 'bg-soft-warning';
                    
                    if ($st_bayar == 'valid' || $st_bayar == 'lunas') {
                        $borderClass = 'ticket-success';
                        $badgeBayarClass = 'bg-soft-success';
                    } elseif ($st_bayar == 'ditolak') {
                        $borderClass = 'ticket-cancel';
                        $badgeBayarClass = 'bg-soft-danger';
                    }

                    $badgeJalanClass = 'bg-secondary';
                    $labelJalan = 'Menunggu';
                    if($st_jalan == 'dialokasikan') { $badgeJalanClass='bg-soft-info'; $labelJalan='Driver Dijalan'; }
                    if($st_jalan == 'dalam_perjalanan') { $badgeJalanClass='bg-soft-primary'; $labelJalan='OTW'; }
                    if($st_jalan == 'selesai') { $badgeJalanClass='bg-soft-success'; $labelJalan='Sampai'; }
                    if($st_jalan == 'dibatalkan') { $badgeJalanClass='bg-soft-danger'; $labelJalan='Batal'; }
                ?>
                
                <div class="ticket-card <?= $borderClass ?>">
                    <div class="ticket-body">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-2 mb-md-0">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($row['asal']) ?></h6>
                                    <i class="fas fa-arrow-right mx-2 text-muted small"></i>
                                    <h6 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($row['tujuan']) ?></h6>
                                </div>
                                <div class="text-muted" style="font-size: 0.85rem;">
                                    <?= date('d M', strtotime($row['tanggal_berangkat'])) ?> • <?= date('H:i', strtotime($row['jam_berangkat'])) ?> • #<?= $row['id_pemesanan'] ?>
                                </div>
                                <?php if(!empty($row['nama_sopir'])): ?>
                                    <div class="mt-1 text-success small"><i class="fas fa-id-card me-1"></i> Sopir: <strong><?= htmlspecialchars($row['nama_sopir']) ?></strong></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-2 mb-md-0 border-start border-light ps-md-3">
                                <div class="d-flex flex-column">
                                    <span class="price-tag">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span>
                                    <div class="d-flex gap-2 mt-1">
                                        <span class="badge-soft <?= $badgeBayarClass ?>">
                                            <?= ($st_bayar == 'belum bayar') ? 'Belum Bayar' : ucfirst($st_bayar) ?>
                                        </span>
                                        <span class="badge-soft <?= $badgeJalanClass ?>">
                                            <?= $labelJalan ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 text-md-end">
                                <?php if ($st_bayar == 'belum bayar'): ?>
                                    <a href="pelanggan_bayar.php?id=<?= $row['id_pemesanan'] ?>" class="btn btn-sm btn-warning w-100 fw-bold">Bayar</a>
                                <?php elseif ($st_bayar == 'lunas' || $st_bayar == 'valid'): ?>
                                    <a href="cetak_tiket.php?id=<?= $row['id_pemesanan'] ?>" target="_blank" class="btn btn-sm btn-outline-success w-100">E-Tiket</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const scheduleMap = <?= $schedule_json ?>;
    function ubahPenumpang(delta) {
        const input = document.getElementById('jumlah_penumpang');
        let val = parseInt(input.value) || 1;
        val += delta;
        if (val < 1) val = 1;
        input.value = val;
        input.dispatchEvent(new Event('input'));
    }
    document.addEventListener('DOMContentLoaded', function() {
        const ruteSelect = document.getElementById('id_rute');
        const jamSelect = document.getElementById('jam_berangkat_select');
        const penumpangInput = document.getElementById('jumlah_penumpang');
        const hargaDisplay = document.getElementById('total_harga_display');
        const metodeBayarSelect = document.getElementById('metode_bayar');
        const bankGroup = document.getElementById('bank_tujuan_group');
        const bankSelect = document.getElementById('bank_tujuan_select');

        function updateHarga() {
            const selectedOption = ruteSelect.options[ruteSelect.selectedIndex];
            const hargaPerKursi = parseInt(selectedOption.getAttribute('data-harga')) || 0;
            const jumlahPenumpang = parseInt(penumpangInput.value) || 0;
            const total = hargaPerKursi * jumlahPenumpang;
            const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
            hargaDisplay.textContent = formatter.format(total);
        }
        function updateJamOptions() {
            const selectedRuteId = ruteSelect.value;
            const selectedOption = ruteSelect.options[ruteSelect.selectedIndex];
            const rawSchedule = selectedOption.getAttribute('data-jadwal') || '';
            jamSelect.innerHTML = '<option value="">-</option>';
            if (selectedRuteId && rawSchedule) {
                const schedules = rawSchedule.split(',').map(item => item.trim());
                schedules.forEach(time => {
                    if (time && time.length >= 5) {
                        const option = document.createElement('option');
                        option.value = time;
                        option.textContent = time.substring(0, 5);
                        jamSelect.appendChild(option);
                    }
                });
            }
            updateHarga();
        }
        function toggleBankInput() {
            if (metodeBayarSelect.value === 'transfer') {
                bankGroup.style.display = 'block';
                bankSelect.setAttribute('required', 'required');
            } else {
                bankGroup.style.display = 'none';
                bankSelect.removeAttribute('required');
                bankSelect.value = '';
            }
        }
        ruteSelect.addEventListener('change', updateJamOptions);
        penumpangInput.addEventListener('input', updateHarga);
        metodeBayarSelect.addEventListener('change', toggleBankInput);
        updateJamOptions();
        toggleBankInput();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>