<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
cek_role('pelapor');

// Get user ID
$id_user = $_SESSION['user_id'];

// Get statistics
$total_laporan = $koneksi->query("SELECT COUNT(*) as total FROM laporan_kerusakan WHERE id_user = $id_user")->fetch_assoc()['total'];
$belum_diperbaiki = $koneksi->query("SELECT COUNT(*) as total FROM laporan_kerusakan WHERE id_user = $id_user AND status = 'belum diperbaiki'")->fetch_assoc()['total'];
$sedang_diperbaiki = $koneksi->query("SELECT COUNT(*) as total FROM laporan_kerusakan WHERE id_user = $id_user AND status = 'sedang diperbaiki'")->fetch_assoc()['total'];
$selesai = $koneksi->query("SELECT COUNT(*) as total FROM laporan_kerusakan WHERE id_user = $id_user AND status = 'selesai'")->fetch_assoc()['total'];

// Get recent reports
$recent_query = "SELECT lk.*, p.nama_peralatan, p.lokasi 
                 FROM laporan_kerusakan lk 
                 JOIN peralatan p ON lk.id_peralatan = p.id_peralatan 
                 WHERE lk.id_user = $id_user 
                 ORDER BY lk.tanggal_lapor DESC 
                 LIMIT 5";
$recent_reports = $koneksi->query($recent_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelapor - Sistem Laporan Kerusakan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Dashboard Pelapor</h1>
                    <p class="text-gray-600 mt-1">Selamat datang, <span class="font-semibold text-blue-600"><?php echo $_SESSION['nama']; ?></span></p>
                </div>
                <div class="flex gap-3">
                    <a href="laporan/buat.php" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i>
                        Buat Laporan Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Laporan -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Total Laporan</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_laporan; ?></h3>
                    </div>
                    <div class="bg-gradient-to-br from-blue-100 to-blue-200 p-4 rounded-xl">
                        <i class="fas fa-clipboard-list text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Belum Diperbaiki -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Belum Diperbaiki</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $belum_diperbaiki; ?></h3>
                    </div>
                    <div class="bg-gradient-to-br from-red-100 to-red-200 p-4 rounded-xl">
                        <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Sedang Diperbaiki -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Sedang Diperbaiki</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $sedang_diperbaiki; ?></h3>
                    </div>
                    <div class="bg-gradient-to-br from-orange-100 to-orange-200 p-4 rounded-xl">
                        <i class="fas fa-tools text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Selesai -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Selesai</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $selesai; ?></h3>
                    </div>
                    <div class="bg-gradient-to-br from-green-100 to-green-200 p-4 rounded-xl">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-blue-600"></i>
                Aksi Cepat
            </h2>
            <div class="grid grid-cols-1 gap-4">
                <a href="laporan/data.php" class="group bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white p-6 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-lg mb-1">Lihat Semua Laporan</h3>
                            <p class="text-sm text-slate-300">Cek status laporan Anda</p>
                        </div>
                        <i class="fas fa-list text-3xl group-hover:translate-x-2 transition-transform duration-300"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-history"></i>
                    Laporan Terbaru Anda
                </h2>
            </div>
            
            <div class="p-6">
                <?php if ($recent_reports->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($report = $recent_reports->fetch_assoc()): 
                            // Status badge styling
                            $statusClass = '';
                            $statusIcon = '';
                            switch ($report['status']) {
                                case 'belum diperbaiki':
                                    $statusClass = 'bg-red-100 text-red-800';
                                    $statusIcon = 'fa-exclamation-circle';
                                    break;
                                case 'sedang diperbaiki':
                                    $statusClass = 'bg-orange-100 text-orange-800';
                                    $statusIcon = 'fa-tools';
                                    break;
                                case 'selesai':
                                    $statusClass = 'bg-green-100 text-green-800';
                                    $statusIcon = 'fa-check-circle';
                                    break;
                            }
                        ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-bold text-gray-800"><?php echo $report['nama_peralatan']; ?></h3>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?php echo $statusClass; ?>">
                                            <i class="fas <?php echo $statusIcon; ?>"></i>
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2"><?php echo $report['deskripsi']; ?></p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                        <span><i class="fas fa-map-marker-alt mr-1"></i><?php echo $report['lokasi']; ?></span>
                                        <span><i class="fas fa-calendar mr-1"></i><?php echo date('d M Y', strtotime($report['tanggal_lapor'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <a href="laporan/data.php" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                            Lihat Semua Laporan
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                            <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum Ada Laporan</h3>
                        <p class="text-gray-600">Anda belum membuat laporan kerusakan. Klik tombol "Buat Laporan Baru" di atas untuk memulai.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>