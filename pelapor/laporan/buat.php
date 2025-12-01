<?php
include '../../config/koneksi.php';
include '../../auth/cek_login.php';
cek_role('pelapor');

$id_user = $_SESSION['user_id'];
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peralatan = intval($_POST['id_peralatan'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($id_peralatan <= 0) {
        $error = 'Silakan pilih peralatan yang rusak!';
    } elseif (empty($deskripsi)) {
        $error = 'Deskripsi kerusakan tidak boleh kosong!';
    } else {
        // Insert laporan
        $stmt = $koneksi->prepare("INSERT INTO laporan_kerusakan (id_user, id_peralatan, deskripsi, tanggal_lapor, status) VALUES (?, ?, ?, CURDATE(), 'belum diperbaiki')");
        $stmt->bind_param("iis", $id_user, $id_peralatan, $deskripsi);

        if ($stmt->execute()) {
            header("Location: ../dashboard.php");
            exit;
        } else {
            $error = 'Gagal membuat laporan. Silakan coba lagi!';
        }
        $stmt->close();
    }
}

// Get all available equipment
$peralatan_query = "SELECT id_peralatan, nama_peralatan, jenis, lokasi, kondisi FROM peralatan ORDER BY nama_peralatan ASC";
$peralatan_result = $koneksi->query($peralatan_query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - Sistem Laporan Kerusakan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-container {
            animation: slideInRight 0.5s ease-out;
        }

        .select-custom:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-plus-circle text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Buat Laporan Kerusakan</h1>
                    <p class="text-gray-600 mt-1">Laporkan kerusakan peralatan yang ditemukan</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 form-container">
                    <!-- Form Header -->
                    <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-6">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-file-pen"></i>
                            Form Laporan Kerusakan
                        </h2>
                        <p class="text-slate-300 text-sm mt-1">Isi formulir di bawah dengan lengkap dan jelas</p>
                    </div>

                    <!-- Alert Messages -->
                    <?php if ($success): ?>
                        <div class="mx-8 mt-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                            <div class="flex items-center">
                                <i class="fa-solid fa-check-circle text-green-500 text-xl mr-3"></i>
                                <div class="flex-1">
                                    <h3 class="text-green-800 font-bold">Laporan Berhasil Dibuat!</h3>
                                    <p class="text-green-700 text-sm mt-1">Laporan Anda telah disimpan dan akan segera ditindaklanjuti.</p>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-3">
                                <a href="data.php" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all">
                                    <i class="fa-solid fa-list"></i>
                                    Lihat Semua Laporan
                                </a>
                                <a href="buat.php" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-green-700 border border-green-300 px-4 py-2 rounded-lg text-sm font-semibold transition-all">
                                    <i class="fa-solid fa-plus"></i>
                                    Buat Laporan Lagi
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="mx-8 mt-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                            <div class="flex items-center">
                                <i class="fa-solid fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                                <div>
                                    <h3 class="text-red-800 font-bold">Terjadi Kesalahan!</h3>
                                    <p class="text-red-700 text-sm"><?php echo $error; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Form Body -->
                    <form method="POST" action="buat.php" class="p-8">
                        <div class="space-y-6">
                            <!-- Pilih Peralatan -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fa-solid fa-screwdriver-wrench text-slate-600 mr-2"></i>
                                    Pilih Peralatan yang Rusak
                                </label>
                                <select name="id_peralatan" required
                                    class="select-custom w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition bg-white"
                                    onchange="showPeralatanInfo(this)">
                                    <option value="">-- Pilih Peralatan --</option>
                                    <?php while ($peralatan = $peralatan_result->fetch_assoc()): ?>
                                        <option value="<?php echo $peralatan['id_peralatan']; ?>"
                                            data-jenis="<?php echo htmlspecialchars($peralatan['jenis']); ?>"
                                            data-lokasi="<?php echo htmlspecialchars($peralatan['lokasi']); ?>"
                                            data-kondisi="<?php echo $peralatan['kondisi']; ?>">
                                            <?php echo htmlspecialchars($peralatan['nama_peralatan']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Info Peralatan (Hidden by default) -->
                            <div id="peralatanInfo" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="text-sm font-bold text-blue-900 mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-info-circle"></i>
                                    Informasi Peralatan
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2 text-gray-700">
                                        <i class="fa-solid fa-tag text-blue-600 w-5"></i>
                                        <span class="font-semibold">Jenis:</span>
                                        <span id="infoJenis">-</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-700">
                                        <i class="fa-solid fa-location-dot text-blue-600 w-5"></i>
                                        <span class="font-semibold">Lokasi:</span>
                                        <span id="infoLokasi">-</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-700">
                                        <i class="fa-solid fa-circle-info text-blue-600 w-5"></i>
                                        <span class="font-semibold">Kondisi:</span>
                                        <span id="infoKondisi" class="px-2 py-1 rounded-full text-xs font-semibold">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi Kerusakan -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fa-solid fa-clipboard-list text-slate-600 mr-2"></i>
                                    Deskripsi Kerusakan
                                </label>
                                <textarea name="deskripsi" required rows="6"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
                                    placeholder="Jelaskan kerusakan secara detail, seperti:&#10;- Apa yang rusak?&#10;- Kapan ditemukan?&#10;- Bagaimana kondisinya?&#10;- Apakah masih bisa digunakan?"></textarea>
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fa-solid fa-lightbulb mr-1"></i>
                                    Tip: Berikan deskripsi yang jelas dan lengkap agar teknisi dapat menindaklanjuti dengan cepat
                                </p>
                            </div>

                            <!-- Tanggal Lapor (Auto) -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fa-solid fa-calendar text-slate-600 mr-2"></i>
                                    Tanggal Laporan
                                </label>
                                <input type="text" value="<?php echo date('d F Y'); ?>" disabled
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                                <p class="text-xs text-gray-500 mt-1">Tanggal otomatis diisi dengan hari ini</p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                            <a href="../dashboard.php" class="inline-flex items-center gap-2 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                                <i class="fa-solid fa-arrow-left"></i>
                                Kembali
                            </a>
                            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fa-solid fa-paper-plane"></i>
                                Kirim Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Sidebar -->
            <div class="lg:col-span-1">
                <!-- Tips Card -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg p-6 border border-blue-200 mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-lightbulb text-white text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-blue-900">Tips Melaporkan</h3>
                    </div>
                    <ul class="space-y-3 text-sm text-blue-900">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-blue-600 mt-0.5"></i>
                            <span>Pastikan peralatan yang dipilih sudah benar</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-blue-600 mt-0.5"></i>
                            <span>Jelaskan kerusakan dengan detail dan jelas</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-blue-600 mt-0.5"></i>
                            <span>Sertakan informasi waktu/kondisi saat ditemukan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-blue-600 mt-0.5"></i>
                            <span>Laporan akan ditindaklanjuti maksimal 1x24 jam</span>
                        </li>
                    </ul>
                </div>

                <!-- Status Info Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-info-circle text-slate-700"></i>
                        Status Laporan
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-200">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-exclamation-circle text-red-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-red-900 text-sm">Belum Diperbaiki</h4>
                                <p class="text-xs text-red-700 mt-0.5">Laporan baru masuk, menunggu ditindaklanjuti</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 bg-orange-50 rounded-lg border border-orange-200">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-tools text-orange-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-orange-900 text-sm">Sedang Diperbaiki</h4>
                                <p class="text-xs text-orange-700 mt-0.5">Teknisi sedang menangani perbaikan</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg border border-green-200">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-check-circle text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-green-900 text-sm">Selesai</h4>
                                <p class="text-xs text-green-700 mt-0.5">Perbaikan telah selesai dilakukan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show peralatan info when selected
        function showPeralatanInfo(select) {
            const option = select.options[select.selectedIndex];
            const infoDiv = document.getElementById('peralatanInfo');

            if (select.value) {
                const jenis = option.getAttribute('data-jenis');
                const lokasi = option.getAttribute('data-lokasi');
                const kondisi = option.getAttribute('data-kondisi');

                document.getElementById('infoJenis').textContent = jenis;
                document.getElementById('infoLokasi').textContent = lokasi;

                // Kondisi badge styling
                const kondisiSpan = document.getElementById('infoKondisi');
                kondisiSpan.textContent = kondisi.charAt(0).toUpperCase() + kondisi.slice(1);

                if (kondisi === 'baik') {
                    kondisiSpan.className = 'px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800';
                } else if (kondisi === 'rusak') {
                    kondisiSpan.className = 'px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800';
                } else {
                    kondisiSpan.className = 'px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800';
                }

                infoDiv.classList.remove('hidden');
            } else {
                infoDiv.classList.add('hidden');
            }
        }
    </script>

</body>

</html>