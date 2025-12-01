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
    <title>Edit Peralatan - Sistem Manajemen</title>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <?php
    include '../../includes/sidebar.php';

    // get ID from URL
    $id = $_GET['id'] ?? '';

    // fench data
    $query = "SELECT * FROM peralatan WHERE id_peralatan = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        echo "<script>alert('Data tidak ditemukan!'); window.location.href='data.php';</script>";
        exit;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $nama = $_POST['nama_peralatan'];
        $jenis = $_POST['jenis'];
        $kondisi = $_POST['kondisi'];
        $lokasi = $_POST['lokasi'];

        $query = "UPDATE peralatan SET nama_peralatan=?, jenis=?, kondisi=?, lokasi=? WHERE id_peralatan=?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("ssssi", $nama, $jenis, $kondisi, $lokasi, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Data berhasil diperbarui!'); window.location.href='data.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui data!');</script>";
        }
    }
    ?>

    <!-- main content -->
    <div class="ml-64 p-8">
        <!-- page header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 bg-gradient-to-br from-slate-700 to-slate-900 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-pen-to-square text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Peralatan</h1>
                    <p class="text-gray-600 mt-1">Perbarui informasi peralatan yang sudah terdaftar</p>
                </div>
            </div>
        </div>

        <!-- tombol back -->
        <div class="mb-6">
            <nav class="flex items-center gap-2 text-sm">
                <a href="data.php" class="text-slate-700 hover:text-slate-900 font-semibold transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Data Peralatan
                </a>
            </nav>
        </div>

        <!-- form card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 max-w-4xl">
            <!-- card header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Form Edit Peralatan</h1>
                        <p class="text-slate-300 text-sm mt-1">Ubah data peralatan sesuai kebutuhan</p>
                    </div>
                </div>
            </div>

            <!-- card body -->
            <form method="POST" action="" class="p-8">
                <div class="space-y-6">
                    <!-- info box -->
                    <div class="bg-slate-50 border-l-4 border-slate-700 p-4 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-slate-700 text-xl mt-0.5"></i>
                            <div>
                                <p class="text-slate-800 text-sm font-semibold">Informasi</p>
                                <p class="text-slate-600 text-sm mt-1">Pastikan semua data yang diisi sudah benar sebelum menyimpan perubahan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- nama peralatan -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-screwdriver-wrench text-slate-700 mr-2"></i>
                            Nama Peralatan
                        </label>
                        <input type="text" name="nama_peralatan" placeholder="Masukkan nama peralatan"
                            value="<?php echo htmlspecialchars($data['nama_peralatan']) ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition">
                    </div>
                    <!-- jenis/kategori -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-folder text-slate-700 mr-2"></i>
                            Kategori Peralatan
                        </label>
                        <select name="jenis" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition bg-white">
                            <option value="">Pilih Kategori</option>
                            <option value="Komputer & Laptop" <?php echo ($data['jenis'] == 'Komputer & Laptop') ? 'selected' : ''; ?>>💻 Komputer & Laptop</option>
                            <option value="Perangkat Cetak & Scan" <?php echo ($data['jenis'] == 'Perangkat Cetak & Scan') ? 'selected' : ''; ?>>🖨️ Perangkat Cetak & Scan</option>
                            <option value="Jaringan" <?php echo ($data['jenis'] == 'Jaringan') ? 'selected' : ''; ?>>🌐 Jaringan</option>
                            <option value="Perangkat Keamanan" <?php echo ($data['jenis'] == 'Perangkat Keamanan') ? 'selected' : ''; ?>>📹 Perangkat Keamanan</option>
                            <option value="Lainnya" <?php echo ($data['jenis'] == 'Lainnya') ? 'selected' : ''; ?>>📦 Lainnya</option>
                        </select>
                    </div>
                    <!-- kondisi -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-clipboard-check text-slate-700 mr-2"></i>
                            Pilih Kondisi
                        </label>
                        <select name="kondisi" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition bg-white">
                            <option value="">Pilih Kondisi</option>
                            <option value="baik" <?php echo ($data['kondisi'] == 'baik') ? 'selected' : ''; ?>>✅ Baik</option>
                            <option value="rusak" <?php echo ($data['kondisi'] == 'rusak') ? 'selected' : ''; ?>>❌ Rusak</option>
                            <option value="dalam perbaikan" <?php echo ($data['kondisi'] == 'dalam perbaikan') ? 'selected' : ''; ?>>🔧 Dalam Perbaikan</option>
                        </select>
                    </div>
                    <!-- lokasi -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-location-dot text-slate-700 mr-2"></i>
                            Lokasi
                        </label>
                        <input type="text" name="lokasi" required
                            value="<?php echo htmlspecialchars($data['lokasi']) ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                            placeholder="Contoh: Gudang A, Lantai 2, dll">
                    </div>
                </div>

                <!-- form footer -->
                <div class="flex items-center justify-between gap-3 mt-8 pt-6 border-t border-gray-200">
                    <a href="data.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200 inline-flex items-center gap-2">
                        <i class="fa-solid fa-times"></i>
                        Batal
                    </a>
                    <button type="submit" name="update"
                        class="px-6 py-3 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-slate-950 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 inline-flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Additional Info Card -->
        <div class="mt-6 bg-gradient-to-r from-slate-50 to-gray-50 border border-slate-200 rounded-xl p-6 max-w-4xl">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-lightbulb text-slate-700 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-2">Tips Pengisian Form</h4>
                    <ul class="text-sm text-slate-700 space-y-1">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-slate-600 mt-0.5"></i>
                            <span>Pastikan nama peralatan ditulis dengan jelas dan spesifik</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-slate-600 mt-0.5"></i>
                            <span>Pilih kondisi yang sesuai dengan keadaan peralatan saat ini</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-slate-600 mt-0.5"></i>
                            <span>Lokasi harus detail agar mudah ditemukan saat dibutuhkan</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>

</html>