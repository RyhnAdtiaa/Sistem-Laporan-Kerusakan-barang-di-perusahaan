<?php
// logout.php
session_start();

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman login dengan path yang benar
header('Location: login.php'); // Karena logout.php dan login.php sama-sama di folder auth/
exit();
?>