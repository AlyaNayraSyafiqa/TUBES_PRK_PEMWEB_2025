<?php
include 'config.php';

$admin_id = $_SESSION['id_user'];
$admin = $conn->query("SELECT * FROM users WHERE id_user = $admin_id")->fetch_assoc();
$foto_profil = !empty($admin['profile_picture']) ? '../' . $admin['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin['nama'] ?? 'Admin');

// Hak akses per role
$permissions = [
    'owner' => [
        'name' => 'Owner',
        'color' => 'yellow',
        'icon' => 'fa-crown',
        'access' => ['Dashboard', 'Laporan Penjualan', 'Manajemen Menu', 'Manajemen Pengguna', 'Profil', 'Semua Hak Akses']
    ],
    'admin' => [
        'name' => 'Admin',
        'color' => 'green',
        'icon' => 'fa-user-shield',
        'access' => ['Dashboard', 'Manajemen Pengguna', 'Manajemen Menu', 'Manajemen Transaksi', 'Laporan Penjualan', 'Manajemen Hak Akses', 'Profil']
    ],
    'kasir' => [
        'name' => 'Kasir',
        'color' => 'purple',
        'icon' => 'fa-cash-register',
        'access' => ['Transaksi', 'Riwayat Transaksi', 'Laporan Harian', 'Profil']
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Hak Akses - EasyResto Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>tailwind.config={theme:{extend:{colors:{'antique-white':'#F7EBDF','pale-taupe':'#B7A087','primary':'#B7A087','secondary':'#F7EBDF'}}}}</script>
    <style>body{background-color:#F7EBDF}.sidebar{background:linear-gradient(to bottom,#B7A087,#8B7355)}.btn-primary{background-color:#B7A087;color:white}.btn-primary:hover{background-color:#8B7355}</style>
</head>
<body class="bg-antique-white">
    <div class="fixed inset-y-0 left-0 w-64 sidebar shadow-xl flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-center h-16 bg-pale-taupe"><div class="text-white text-center"><h1 class="text-xl font-bold">EasyResto</h1><p class="text-xs opacity-90">Admin Panel</p></div></div>
            <nav class="mt-8">
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-chart-line w-6"></i><span class="mx-3">Dashboard</span></a>
                <a href="manajemen_pengguna.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-users w-6"></i><span class="mx-3">Manajemen Pengguna</span></a>
                <a href="manajemen_menu.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-utensils w-6"></i><span class="mx-3">Manajemen Menu</span></a>
                <a href="manajemen_transaksi.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-cash-register w-6"></i><span class="mx-3">Manajemen Transaksi</span></a>
                <a href="laporan_penjualan.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-file-invoice-dollar w-6"></i><span class="mx-3">Laporan Penjualan</span></a>
                <a href="manajemen_hak_akses.php" class="flex items-center px-6 py-3 text-white bg-pale-taupe bg-opacity-40 border-l-4 border-white"><i class="fas fa-user-shield w-6"></i><span class="mx-3">Manajemen Hak Akses</span></a>
                <a href="profil.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-user-cog w-6"></i><span class="mx-3">Profil</span></a>
            </nav>
        </div>
        <div class="p-4 bg-pale-taupe bg-opacity-80"><div class="flex items-center gap-3"><img src="<?=$foto_profil?>" class="w-10 h-10 rounded-full border-2 border-white object-cover"><div class="text-white"><p class="font-bold text-sm truncate"><?=htmlspecialchars($_SESSION['nama'])?></p><p class="text-xs opacity-90">Role: Admin</p><a href="../logout.php" class="text-xs text-red-200 hover:text-white"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div></div>
    </div>

    <div class="ml-64">
        <header class="bg-white shadow-sm border-b border-pale-taupe"><div class="px-8 py-4"><h1 class="text-2xl font-bold text-gray-800">Manajemen Hak Akses</h1><p class="text-gray-600">Konfigurasi hak akses untuk setiap peran pengguna</p></div></header>
        <main class="p-8">
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <i class="fas fa-info-circle text-pale-taupe text-xl"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Informasi Hak Akses</h3>
                </div>
                <p class="text-gray-600 text-sm">Sistem EasyResto memiliki 3 level pengguna dengan hak akses berbeda. Setiap pengguna hanya dapat mengakses halaman sesuai dengan peran yang ditetapkan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach($permissions as $role => $data): ?>
                <div class="bg-white rounded-xl shadow-lg border overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="bg-gradient-to-r from-<?=$data['color']?>-500 to-<?=$data['color']?>-600 p-6 text-white">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas <?=$data['icon']?> text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold"><?=$data['name']?></h3>
                                <p class="text-sm opacity-90"><?=count($data['access'])?> Hak Akses</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <h4 class="text-sm font-semibold text-gray-600 mb-3">MENU YANG DAPAT DIAKSES:</h4>
                        <ul class="space-y-2">
                            <?php foreach($data['access'] as $access): ?>
                            <li class="flex items-center gap-2 text-sm text-gray-700">
                                <i class="fas fa-check-circle text-<?=$data['color']?>-500"></i>
                                <span><?=$access?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-xl shadow-sm border mt-8 overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50"><h3 class="text-lg font-semibold text-gray-800">Matriks Hak Akses</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Fitur</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600"><i class="fas fa-crown text-yellow-500 mr-1"></i>Owner</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600"><i class="fas fa-user-shield text-green-500 mr-1"></i>Admin</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600"><i class="fas fa-cash-register text-purple-500 mr-1"></i>Kasir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php 
                            $features = ['Dashboard', 'Manajemen Pengguna', 'Manajemen Menu', 'Manajemen Transaksi', 'Laporan Penjualan', 'Manajemen Hak Akses', 'Transaksi Kasir', 'Profil'];
                            $owner_access = [1,1,1,0,1,0,0,1];
                            $admin_access = [1,1,1,1,1,1,0,1];
                            $kasir_access = [0,0,0,0,0,0,1,1];
                            foreach($features as $i => $feature): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900"><?=$feature?></td>
                                <td class="px-6 py-3 text-center"><?=$owner_access[$i]?'<i class="fas fa-check text-green-500"></i>':'<i class="fas fa-times text-red-400"></i>'?></td>
                                <td class="px-6 py-3 text-center"><?=$admin_access[$i]?'<i class="fas fa-check text-green-500"></i>':'<i class="fas fa-times text-red-400"></i>'?></td>
                                <td class="px-6 py-3 text-center"><?=$kasir_access[$i]?'<i class="fas fa-check text-green-500"></i>':'<i class="fas fa-times text-red-400"></i>'?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
