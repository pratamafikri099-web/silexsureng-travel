<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['sopir_id'])) {
    header("Location: login_sopir.php");
    exit;
}

$id_pemesanan = $_GET['id'];
$id_sopir     = $_SESSION['sopir_id'];

// Proses Update Status
if (isset($_POST['update_status'])) {
    $status_baru = $_POST['status'];
    
    // Update status di database
    $update = mysqli_query($koneksi, "UPDATE pemesanan SET status='$status_baru' WHERE id_pemesanan='$id_pemesanan' AND id_sopir='$id_sopir'");
    
    if ($update) {
        echo "<script>alert('Status berhasil diperbarui!'); window.location='sopir_dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal update! Pastikan status sesuai database.');</script>";
    }
}

// Ambil Data Detail
$query = "SELECT p.*, pel.nama, pel.no_hp, pel.alamat AS alamat_jemput, r.asal, r.tujuan 
          FROM pemesanan p
          JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
          JOIN rute r ON p.id_rute = r.id_rute
          WHERE p.id_pemesanan = '$id_pemesanan' AND p.id_sopir = '$id_sopir'";

$result = mysqli_query($koneksi, $query);
$data   = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Detail Tugas #<?= $id_pemesanan ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-check me-2"></i> Detail Tugas Perjalanan</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-center mb-4"><?= $data['asal'] ?> &rarr; <?= $data['tujuan'] ?></h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nama Penumpang:</strong><br><?= $data['nama'] ?></p>
                            <p><strong>No. HP / WA:</strong><br><?= $data['no_hp'] ?></p>
                            <p><strong>Jum. Penumpang:</strong><br><?= $data['jumlah_penumpang'] ?> Orang</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tgl Berangkat:</strong><br><?= $data['tanggal_berangkat'] ?></p>
                            <p><strong>Jam:</strong><br><?= $data['jam_berangkat'] ?></p>
                            <p><strong>Status Saat Ini:</strong><br><span class="badge bg-primary"><?= strtoupper($data['status']) ?></span></p>
                        </div>
                    </div>
                    
                    <hr>
                    <p><strong>Alamat Penjemputan:</strong><br><?= htmlspecialchars($data['alamat_jemput']) ?></p>
                    
                    <hr>
                    <form method="POST">
                        <label class="form-label fw-bold">Update Status Perjalanan:</label>
                        <select name="status" class="form-select mb-3">
                            <option value="dialokasikan" <?= ($data['status'] == 'dialokasikan') ? 'selected' : '' ?>>Baru (Dialokasikan)</option>
                            
                            <option value="dalam_perjalanan" <?= ($data['status'] == 'dalam_perjalanan') ? 'selected' : '' ?>>Sedang Jalan (OTW)</option>
                            
                            <option value="selesai" <?= ($data['status'] == 'selesai') ? 'selected' : '' ?>>Selesai / Sampai</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-success w-100">Simpan Status</button>
                    </form>
                    
                    <a href="sopir_dashboard.php" class="btn btn-secondary w-100 mt-2">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>