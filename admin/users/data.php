<?php
include '../../config/koneksi.php';
include '../../auth/cek_login.php';
cek_role('admin');

// ----------------------
// HANDLE ACTIONS
// ----------------------

// Tambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $nama       = trim($_POST['nama'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $departemen = trim($_POST['departemen'] ?? '');
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $role       = 'pelapor';

    if (empty($nama) || empty($email) || empty($password)) {
        echo "<script>alert('Nama, email dan password wajib diisi!'); window.location.href='data.php';</script>";
        exit;
    }

    // Cek email unik
    $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location.href='data.php';</script>";
        exit;
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $koneksi->prepare("INSERT INTO users (nama, email, password, role, departemen, no_hp) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nama, $email, $hash, $role, $departemen, $no_hp);
    if ($stmt->execute()) {
        echo "<script>alert('Pegawai berhasil ditambahkan!'); window.location.href='data.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah pegawai!'); window.location.href='data.php';</script>";
    }
    $stmt->close();
    exit;
}

// Edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id         = intval($_POST['id'] ?? 0);
    $nama       = trim($_POST['nama'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $departemen = trim($_POST['departemen'] ?? '');
    $no_hp      = trim($_POST['no_hp'] ?? '');

    if ($id <= 0 || empty($nama) || empty($email)) {
        echo "<script>alert('Data tidak lengkap!'); window.location.href='data.php';</script>";
        exit;
    }

    // Cek email duplikat
    $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email sudah digunakan user lain!'); window.location.href='data.php';</script>";
        exit;
    }
    $stmt->close();

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE users SET nama=?, email=?, password=?, departemen=?, no_hp=? WHERE id=?");
        $stmt->bind_param("sssssi", $nama, $email, $hash, $departemen, $no_hp, $id);
    } else {
        $stmt = $koneksi->prepare("UPDATE users SET nama=?, email=?, departemen=?, no_hp=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama, $email, $departemen, $no_hp, $id);
    }
    if ($stmt->execute()) {
        echo "<script>alert('Data pegawai berhasil diperbarui!'); window.location.href='data.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate pegawai!'); window.location.href='data.php';</script>";
    }
    $stmt->close();
    exit;
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id > 0) {
        $stmt = $koneksi->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>alert('Pegawai berhasil dihapus!'); window.location.href='data.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus pegawai!'); window.location.href='data.php';</script>";
        }
        $stmt->close();
    }
    exit;
}

// Ambil semua pegawai
$query = "SELECT id, nama, email, departemen, no_hp, created_at FROM users WHERE role = 'pelapor' ORDER BY created_at DESC";
$result = $koneksi->query($query);
$total_pegawai = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pegawai - Sistem Manajemen</title>
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
                <i class="fa-solid fa-users text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Data Pegawai</h1>
                <p class="text-gray-600 mt-1">Kelola akun pegawai/pelapor dalam sistem</p>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white shadow-md rounded-xl p-6 mb-6 border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <!-- Search Box -->
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Cari pegawai..."
                        class="pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-72 transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                </div>

                <!-- Total Badge -->
                <div class="px-4 py-2.5 bg-slate-100 rounded-lg">
                    <span class="text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-users mr-2"></i><?php echo $total_pegawai; ?> Pegawai
                    </span>
                </div>
            </div>

            <!-- Add Button -->
            <button onclick="openModal('modalTambah')"
                class="bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 transform hover:scale-105">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Pegawai</span>
            </button>
        </div>
    </div>

    <!-- Cards Grid -->
    <div id="cardsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if ($total_pegawai === 0): ?>
            <div class="col-span-full bg-white rounded-xl shadow-md p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                    <i class="fa-solid fa-users text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum Ada Pegawai</h3>
                <p class="text-gray-600 mb-4">Klik tombol "Tambah Pegawai" untuk menambah pegawai baru</p>
            </div>
        <?php else: ?>
            <?php while ($pegawai = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 transform hover:-translate-y-1">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center shadow-lg">
                                <i class="fa-solid fa-user text-white text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($pegawai['nama']); ?></h3>
                                <p class="text-sm text-gray-500 mt-0.5"><?php echo htmlspecialchars($pegawai['departemen']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 space-y-3">
                        <div class="flex items-center gap-3 text-sm text-gray-600">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-envelope text-blue-600 text-xs"></i>
                            </div>
                            <span class="flex-1 truncate"><?php echo htmlspecialchars($pegawai['email']); ?></span>
                        </div>

                        <div class="flex items-center gap-3 text-sm text-gray-600">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-phone text-green-600 text-xs"></i>
                            </div>
                            <span><?php echo htmlspecialchars($pegawai['no_hp']); ?></span>
                        </div>

                        <div class="flex items-center gap-3 text-sm text-gray-500">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-calendar text-purple-600 text-xs"></i>
                            </div>
                            <span>Terdaftar: <?php echo date('d M Y', strtotime($pegawai['created_at'])); ?></span>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-2">
                        <button onclick="openEditModal(<?php echo $pegawai['id']; ?>, '<?php echo addslashes($pegawai['nama']); ?>', '<?php echo addslashes($pegawai['email']); ?>', '<?php echo addslashes($pegawai['departemen']); ?>', '<?php echo addslashes($pegawai['no_hp']); ?>')"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                            <i class="fa-solid fa-pen"></i>
                            Edit
                        </button>

                        <a href="data.php?hapus=<?php echo $pegawai['id']; ?>" onclick="return confirm('Yakin ingin menghapus pegawai ini?\n\nSemua laporan terkait pegawai akan ikut terhapus!')"
                            class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                            <i class="fa-solid fa-trash"></i>
                            Hapus
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-6 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-user-plus text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">Tambah Pegawai Baru</h3>
                        <p class="text-slate-300 text-sm mt-1">Buat akun pegawai/pelapor baru</p>
                    </div>
                </div>
                <button onclick="closeModal('modalTambah')" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="data.php" class="p-8">
            <input type="hidden" name="action" value="tambah">

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-user text-slate-600 mr-2"></i>Nama Lengkap
                    </label>
                    <input type="text" name="nama" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                        placeholder="Masukkan nama lengkap">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-envelope text-slate-600 mr-2"></i>Email
                    </label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                        placeholder="nama@email.com">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-lock text-slate-600 mr-2"></i>Password
                    </label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                        placeholder="Minimal 6 karakter">
                    <p class="text-xs text-gray-500 mt-1">Password akan di-hash dan disimpan dengan aman</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-building text-slate-600 mr-2"></i>Departemen
                        </label>
                        <input type="text" name="departemen" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                            placeholder="Contoh: IT Department">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-phone text-slate-600 mr-2"></i>No. HP
                        </label>
                        <input type="text" name="no_hp" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                            placeholder="08xxxxxxxxxx">
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeModal('modalTambah')"
                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                    <i class="fa-solid fa-times mr-2"></i>Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fa-solid fa-save mr-2"></i>Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-6 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-pen text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">Edit Data Pegawai</h3>
                        <p class="text-slate-300 text-sm mt-1">Ubah informasi pegawai</p>
                    </div>
                </div>
                <button onclick="closeModal('modalEdit')" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="data.php" class="p-8">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-user text-slate-600 mr-2"></i>Nama Lengkap
                    </label>
                    <input type="text" name="nama" id="edit_nama" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-envelope text-slate-600 mr-2"></i>Email
                    </label>
                    <input type="email" name="email" id="edit_email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-lock text-slate-600 mr-2"></i>Password (Kosongkan jika tidak diubah)
                    </label>
                    <input type="password" name="password" id="edit_password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                        placeholder="Masukkan password baru">
                    <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah password</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-building text-slate-600 mr-2"></i>Departemen
                        </label>
                        <input type="text" name="departemen" id="edit_departemen" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-phone text-slate-600 mr-2"></i>No. HP
                        </label>
                        <input type="text" name="no_hp" id="edit_no_hp" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition">
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeModal('modalEdit')"
                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                    <i class="fa-solid fa-times mr-2"></i>Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
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
    function openEditModal(id, nama, email, departemen, no_hp) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_departemen').value = departemen;
        document.getElementById('edit_no_hp').value = no_hp;
        document.getElementById('edit_password').value = '';
        openModal('modalEdit');
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('#cardsGrid > div:not(.col-span-full)');
        
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            ['modalTambah', 'modalEdit'].forEach(id => {
                if (document.getElementById(id).classList.contains('active')) {
                    closeModal(id);
                }
            });
        }
    });

    // Close modal when clicking outside
    ['modalTambah', 'modalEdit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal(id);
            }
        });
    });
</script>

</body>
</html>