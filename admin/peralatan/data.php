<?php 
// JANGAN ADA session_start() di sini!
include '../../config/koneksi.php';  // Session auto start dari sini
include '../../auth/cek_login.php';  // Tinggal cek session
cek_role('admin'); // Opsional
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Data Peralatan - Sistem Manajemen</title>
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

        .category-section {
            margin-bottom: 2rem;
        }

        .category-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem 0.75rem 0 0;
            margin-bottom: 0;
            border-bottom: 3px solid #3b82f6;
        }

        .category-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            background: rgba(59, 130, 246, 0.2);
            border-radius: 0.5rem;
            margin-right: 0.75rem;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <?php
    include '../../includes/sidebar.php';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
        $nama = $_POST['nama_peralatan'];
        $jenis = $_POST['kategori']; // Langsung gunakan kategori sebagai jenis
        $kondisi = $_POST['kondisi'];
        $lokasi = $_POST['lokasi'];

        $query = "INSERT INTO peralatan (nama_peralatan, jenis, kondisi, lokasi) VALUES (?, ?, ?, ?)";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("ssss", $nama, $jenis, $kondisi, $lokasi);

        if ($stmt->execute()) {
            echo "<script>alert('Data berhasil ditambahkan!'); window.location.href='data.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan data!');</script>";
        }
    }

    // Handle delete
    if (isset($_GET['hapus'])) {
        $id = $_GET['hapus'];
        $koneksi->query("DELETE FROM peralatan WHERE id_peralatan='$id'");
        echo "<script>alert('Data berhasil dihapus!'); window.location.href='data.php';</script>";
    }

    // Function to categorize equipment
    function categorizeEquipment($jenis)
    {
        $jenis = strtolower($jenis);

        // Komputer & Laptop
        if (
            strpos($jenis, 'komputer') !== false ||
            strpos($jenis, 'laptop') !== false ||
            strpos($jenis, 'pc') !== false ||
            strpos($jenis, 'desktop') !== false ||
            strpos($jenis, 'monitor') !== false ||
            strpos($jenis, 'cpu') !== false
        ) {
            return 'Komputer & Laptop';
        }

        // Perangkat Cetak & Scan
        if (
            strpos($jenis, 'printer') !== false ||
            strpos($jenis, 'cetak') !== false ||
            strpos($jenis, 'scanner') !== false ||
            strpos($jenis, 'print') !== false ||
            strpos($jenis, 'fotocopy') !== false
        ) {
            return 'Perangkat Cetak & Scan';
        }

        // Jaringan
        if (
            strpos($jenis, 'jaringan') !== false ||
            strpos($jenis, 'router') !== false ||
            strpos($jenis, 'switch') !== false ||
            strpos($jenis, 'access point') !== false ||
            strpos($jenis, 'modem') !== false ||
            strpos($jenis, 'lan') !== false ||
            strpos($jenis, 'wifi') !== false
        ) {
            return 'Jaringan';
        }

        // Perangkat Keamanan
        if (
            strpos($jenis, 'cctv') !== false ||
            strpos($jenis, 'keamanan') !== false ||
            strpos($jenis, 'security') !== false ||
            strpos($jenis, 'kamera') !== false ||
            strpos($jenis, 'fingerprint') !== false ||
            strpos($jenis, 'akses') !== false
        ) {
            return 'Perangkat Keamanan';
        }

        return 'Lainnya';
    }

    // Function to get category icon
    function getCategoryIcon($category) {
        $icons = [
            'Komputer & Laptop' => 'fa-laptop',
            'Perangkat Cetak & Scan' => 'fa-print',
            'Jaringan' => 'fa-network-wired',
            'Perangkat Keamanan' => 'fa-video',
            'Lainnya' => 'fa-box'
        ];
        return $icons[$category] ?? 'fa-folder';
    }

    // Get all equipment and categorize them
    $result = $koneksi->query("SELECT * FROM peralatan ORDER BY id_peralatan ASC");
    $categorized_equipment = [
        'Komputer & Laptop' => [],
        'Perangkat Cetak & Scan' => [],
        'Jaringan' => [],
        'Perangkat Keamanan' => [],
        'Lainnya' => []
    ];

    while ($row = $result->fetch_assoc()) {
        $category = categorizeEquipment($row['jenis']);
        $categorized_equipment[$category][] = $row;
    }

    // Get statistics
    $total_peralatan = $koneksi->query("SELECT COUNT(*) AS total FROM peralatan")->fetch_assoc()['total'];
    $baik = $koneksi->query("SELECT COUNT(*) AS total FROM peralatan WHERE kondisi='baik'")->fetch_assoc()['total'];
    $rusak = $koneksi->query("SELECT COUNT(*) AS total FROM peralatan WHERE kondisi='rusak'")->fetch_assoc()['total'];
    $perbaikan = $koneksi->query("SELECT COUNT(*) AS total FROM peralatan WHERE kondisi='dalam perbaikan'")->fetch_assoc()['total'];
    ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 bg-gradient-to-br from-slate-700 to-slate-900 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-screwdriver-wrench text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Data Peralatan</h1>
                    <p class="text-gray-600 mt-1">Kelola dan pantau semua peralatan yang terdaftar dalam sistem</p>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="bg-white shadow-md rounded-xl p-6 mb-6 border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <!-- Search Box -->
                    <div class="relative w-full sm:w-auto">
                        <input type="text"
                            id="searchInput"
                            placeholder="Cari peralatan..."
                            class="pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full sm:w-72 transition">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                    </div>

                    <!-- Filter Dropdown -->
                    <select id="filterStatus"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white transition w-full sm:w-auto">
                        <option value="">🔍 Semua Kondisi</option>
                        <option value="baik">✅ Baik</option>
                        <option value="rusak">❌ Rusak</option>
                        <option value="dalam perbaikan">🔧 Dalam Perbaikan</option>
                    </select>
                </div>

                <!-- Add Button -->
                <button onclick="openModal()"
                    class="bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 transform hover:scale-105">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Peralatan</span>
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Peralatan Card -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-slate-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Total Peralatan</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_peralatan; ?></h3>
                    </div>
                    <div class="bg-gradient-to-br from-slate-100 to-slate-200 p-4 rounded-xl">
                        <i class="fa-solid fa-screwdriver-wrench text-slate-700 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Kondisi Baik Card -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Kondisi Baik</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $baik; ?></h3>
                    </div>
                    <div class="bg-gradient-to-br from-green-100 to-green-200 p-4 rounded-xl">
                        <i class="fa-solid fa-circle-check text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Rusak Card -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Rusak</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $rusak; ?></h3>
                    </div>
                    <div class="bg-gradient-to-br from-red-100 to-red-200 p-4 rounded-xl">
                        <i class="fa-solid fa-triangle-exclamation text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Dalam Perbaikan Card -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Dalam Perbaikan</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $perbaikan; ?></h3>
                    </div>
                    <div class="bg-gradient-to-br from-orange-100 to-orange-200 p-4 rounded-xl">
                        <i class="fa-solid fa-hammer text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table by Categories -->
        <?php foreach ($categorized_equipment as $category => $equipment): ?>
            <?php if (!empty($equipment)): ?>
                <div class="category-section">
                    <!-- Category Header -->
                    <div class="category-header mb-0">
                        <div class="flex items-center">
                            <div class="category-icon">
                                <i class="fa-solid <?php echo getCategoryIcon($category); ?> text-blue-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white"><?php echo $category ?></h2>
                                <p class="text-slate-300 text-sm mt-0.5"><?php echo count($equipment); ?> peralatan terdaftar</p>
                            </div>
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="bg-white rounded-b-xl shadow-lg overflow-hidden border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-slate-50 to-slate-100">
                                    <tr>
                                        <th class="py-4 px-6 text-left text-xs font-bold text-slate-700 uppercase tracking-wider w-[5%]">No</th>
                                        <th class="py-4 px-6 text-left text-xs font-bold text-slate-700 uppercase tracking-wider w-[25%]">Nama Peralatan</th>
                                        <th class="py-4 px-6 text-left text-xs font-bold text-slate-700 uppercase tracking-wider w-[15%]">Kategori</th>
                                        <th class="py-4 px-6 text-center text-xs font-bold text-slate-700 uppercase tracking-wider w-[15%]">Kondisi</th>
                                        <th class="py-4 px-6 text-left text-xs font-bold text-slate-700 uppercase tracking-wider w-[20%]">Lokasi</th>
                                        <th class="py-4 px-6 text-center text-xs font-bold text-slate-700 uppercase tracking-wider w-[20%]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php
                                    $no = 1;
                                    foreach ($equipment as $row) {
                                        // Kondisi badge styling
                                        $kondisiBadge = '';
                                        switch (strtolower($row['kondisi'])) {
                                            case 'baik':
                                                $kondisiBadge = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                                    <i class="fa-solid fa-circle-check"></i> Baik
                                                                </span>';
                                                break;
                                            case 'rusak':
                                                $kondisiBadge = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                                    <i class="fa-solid fa-triangle-exclamation"></i> Rusak
                                                                </span>';
                                                break;
                                            case 'dalam perbaikan':
                                                $kondisiBadge = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                                                    <i class="fa-solid fa-hammer"></i> Dalam Perbaikan
                                                                </span>';
                                                break;
                                        }

                                        echo "
                                        <tr class='hover:bg-slate-50 transition-colors duration-150'>
                                            <td class='py-4 px-6 text-sm font-medium text-gray-900'>{$no}</td>
                                            <td class='py-4 px-6'>
                                                <div class='flex items-center gap-3'>
                                                    <div class='w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center'>
                                                        <i class='fa-solid fa-tools text-slate-700'></i>
                                                    </div>
                                                    <span class='text-sm font-semibold text-gray-900'>{$row['nama_peralatan']}</span>
                                                </div>
                                            </td>
                                            <td class='py-4 px-6 text-sm text-gray-700'>{$row['jenis']}</td>
                                            <td class='py-4 px-6 text-center'>{$kondisiBadge}</td>
                                            <td class='py-4 px-6 text-sm text-gray-700'>
                                                <div class='flex items-center gap-2'>
                                                    <i class='fa-solid fa-location-dot text-gray-400'></i>
                                                    {$row['lokasi']}
                                                </div>
                                            </td>
                                            <td class='py-4 px-6'>
                                                <div class='flex items-center justify-center gap-2'>
                                                    <a href='edit.php?id={$row['id_peralatan']}' 
                                                        class='inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105' 
                                                        title='Edit Data'>
                                                        <i class='fas fa-edit'></i>
                                                        Edit
                                                    </a>
                                                    <a href='data.php?hapus={$row['id_peralatan']}' 
                                                        onclick='return confirm(\"Apakah Anda yakin ingin menghapus peralatan ini?\")' 
                                                        class='inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105' 
                                                        title='Hapus Data'>
                                                        <i class='fas fa-trash'></i>
                                                        Hapus
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>";
                                        $no++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Modal Form Tambah Peralatan -->
    <div id="modalTambah" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-plus text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Tambah Peralatan</h3>
                            <p class="text-slate-300 text-sm mt-1">Isi formulir di bawah untuk menambah data peralatan</p>
                        </div>
                    </div>
                    <button onclick="closeModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all">
                        <i class="fa-solid fa-times text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form method="POST" action="" class="p-8">
                <div class="space-y-6">
                    <!-- Nama Peralatan -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-screwdriver-wrench text-slate-700 mr-2"></i>
                            Nama Peralatan
                        </label>
                        <input type="text" name="nama_peralatan" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                            placeholder="Masukkan nama peralatan">
                    </div>

                    <!-- Kategori Peralatan (Dropdown) -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-folder text-slate-700 mr-2"></i>
                            Kategori Peralatan
                        </label>
                        <select name="kategori" id="kategoriSelect" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition bg-white">
                            <option value="">Pilih Kategori</option>
                            <option value="Komputer & Laptop">💻 Komputer & Laptop</option>
                            <option value="Perangkat Cetak & Scan">🖨️ Perangkat Cetak & Scan</option>
                            <option value="Jaringan">🌐 Jaringan</option>
                            <option value="Perangkat Keamanan">📹 Perangkat Keamanan</option>
                            <option value="Lainnya">📦 Lainnya</option>
                        </select>
                    </div>

                    <!-- Kondisi -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-clipboard-check text-slate-700 mr-2"></i>
                            Kondisi
                        </label>
                        <select name="kondisi" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition bg-white">
                            <option value="">Pilih Kondisi</option>
                            <option value="baik">✅ Baik</option>
                            <option value="rusak">❌ Rusak</option>
                            <option value="dalam perbaikan">🔧 Dalam Perbaikan</option>
                        </select>
                    </div>

                    <!-- Lokasi -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-location-dot text-slate-700 mr-2"></i>
                            Lokasi
                        </label>
                        <input type="text" name="lokasi" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                            placeholder="Contoh: Gudang A, Lantai 2, Ruang IT">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeModal()"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                        <i class="fa-solid fa-times mr-2"></i>
                        Batal
                    </button>
                    <button type="submit" name="tambah"
                        class="px-6 py-3 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fa-solid fa-save mr-2"></i>
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('modalTambah').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modalTambah').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tables = document.querySelectorAll('table');

            tables.forEach(table => {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        });

        // Filter functionality
        document.getElementById('filterStatus').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const tables = document.querySelectorAll('table');

            tables.forEach(table => {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    if (filterValue === '') {
                        row.style.display = '';
                    } else {
                        const kondisiCell = row.cells[3];
                        const kondisiText = kondisiCell.textContent.toLowerCase();
                        row.style.display = kondisiText.includes(filterValue) ? '' : 'none';
                    }
                });
            });
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Close modal when clicking outside
        document.getElementById('modalTambah').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
    </script>
</body>

</html>