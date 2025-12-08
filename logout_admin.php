<?php
session_start();
// Hapus semua variabel sesi Admin
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
session_destroy();
// Arahkan kembali ke halaman login Admin
header("Location: login.php");
exit;
?>