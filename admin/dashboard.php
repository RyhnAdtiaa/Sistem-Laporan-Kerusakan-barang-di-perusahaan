<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
cek_role('admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Laporan Kerusakan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem;
            position: relative;
            overflow-y: auto;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.1) 0%, transparent 50%);
            opacity: 0.5;
        }

        .container-box {
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            text-align: center;
            max-width: 650px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            border-radius: 24px 24px 0 0;
        }

        .btn-module {
            transition: all 0.3s ease;
        }

        .btn-module:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container-box">
        <!-- User Info Badge -->
        <div class="user-badge">
            <i class="fas fa-user-shield"></i>
            <span><?php echo $_SESSION['nama']; ?></span>
        </div>

        <!-- Logo/Icon -->
        <div class="mb-6">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-slate-700 to-slate-900 rounded-full shadow-xl mb-4">
                <i class="fas fa-tools text-white text-4xl"></i>
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-4xl font-bold text-gray-800 mb-3">Dashboard Admin</h1>
        <p class="text-gray-600 mb-8 text-lg">Sistem Manajemen Laporan Kerusakan Peralatan</p>

        <!-- Module Buttons -->
        <div class="flex flex-col gap-4">
            <!-- Kelola Peralatan -->
            <a href="peralatan/data.php" class="btn-module group bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white px-8 py-4 rounded-xl font-semibold shadow-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-screwdriver-wrench text-2xl"></i>
                    </div>
                    <span class="text-lg">Kelola Peralatan</span>
                </div>
                <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
            </a>

            <!-- Kelola Laporan -->
            <a href="laporan/data.php" class="btn-module group bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white px-8 py-4 rounded-xl font-semibold shadow-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-2xl"></i>
                    </div>
                    <span class="text-lg">Kelola Laporan</span>
                </div>
                <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
            </a>

            <!-- Kelola Users -->
            <a href="users/data.php" class="btn-module group bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white px-8 py-4 rounded-xl font-semibold shadow-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <span class="text-lg">Kelola Users</span>
                </div>
                <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
            </a>

            <!-- Logout -->
            <a href="<?php echo BASE_URL; ?>auth/logout.php" onclick="return confirm('Yakin ingin keluar?')" class="btn-module group bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-8 py-4 rounded-xl font-semibold shadow-lg flex items-center justify-between mt-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sign-out-alt text-2xl"></i>
                    </div>
                    <span class="text-lg">Logout</span>
                </div>
                <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
            </a>
        </div>

        <!-- Footer Info -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500">
                <i class="fas fa-building mr-2"></i><?php echo $_SESSION['departemen']; ?>
            </p>
            <p class="text-xs text-gray-400 mt-2">&copy; 2025 Sistem Laporan Kerusakan</p>
        </div>
    </div>
</body>
</html>