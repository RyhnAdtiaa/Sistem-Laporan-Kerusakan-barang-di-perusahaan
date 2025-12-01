<?php
// auth/cek_login.php
// Middleware untuk cek session login

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

// Function untuk cek role
function cek_role($required_role) {
    if ($_SESSION['role'] != $required_role) {
        // Redirect ke dashboard sesuai role user
        if ($_SESSION['role'] == 'admin') {
            header('Location: ' . BASE_URL . 'admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'pelapor/dashboard.php');
        }
        exit();
    }
}

// Function untuk cek multiple roles
function cek_roles($allowed_roles = []) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Redirect ke dashboard sesuai role user
        if ($_SESSION['role'] == 'admin') {
            header('Location: ' . BASE_URL . 'admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'pelapor/dashboard.php');
        }
        exit();
    }
}
?>