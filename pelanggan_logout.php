<?php
session_start();
// Hapus semua variabel sesi Pelanggan
unset($_SESSION['pelanggan_id']);
unset($_SESSION['pelanggan_nama']);
session_destroy();
// Arahkan kembali ke halaman utama
header("Location: index.php");
exit;
?>