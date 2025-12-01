<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_path = $_SERVER['PHP_SELF'];

// ambil folder setelah /pelaporan/
$folder = '';
if (strpos($current_path, '/pelaporan/') !== false) {
    $parts = explode('/', $current_path);
    $index = array_search('pelaporan', $parts);
    $folder = isset($parts[$index + 1]) ? $parts[$index + 1] : '';
}
?>

<!-- includes/sidebar.php -->
<div class="w-64 bg-gradient-to-b from-slate-900 to-slate-800 text-white flex flex-col fixed h-screen top-0 left-0 shadow-2xl">
    <!-- Profile Section -->
    <div class="px-6 py-6 border-b border-slate-700/50">
        <div class="flex flex-col items-center">
            <div class="relative">
                <div class="w-20 h-20 rounded-full overflow-hidden ring-4 ring-slate-700/50 shadow-lg">
                    <img src="https://i.pinimg.com/1200x/7b/a6/ef/7ba6efbd6d301afe566aa104d53c2455.jpg"
                        alt="profile" class="w-full h-full object-cover">
                </div>
                <div class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-2 border-slate-900"></div>
            </div>
            <h2 class="mt-4 text-lg font-bold text-white"><?php echo $_SESSION['nama']; ?></h2>
            <p class="text-xs text-slate-400 mt-0.5"><?php echo $_SESSION['departemen']; ?></p>
            <span class="mt-2 px-3 py-1 rounded-full text-xs font-semibold <?php echo $_SESSION['role'] == 'admin' ? 'bg-red-500/20 text-red-300' : 'bg-blue-500/20 text-blue-300'; ?>">
                <?php echo $_SESSION['role'] == 'admin' ? 'Administrator' : 'Pelapor'; ?>
            </span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto">
        <ul class="space-y-2">
            <!-- Home -->
            <li>
                <a href="<?php echo BASE_URL; ?><?php echo $_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'pelapor/dashboard.php'; ?>"
                    class="group flex items-center gap-3 px-4 py-3 rounded-lg 
                        <?php echo ($current_page == 'dashboard.php')
                            ? 'bg-blue-600 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'; ?> 
                        transition-all duration-200">
                    <i class="fa-solid fa-house text-lg group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>

            <?php if ($_SESSION['role'] == 'admin'): ?>
                <!-- Menu untuk Admin -->
                
                <!-- Data Pengguna -->
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/users/data.php"
                        class="group flex items-center gap-3 px-4 py-3 rounded-lg 
                            <?php echo (strpos($current_path, '/users/') !== false)
                                ? 'bg-blue-600 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'; ?> 
                            transition-all duration-200">
                        <i class="fa-solid fa-users text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Data Pengguna</span>
                    </a>
                </li>

                <!-- Data Peralatan -->
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/peralatan/data.php"
                        class="group flex items-center gap-3 px-4 py-3 rounded-lg 
                            <?php echo (strpos($current_path, '/peralatan/') !== false)
                                ? 'bg-blue-600 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'; ?> 
                            transition-all duration-200">
                        <i class="fa-solid fa-screwdriver-wrench text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Data Peralatan</span>
                    </a>
                </li>

                <!-- Laporan Kerusakan (Admin) -->
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/laporan/data.php"
                        class="group flex items-center gap-3 px-4 py-3 rounded-lg 
                            <?php echo (strpos($current_path, '/laporan/') !== false)
                                ? 'bg-blue-600 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'; ?> 
                            transition-all duration-200">
                        <i class="fa-solid fa-clipboard-list text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Kelola Laporan</span>
                    </a>
                </li>

            <?php else: ?>
                <!-- Menu untuk Pelapor -->
                
                <!-- Buat Laporan -->
                <li>
                    <a href="<?php echo BASE_URL; ?>pelapor/laporan/buat.php"
                        class="group flex items-center gap-3 px-4 py-3 rounded-lg 
                            <?php echo ($current_page == 'buat.php')
                                ? 'bg-blue-600 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'; ?> 
                            transition-all duration-200">
                        <i class="fa-solid fa-plus-circle text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Buat Laporan</span>
                    </a>
                </li>

                <!-- Laporan Saya -->
                <li>
                    <a href="<?php echo BASE_URL; ?>pelapor/laporan/data.php"
                        class="group flex items-center gap-3 px-4 py-3 rounded-lg 
                            <?php echo ($current_page == 'data.php' && strpos($current_path, '/pelapor/') !== false)
                                ? 'bg-blue-600 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'; ?> 
                            transition-all duration-200">
                        <i class="fa-solid fa-clipboard-list text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Laporan Saya</span>
                    </a>
                </li>

            <?php endif; ?>

        </ul>
    </nav>

    <!-- Logout Button -->
    <div class="p-4 border-t border-slate-700/50">
        <a href="<?php echo BASE_URL; ?>auth/logout.php" 
           onclick="return confirm('Yakin ingin keluar?')"
           class="group flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-red-600 hover:text-white transition-all duration-200 shadow-sm">
            <i class="fa-solid fa-right-from-bracket text-lg group-hover:translate-x-1 transition-transform"></i>
            <span class="font-medium">Keluar</span>
        </a>
    </div>
</div>