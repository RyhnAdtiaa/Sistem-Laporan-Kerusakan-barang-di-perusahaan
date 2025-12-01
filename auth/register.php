<?php
// Start session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/koneksi.php'; // Tambah ../ karena file ada di folder auth/

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../pelapor/dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $departemen = trim($_POST['departemen']);
    $no_hp = trim($_POST['no_hp']);

    // Validasi
    if (empty($nama) || empty($email) || empty($password) || empty($departemen) || empty($no_hp)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek email sudah terdaftar
        $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user baru dengan role pelapor
            $stmt = $koneksi->prepare("INSERT INTO users (nama, email, password, role, departemen, no_hp) VALUES (?, ?, ?, 'pelapor', ?, ?)");
            $stmt->bind_param("sssss", $nama, $email, $hashed_password, $departemen, $no_hp);

            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Laporan Kerusakan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl shadow-2xl mb-4">
                <i class="fas fa-tools text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Buat Akun Baru</h1>
            <p class="text-slate-400">Daftar sebagai Pelapor Kerusakan</p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-8">
                <?php if ($error): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <p class="text-red-700 text-sm font-semibold"><?php echo $error; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <p class="text-green-700 text-sm font-semibold"><?php echo $success; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-user text-slate-600 mr-2"></i>Nama Lengkap
                        </label>
                        <input type="text" name="nama" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Masukkan nama lengkap">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-envelope text-slate-600 mr-2"></i>Email
                        </label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="nama@email.com">
                    </div>

                    <!-- Departemen -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-building text-slate-600 mr-2"></i>Departemen
                        </label>
                        <input type="text" name="departemen" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Contoh: IT Department">
                    </div>

                    <!-- No HP -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-phone text-slate-600 mr-2"></i>No. HP
                        </label>
                        <input type="text" name="no_hp" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="08xxxxxxxxxx">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-lock text-slate-600 mr-2"></i>Password
                        </label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Minimal 6 karakter">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-lock text-slate-600 mr-2"></i>Konfirmasi Password
                        </label>
                        <input type="password" name="confirm_password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Ulangi password">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                        <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                    </button>
                </form>
            </div>

            <!-- Login Link -->
            <div class="bg-slate-50 px-8 py-4 border-t border-gray-200">
                <p class="text-center text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="login.php" class="text-blue-600 hover:text-blue-800 font-bold hover:underline transition">
                        Login di sini
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-slate-400 text-sm">
                <i class="fas fa-shield-alt mr-2"></i>Data Anda aman dan terenkripsi
            </p>
        </div>
    </div>
</body>
</html>