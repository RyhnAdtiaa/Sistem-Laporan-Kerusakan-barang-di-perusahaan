<?php
include '../../config/koneksi.php';
include '../../auth/cek_login.php';
cek_role('admin');

// ----------------------
// HANDLE ACTIONS
// ----------------------

// Update Status Laporan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id_laporan = intval($_POST['id_laporan'] ?? 0);
    $status = $_POST['status'] ?? '';

    $valid_status = ['belum diperbaiki', 'sedang diperbaiki', 'selesai'];
    if ($id_laporan > 0 && in_array($status, $valid_status)) {
        $stmt = $koneksi->prepare("UPDATE laporan_kerusakan SET status = ? WHERE id_laporan = ?");
        $stmt->bind_param("si", $status, $id_laporan);
        if ($stmt->execute()) {
            echo "<script>alert('Status laporan berhasil diperbarui!'); window.location.href='data.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui status!'); window.location.href='data.php';</script>";
        }
        $stmt->close();
    }
    exit;
}

// Edit Deskripsi Laporan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_laporan = intval($_POST['id_laporan'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($id_laporan > 0 && !empty($deskripsi)) {
        $stmt = $koneksi->prepare("UPDATE laporan_kerusakan SET deskripsi = ? WHERE id_laporan = ?");
        $stmt->bind_param("si", $deskripsi, $id_laporan);
        if ($stmt->execute()) {
            echo "<script>alert('Deskripsi laporan berhasil diperbarui!'); window.location.href='data.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui deskripsi!'); window.location.href='data.php';</script>";
        }
        $stmt->close();
    }
    exit;
}

// Hapus Laporan
if (isset($_GET['hapus'])) {
    $id_laporan = intval($_GET['hapus']);
    if ($id_laporan > 0) {
        $stmt = $koneksi->prepare("DELETE FROM laporan_kerusakan WHERE id_laporan = ?");
        $stmt->bind_param("i", $id_laporan);
        if ($stmt->execute()) {
            echo "<script>alert('Laporan berhasil dihapus!'); window.location.href='data.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus laporan!'); window.location.href='data.php';</script>";
        }
        $stmt->close();
    }
    exit;
}

// ----------------------
// FETCH DATA - PERBAIKAN STATISTIK
// ----------------------

// Inisialisasi statistik dengan nilai default 0
$stats = [
    'total' => 0,
    'belum diperbaiki' => 0,
    'sedang diperbaiki' => 0,
    'selesai' => 0
];

// Query untuk menghitung statistik per status
$result = $koneksi->query("SELECT status, COUNT(*) as jumlah FROM laporan_kerusakan GROUP BY status");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Pastikan key status sesuai dengan yang ada di database
        $status_key = strtolower(trim($row['status'])); // Normalisasi status
        
        if (isset($stats[$status_key])) {
            $stats[$status_key] = (int)$row['jumlah'];
        }
    }
    
    // Hitung total dari semua status
    $stats['total'] = $stats['belum diperbaiki'] + $stats['sedang diperbaiki'] + $stats['selesai'];
}

// Ambil semua laporan dengan JOIN
$query = "
    SELECT 
        lk.id_laporan,
        lk.deskripsi,
        lk.tanggal_lapor,
        lk.status,
        u.nama as nama_pelapor,
        u.departemen,
        u.no_hp,
        p.nama_peralatan,
        p.jenis,
        p.lokasi
    FROM laporan_kerusakan lk
    INNER JOIN users u ON lk.id_user = u.id
    INNER JOIN peralatan p ON lk.id_peralatan = p.id_peralatan
    ORDER BY 
        CASE 
            WHEN lk.status = 'belum diperbaiki' THEN 1
            WHEN lk.status = 'sedang diperbaiki' THEN 2
            WHEN lk.status = 'selesai' THEN 3
        END,
        lk.tanggal_lapor DESC
";
$result = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Laporan Kerusakan - Sistem Manajemen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }
        .modal.active {
            display: flex;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            animation: slideUp 0.3s ease-in-out;
        }
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .status-badge {
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">

<?php include '../../includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="ml-64 p-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-12 h-12 bg-gradient-to-br from-slate-700 to-slate-900 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-clipboard-list text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Data Laporan Kerusakan</h1>
                <p class="text-gray-600 mt-1">Kelola dan pantau semua laporan kerusakan peralatan</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards - DIPERBAIKI -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Laporan -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-slate-700 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase">Total Laporan</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-2"><?php echo $stats['total']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-slate-100 to-slate-200 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-list text-slate-700 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Belum Diperbaiki -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase">Belum Diperbaiki</p>
                    <h3 class="text-3xl font-bold text-red-600 mt-2"><?php echo $stats['belum diperbaiki']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-red-100 to-red-200 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Sedang Diperbaiki -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase">Sedang Diperbaiki</p>
                    <h3 class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $stats['sedang diperbaiki']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-tools text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase">Selesai</p>
                    <h3 class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['selesai']; ?></h3>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white shadow-md rounded-xl p-6 mb-6 border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <!-- Search Box -->
            <div class="relative flex-1">
                <input type="text" id="searchInput" placeholder="Cari berdasarkan peralatan, pelapor, atau deskripsi..."
                    class="pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent w-full transition">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
            </div>

            <!-- Filter Status -->
            <div class="flex items-center gap-3">
                <select id="filterStatus" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition bg-white">
                    <option value="">Semua Status</option>
                    <option value="belum diperbaiki">Belum Diperbaiki</option>
                    <option value="sedang diperbaiki">Sedang Diperbaiki</option>
                    <option value="selesai">Selesai</option>
                </select>

                <!-- Total Badge -->
                <div class="px-4 py-2.5 bg-slate-100 rounded-lg whitespace-nowrap">
                    <span class="text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-list mr-2"></i><span id="totalCount"><?php echo $stats['total']; ?></span> Laporan
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards Grid - LAYOUT DIPERBAIKI -->
    <div id="cardsGrid" class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6">
        <?php if ($stats['total'] === 0): ?>
            <div class="col-span-full bg-white rounded-xl shadow-md p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                    <i class="fa-solid fa-clipboard-list text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum Ada Laporan</h3>
                <p class="text-gray-600">Belum ada laporan kerusakan yang masuk ke sistem</p>
            </div>
        <?php else: ?>
            <?php while ($laporan = $result->fetch_assoc()): 
                // Status badge styling
                $status_config = [
                    'belum diperbaiki' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fa-exclamation-triangle', 'label' => 'Belum Diperbaiki'],
                    'sedang diperbaiki' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-tools', 'label' => 'Sedang Diperbaiki'],
                    'selesai' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'fa-check-circle', 'label' => 'Selesai']
                ];
                $status = $status_config[$laporan['status']];
            ?>
                <div class="laporan-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 transform hover:-translate-y-1 flex flex-col" 
                     data-status="<?php echo $laporan['status']; ?>"
                     data-search="<?php echo strtolower($laporan['nama_peralatan'] . ' ' . $laporan['nama_pelapor'] . ' ' . $laporan['deskripsi']); ?>">
                    
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-5 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center shadow-md flex-shrink-0">
                                    <i class="fa-solid fa-wrench text-white text-base"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-bold text-gray-800 truncate leading-tight"><?php echo htmlspecialchars($laporan['nama_peralatan']); ?></h3>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate"><?php echo htmlspecialchars($laporan['jenis']); ?></p>
                                </div>
                            </div>
                            <span class="status-badge <?php echo $status['bg']; ?> <?php echo $status['text']; ?> px-2.5 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5 whitespace-nowrap flex-shrink-0">
                                <i class="fa-solid <?php echo $status['icon']; ?> text-xs"></i>
                                <span class="hidden xl:inline"><?php echo $status['label']; ?></span>
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 space-y-3.5 flex-1 flex flex-col">
                        <!-- Pelapor Info -->
                        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                            <div class="flex items-center gap-2 mb-1.5">
                                <i class="fa-solid fa-user text-blue-600 text-xs"></i>
                                <span class="text-xs font-bold text-blue-900">Pelapor</span>
                            </div>
                            <div class="space-y-0.5 text-sm text-gray-700">
                                <p class="font-semibold truncate"><?php echo htmlspecialchars($laporan['nama_pelapor']); ?></p>
                                <p class="text-xs text-gray-600 truncate"><?php echo htmlspecialchars($laporan['departemen']); ?></p>
                                <p class="text-xs text-gray-600 truncate"><i class="fa-solid fa-phone mr-1"></i><?php echo htmlspecialchars($laporan['no_hp']); ?></p>
                            </div>
                        </div>

                        <!-- Lokasi -->
                        <div class="flex items-center gap-2.5 text-sm bg-purple-50 rounded-lg p-2.5 border border-purple-100">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-map-marker-alt text-purple-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-purple-900 font-semibold mb-0.5">Lokasi</p>
                                <p class="text-sm text-gray-700 font-medium truncate"><?php echo htmlspecialchars($laporan['lokasi']); ?></p>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 flex-1 min-h-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <i class="fa-solid fa-file-lines text-gray-600 text-xs"></i>
                                <span class="text-xs font-bold text-gray-700">Deskripsi Kerusakan</span>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed line-clamp-3"><?php echo nl2br(htmlspecialchars($laporan['deskripsi'])); ?></p>
                        </div>

                        <!-- Tanggal -->
                        <div class="flex items-center gap-2 text-xs text-gray-500 pt-2 border-t border-gray-200">
                            <i class="fa-solid fa-calendar"></i>
                            <span>Dilaporkan: <?php echo date('d F Y', strtotime($laporan['tanggal_lapor'])); ?></span>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-end gap-2 border-t border-gray-200 flex-shrink-0">
                        <button onclick="openStatusModal(<?php echo $laporan['id_laporan']; ?>, '<?php echo addslashes($laporan['nama_peralatan']); ?>', '<?php echo $laporan['status']; ?>')"
                            class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fa-solid fa-rotate text-xs"></i>
                            <span>Status</span>
                        </button>

                        <button onclick="openEditModal(<?php echo $laporan['id_laporan']; ?>, '<?php echo addslashes($laporan['nama_peralatan']); ?>', '<?php echo addslashes($laporan['deskripsi']); ?>')"
                            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fa-solid fa-pen text-xs"></i>
                            <span>Edit</span>
                        </button>

                        <a href="data.php?hapus=<?php echo $laporan['id_laporan']; ?>" onclick="return confirm('Yakin ingin menghapus laporan ini?')"
                            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fa-solid fa-trash text-xs"></i>
                            <span>Hapus</span>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Update Status -->
<div id="modalStatus" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-5 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-rotate text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Ubah Status Laporan</h3>
                        <p class="text-slate-300 text-sm" id="status_peralatan"></p>
                    </div>
                </div>
                <button onclick="closeModal('modalStatus')" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="data.php" class="p-6">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id_laporan" id="status_id">

            <div class="space-y-3">
                <label class="block">
                    <input type="radio" name="status" value="belum diperbaiki" class="peer hidden">
                    <div class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:border-red-500 peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center peer-checked:bg-red-200">
                            <i class="fa-solid fa-exclamation-triangle text-red-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">Belum Diperbaiki</p>
                            <p class="text-xs text-gray-600">Laporan masih menunggu perbaikan</p>
                        </div>
                    </div>
                </label>

                <label class="block">
                    <input type="radio" name="status" value="sedang diperbaiki" class="peer hidden">
                    <div class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center peer-checked:bg-yellow-200">
                            <i class="fa-solid fa-tools text-yellow-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">Sedang Diperbaiki</p>
                            <p class="text-xs text-gray-600">Perbaikan sedang dalam proses</p>
                        </div>
                    </div>
                </label>

                <label class="block">
                    <input type="radio" name="status" value="selesai" class="peer hidden">
                    <div class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:border-green-500 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center peer-checked:bg-green-200">
                            <i class="fa-solid fa-check-circle text-green-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">Selesai</p>
                            <p class="text-xs text-gray-600">Perbaikan telah selesai</p>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeModal('modalStatus')"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                    <i class="fa-solid fa-times mr-2"></i>Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fa-solid fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Deskripsi -->
<div id="modalEdit" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-5 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-pen text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Edit Deskripsi Laporan</h3>
                        <p class="text-slate-300 text-sm" id="edit_peralatan"></p>
                    </div>
                </div>
                <button onclick="closeModal('modalEdit')" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="data.php" class="p-6">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_laporan" id="edit_id">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fa-solid fa-file-lines text-slate-600 mr-2"></i>Deskripsi Kerusakan
                </label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="6" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition resize-none"
                    placeholder="Jelaskan detail kerusakan..."></textarea>
                <p class="text-xs text-gray-500 mt-2">Jelaskan kerusakan dengan detail agar teknisi dapat memahami masalah dengan baik</p>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeModal('modalEdit')"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                    <i class="fa-solid fa-times mr-2"></i>Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fa-solid fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal functions
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Open status modal
    function openStatusModal(id, peralatan, currentStatus) {
        document.getElementById('status_id').value = id;
        document.getElementById('status_peralatan').textContent = peralatan;
        
        // Set current status as checked
        const radios = document.querySelectorAll('input[name="status"]');
        radios.forEach(radio => {
            if (radio.value === currentStatus) {
                radio.checked = true;
            }
        });
        
        openModal('modalStatus');
    }

    // Open edit modal
    function openEditModal(id, peralatan, deskripsi) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_peralatan').textContent = peralatan;
        document.getElementById('edit_deskripsi').value = deskripsi;
        openModal('modalEdit');
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        filterCards();
    });

    // Filter status functionality
    document.getElementById('filterStatus').addEventListener('change', function() {
        filterCards();
    });

    function filterCards() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const filterStatus = document.getElementById('filterStatus').value;
        const cards = document.querySelectorAll('.laporan-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const searchData = card.getAttribute('data-search');
            const cardStatus = card.getAttribute('data-status');
            
            const matchSearch = searchData.includes(searchTerm);
            const matchStatus = filterStatus === '' || cardStatus === filterStatus;
            
            if (matchSearch && matchStatus) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update count
        document.getElementById('totalCount').textContent = visibleCount;
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            ['modalStatus', 'modalEdit'].forEach(id => {
                if (document.getElementById(id).classList.contains('active')) {
                    closeModal(id);
                }
            });
        }
    });

    // Close modal when clicking outside
    ['modalStatus', 'modalEdit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal(id);
            }
        });
    });
</script>

</body>
</html>