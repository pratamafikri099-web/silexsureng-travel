<?php
// Ganti detail koneksi sesuai dengan XAMPP/localhost Anda
$host = "localhost";
$user = "root"; 
$pass = ""; // Kosongkan jika di XAMPP/localhost
$db = "db_silexsureng"; // Nama database Anda

// Lakukan koneksi ke database
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek jika koneksi gagal
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>