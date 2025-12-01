<?php
include '../../config/koneksi.php';
include '../../auth/cek_login.php';
cek_role('pelapor');

$id_user = $_SESSION['user_id'];

// ----------------------
// HANDLE ACTIONS
// ----------------------

// Edit Deskripsi Laporan (hanya jika belum selesai)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_laporan = intval($_POST['id_laporan'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($id_laporan > 0 && !empty($deskripsi)) {
        // Cek ownership dan status
        $check = $koneksi->prepare("SELECT id_laporan FROM laporan_kerusakan WHERE id_laporan = ? AND id_user = ? AND status != 'selesai'");
        $check->bind_param("ii", $id_laporan, $id_user);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $stmt = $koneksi->prepare("UPDATE laporan_kerusakan SET deskripsi = ? WHERE id_laporan = ?");
            $stmt->bind_param("si", $deskripsi, $id_laporan);
            if ($stmt->execute()) {
                echo "<script>alert('Deskripsi laporan berhasil diperbarui!'); window.location.href='data.php';</script>";
            } else {
                echo "<script>alert('Gagal memperbarui deskripsi!'); window.location.href='data.php';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Laporan tidak dapat diedit!'); window.location.href='data.php';</script>";
        }
        $check->close();
    }
    exit;
}

// Hapus Laporan (hanya jika belum diperbaiki)
if (isset($_GET['hapus'])) {
    $id_laporan = intval($_GET['hapus']);
    if ($id_laporan > 0) {
        // Cek ownership dan status
        $check = $koneksi->prepare("SELECT id_laporan FROM laporan_kerusakan WHERE id_laporan = ? AND id_user = ? AND status = 'belum diperbaiki'");
        $check->bind_param("ii", $id_laporan, $id_user);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $stmt = $koneksi->prepare("DELETE FROM laporan_kerusakan WHERE id_laporan = ?");
            $stmt->bind_param("i", $id_laporan);
            if ($stmt->execute()) {
                echo "<script>alert('Laporan berhasil dihapus!'); window.location.href='data.php';</script>";
            } else {
                echo "<script>alert('Gagal menghapus laporan!'); window.location.href='data.php';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Laporan tidak dapat dihapus!'); window.location.href='data.php';</script>";
        }
        $check->close();
    }
    exit;
}

// ----------------------
// FETCH DATA
// ----------------------

// Hitung total laporan untuk badge filter
$count_result = $koneksi->prepare("SELECT COUNT(*) as total FROM laporan_kerusakan WHERE id_user = ?");
$count_result->bind_param("i", $id_user);
$count_result->execute();
$count_data = $count_result->get_result()->fetch_assoc();
$total_laporan = $count_data['total'];
$count_result->close();

// Ambil semua laporan user dengan JOIN
$query = $koneksi->prepare("
    SELECT 
        lk.id_laporan,
        lk.deskripsi,
        lk.tanggal_lapor,
        lk.status,
        p.nama_peralatan,
        p.jenis,
        p.lokasi,
        p.kondisi
    FROM laporan_kerusakan lk
    INNER JOIN peralatan p ON lk.id_peralatan = p.id_peralatan
    WHERE lk.id_user = ?
    ORDER BY 
        CASE 
            WHEN lk.status = 'belum diperbaiki' THEN 1
            WHEN lk.status = 'sedang diperbaiki' THEN 2
            WHEN lk.status = 'selesai' THEN 3
        END,
        lk.tanggal_lapor DESC
");
$query->bind_param("i", $id_user);
$query->execute();
$result = $query->get_result();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya - Sistem Laporan Kerusakan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com?plugins=line-clamp"></script>
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
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">

    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-list-check text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Laporan Saya</h1>
                        <p class="text-gray-600 mt-1">Pantau status laporan kerusakan yang Anda buat</p>
                    </div>
                </div>
                <a href="buat.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fa-solid fa-plus"></i>
                    Buat Laporan Baru
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white shadow-md rounded-xl p-6 mb-6 border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <!-- Search Box -->
                <div class="relative flex-1">
                    <input type="text" id="searchInput" placeholder="Cari berdasarkan peralatan atau deskripsi..."
                        class="pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                </div>

                <!-- Filter Status -->
                <div class="flex items-center gap-3">
                    <select id="filterStatus" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white">
                        <option value="">Semua Status</option>
                        <option value="belum diperbaiki">Belum Diperbaiki</option>
                        <option value="sedang diperbaiki">Sedang Diperbaiki</option>
                        <option value="selesai">Selesai</option>
                    </select>

                    <!-- Total Badge -->
                    <div class="px-4 py-2.5 bg-blue-100 rounded-lg whitespace-nowrap">
                        <span class="text-sm font-semibold text-blue-700">
                            <i class="fa-solid fa-list mr-2"></i><span id="totalCount"><?php echo $total_laporan; ?></span> Laporan
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Grid -->
        <div id="cardsGrid" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php if ($total_laporan === 0): ?>
                <div class="col-span-full bg-white rounded-xl shadow-md p-12 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                        <i class="fa-solid fa-clipboard-list text-blue-500 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum Ada Laporan</h3>
                    <p class="text-gray-600 mb-4">Anda belum membuat laporan kerusakan</p>
                    <a href="buat.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all">
                        <i class="fa-solid fa-plus"></i>
                        Buat Laporan Pertama
                    </a>
                </div>
            <?php else: ?>
                <?php while ($laporan = $result->fetch_assoc()):
                    // Status badge styling
                    $status_config = [
                        'belum diperbaiki' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fa-clock', 'label' => 'Belum Diperbaiki'],
                        'sedang diperbaiki' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-tools', 'label' => 'Sedang Diperbaiki'],
                        'selesai' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'fa-check-circle', 'label' => 'Selesai']
                    ];
                    $status = $status_config[$laporan['status']];

                    // Kondisi badge
                    $kondisi_config = [
                        'baik' => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
                        'rusak' => ['bg' => 'bg-red-100', 'text' => 'text-red-700'],
                        'dalam perbaikan' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700']
                    ];
                    $kondisi = $kondisi_config[$laporan['kondisi']];
                ?>
                    <div class="laporan-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 transform hover:-translate-y-1 flex flex-col h-full"
                        data-status="<?php echo $laporan['status']; ?>"
                        data-search="<?php echo strtolower($laporan['nama_peralatan'] . ' ' . $laporan['deskripsi']); ?>">

                        <!-- Card Header -->
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200 flex-shrink-0">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-lg flex-shrink-0">
                                        <i class="fa-solid fa-wrench text-white text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-bold text-gray-800 truncate"><?php echo htmlspecialchars($laporan['nama_peralatan']); ?></h3>
                                        <p class="text-xs text-gray-600 mt-0.5 truncate"><?php echo htmlspecialchars($laporan['jenis']); ?></p>
                                    </div>
                                </div>
                                <span class="status-badge <?php echo $status['bg']; ?> <?php echo $status['text']; ?> px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 whitespace-nowrap flex-shrink-0">
                                    <i class="fa-solid <?php echo $status['icon']; ?>"></i>
                                    <span class="hidden sm:inline"><?php echo $status['label']; ?></span>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 space-y-4 flex-1 flex flex-col">
                            <!-- Info Peralatan -->
                            <div class="grid grid-cols-2 gap-3 flex-shrink-0">
                                <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i class="fa-solid fa-map-marker-alt text-purple-600 text-xs"></i>
                                        <span class="text-xs font-bold text-purple-900">Lokasi</span>
                                    </div>
                                    <p class="text-sm text-gray-700 font-semibold truncate"><?php echo htmlspecialchars($laporan['lokasi']); ?></p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i class="fa-solid fa-info-circle text-gray-600 text-xs"></i>
                                        <span class="text-xs font-bold text-gray-700">Kondisi</span>
                                    </div>
                                    <span class="inline-block text-xs font-bold px-2 py-1 rounded-full <?php echo $kondisi['bg']; ?> <?php echo $kondisi['text']; ?>">
                                        <?php echo ucfirst($laporan['kondisi']); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 flex-1 min-h-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fa-solid fa-file-lines text-gray-600 text-sm"></i>
                                    <span class="text-sm font-bold text-gray-700">Deskripsi Kerusakan</span>
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed line-clamp-3">
                                    <?php echo nl2br(htmlspecialchars($laporan['deskripsi'])); ?>
                                </p>
                            </div>

                            <!-- Timeline -->
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200 flex-shrink-0">
                                <div class="flex items-center gap-2 text-sm">
                                    <i class="fa-solid fa-calendar text-blue-600"></i>
                                    <span class="font-semibold text-blue-900">Dilaporkan:</span>
                                    <span class="text-blue-700"><?php echo date('d M Y', strtotime($laporan['tanggal_lapor'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-2 border-t border-gray-200 flex-shrink-0 mt-auto">
                            <?php if ($laporan['status'] !== 'selesai'): ?>
                                <button onclick="openEditModal(<?php echo $laporan['id_laporan']; ?>, '<?php echo addslashes($laporan['nama_peralatan']); ?>', '<?php echo addslashes($laporan['deskripsi']); ?>')"
                                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                                    <i class="fa-solid fa-pen"></i>
                                    <span class="hidden sm:inline">Edit</span>
                                </button>
                            <?php endif; ?>

                            <?php if ($laporan['status'] === 'belum diperbaiki'): ?>
                                <a href="data.php?hapus=<?php echo $laporan['id_laporan']; ?>" onclick="return confirm('Yakin ingin menghapus laporan ini?')"
                                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                                    <i class="fa-solid fa-trash"></i>
                                    <span class="hidden sm:inline">Hapus</span>
                                </a>
                            <?php endif; ?>

                            <?php if ($laporan['status'] === 'selesai'): ?>
                                <span class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm font-semibold">
                                    <i class="fa-solid fa-check-circle"></i>
                                    Laporan Selesai
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Edit Deskripsi -->
    <div id="modalEdit" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-pen text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Edit Deskripsi Laporan</h3>
                            <p class="text-blue-100 text-sm" id="edit_peralatan"></p>
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
                        <i class="fa-solid fa-file-lines text-blue-600 mr-2"></i>Deskripsi Kerusakan
                    </label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="6" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
                        placeholder="Jelaskan detail kerusakan..."></textarea>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Perbarui deskripsi jika ada informasi tambahan yang perlu disampaikan
                    </p>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('modalEdit')"
                        class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                        <i class="fa-solid fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
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

        // Open edit modal
        function openEditModal(id, peralatan, deskripsi) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_peralatan').textContent = peralatan;
            document.getElementById('edit_deskripsi').value = deskripsi;
            openModal('modalEdit');
        }

        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', filterCards);
        document.getElementById('filterStatus').addEventListener('change', filterCards);

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

            document.getElementById('totalCount').textContent = visibleCount;
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (document.getElementById('modalEdit').classList.contains('active')) {
                    closeModal('modalEdit');
                }
            }
        });

        // Close modal when clicking outside
        document.getElementById('modalEdit').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal('modalEdit');
            }
        });
    </script>

</body>

</html>