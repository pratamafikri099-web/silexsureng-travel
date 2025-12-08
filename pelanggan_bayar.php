<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['pelanggan_id'])) {
    header("Location: pelanggan_login.php");
    exit;
}

// Ambil ID Pemesanan dari URL
$id_pemesanan = $_GET['id'];

// Ambil data pemesanan agar user tahu apa yang dibayar
$query = "SELECT p.*, r.asal, r.tujuan, r.harga 
          FROM pemesanan p 
          JOIN rute r ON p.id_rute = r.id_rute 
          WHERE p.id_pemesanan = '$id_pemesanan' AND p.id_pelanggan = '$_SESSION[pelanggan_id]'";
$result = mysqli_query($koneksi, $query);
$data   = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan (misal user iseng ganti ID di URL)
if (!$data) {
    echo "<script>alert('Data pesanan tidak ditemukan!'); window.location='pelanggan_dashboard.php';</script>";
    exit;
}

// PROSES UPLOAD BUKTI
if (isset($_POST['kirim_bukti'])) {
    $nama_file   = $_FILES['bukti']['name'];
    $lokasi_file = $_FILES['bukti']['tmp_name'];
    $tipe_file   = $_FILES['bukti']['type'];
    
    // Generate nama unik agar tidak bentrok
    $nama_baru = date('YmdHis') . '_' . $nama_file;
    $folder    = "uploads/" . $nama_baru;

    // Validasi sederhana (harus gambar)
    $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
    $x = explode('.', $nama_file);
    $ekstensi = strtolower(end($x));

    if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
        // Upload file
        if (move_uploaded_file($lokasi_file, $folder)) {
            // Update Database
            $update = mysqli_query($koneksi, "UPDATE pemesanan SET 
                bukti_pembayaran = '$nama_baru', 
                status_pembayaran = 'Menunggu Konfirmasi' 
                WHERE id_pemesanan = '$id_pemesanan'");

            if ($update) {
                echo "<script>alert('Bukti pembayaran berhasil dikirim! Tunggu konfirmasi admin.'); window.location='pelanggan_dashboard.php';</script>";
            } else {
                echo "<script>alert('Gagal update database!');</script>";
            }
        } else {
            echo "<script>alert('Gagal upload gambar!');</script>";
        }
    } else {
        echo "<script>alert('Ekstensi file tidak diperbolehkan! Harap upload JPG atau PNG.');</script>";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pembayaran - Silexsureng Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-bayar { max-width: 600px; margin: 50px auto; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .bg-orange { background-color: #ff7b00; color: white; }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <div class="card card-bayar">
        <div class="card-header bg-orange text-center py-3">
            <h4 class="mb-0">Konfirmasi Pembayaran</h4>
        </div>
        <div class="card-body p-4">
            
            <div class="alert alert-info">
                Silakan transfer ke rekening di bawah ini, lalu upload bukti transfernya.
            </div>

            <div class="text-center mb-4">
                <h5>Bank BRI</h5>
                <h3 class="fw-bold text-primary">1234-5678-9000</h3>
                <p>a.n. Silexsureng Travel</p>
                <hr>
                <h5 class="text-muted">Total Tagihan:</h5>
                <h2 class="fw-bold text-danger">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></h2>
                <p class="small text-muted">Rute: <?= $data['asal'] ?> - <?= $data['tujuan'] ?></p>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Bukti Transfer (Foto/Screenshot)</label>
                    <input type="file" name="bukti" class="form-control" required accept=".jpg, .jpeg, .png">
                    <div class="form-text">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="kirim_bukti" class="btn btn-success btn-lg">Kirim Bukti Pembayaran</button>
                    <a href="pelanggan_dashboard.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>