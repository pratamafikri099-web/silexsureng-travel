<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['pelanggan_id'])) {
    header("Location: pelanggan_login.php");
    exit;
}

$id_pemesanan = $_GET['id'] ?? 0;
$id_pelanggan = $_SESSION['pelanggan_id'];

// ==========================================
// BAGIAN PERBAIKAN QUERY (Line 21)
// ==========================================
// Kita ubah 'pel.telepon' menjadi 'pel.no_hp AS telepon'
// sesuai dengan nama kolom di database kamu (no_hp)
$query = "
    SELECT p.*, r.asal, r.tujuan, r.harga, pel.nama AS nama_pemesan, pel.no_hp AS telepon
    FROM pemesanan p
    JOIN rute r ON p.id_rute = r.id_rute
    JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    WHERE p.id_pemesanan = '$id_pemesanan' AND p.id_pelanggan = '$id_pelanggan'
";
$result = mysqli_query($koneksi, $query);

// Cek jika query gagal (untuk debugging)
if (!$result) {
    die("Error Database: " . mysqli_error($koneksi));
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Tiket tidak ditemukan.";
    exit;
}

// Hanya bisa cetak jika status VALID atau LUNAS
// Kita gunakan strtolower agar tidak error karena perbedaan huruf besar/kecil
$status_bayar = strtolower($data['status'] ?? $data['status_pembayaran'] ?? ''); 

// Daftar status yang diperbolehkan cetak tiket
$status_boleh = ['valid', 'lunas', 'dialokasikan', 'menunggu']; 
// Catatan: Saya tambahkan 'menunggu' sementara agar kamu bisa tes tampilan tiketnya, 
// nanti bisa dihapus jika ingin ketat.

if (!in_array($status_bayar, $status_boleh)) {
    echo "<h3>Tiket belum dapat dicetak.</h3>";
    echo "<p>Status Pembayaran saat ini: <strong>" . strtoupper($status_bayar) . "</strong></p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Tiket Silexsureng Travel #<?= $id_pemesanan ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; color: #333; }
        .ticket-wrapper {
            max-width: 700px; margin: 30px auto;
            background: #fff; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 2px dashed #e0e0e0;
        }
        .ticket-header { background: #ff7b00; color: #fff; padding: 20px; text-align: center; }
        .ticket-body { padding: 30px; }
        .ticket-footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 0.9em; color: #666; border-top: 1px solid #eee; }
        .route-line { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; font-weight: bold; font-size: 1.2em; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .status-stamp {
            border: 2px solid #198754; color: #198754; 
            padding: 5px 15px; border-radius: 50px; 
            font-weight: bold; text-transform: uppercase;
            display: inline-block;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .ticket-wrapper { box-shadow: none; border: 2px solid #000; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center mt-3 mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="fas fa-print"></i> Cetak Tiket / Simpan PDF</button>
        <a href="pelanggan_dashboard.php" class="btn btn-secondary btn-lg">Kembali</a>
    </div>

    <div class="ticket-wrapper">
        <div class="ticket-header">
            <h3 class="mb-0">E-TIKET SILEXSURENG TRAVEL</h3>
            <small>Kode Booking: #<?= str_pad($data['id_pemesanan'], 6, '0', STR_PAD_LEFT) ?></small>
        </div>
        
        <div class="ticket-body">
            <div class="text-center mb-4">
                <span class="status-stamp">LUNAS / VALID</span>
            </div>

            <div class="route-line text-primary">
                <span><?= strtoupper($data['asal']) ?></span>
                <span style="color:#ccc">────── <i class="fas fa-bus"></i> ──────</span>
                <span><?= strtoupper($data['tujuan']) ?></span>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <small class="text-muted d-block">Tanggal Berangkat</small>
                        <strong><?= date('d F Y', strtotime($data['tanggal_berangkat'])) ?></strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <small class="text-muted d-block">Jam Berangkat</small>
                        <strong><?= isset($data['jam_berangkat']) ? date('H:i', strtotime($data['jam_berangkat'])) : '-' ?> WITA</strong>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <div class="row">
                    <div class="col-6 mb-2">
                        <small class="text-muted d-block">Nama Pemesan</small>
                        <span><?= htmlspecialchars($data['nama_pemesan']) ?></span>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted d-block">No. Telepon</small>
                        <span><?= htmlspecialchars($data['telepon']) ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Jumlah Penumpang</small>
                        <span><?= $data['jumlah_penumpang'] ?> Orang</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Total Bayar</small>
                        <strong class="text-danger">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></strong>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning small mb-0">
                <i class="fas fa-info-circle"></i> <strong>Penting:</strong> Harap tunjukkan tiket ini kepada sopir saat penjemputan. Mohon bersiap 30 menit sebelum jam keberangkatan.
            </div>
        </div>

        <div class="ticket-footer">
            &copy; <?= date('Y') ?> Silexsureng Travel. Layanan Pelanggan: 0822-3701-5387
        </div>
    </div>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>