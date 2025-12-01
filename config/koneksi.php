<?php
// Start session hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/kk_project/');
}

$koneksi = new mysqli("localhost", "root", "", "laporan_kerusakan");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>