<?php
session_start();
include '../config.php';

$kategori_result = $conn->query("SELECT * FROM kategori_menu");

$total_result = $conn->query("SELECT COUNT(*) as total FROM menu");
$total_row = $total_result->fetch_assoc();
$total_menu = $total_row['total'];

$makanan_result = $conn->query("SELECT COUNT(*) as total FROM menu WHERE id_kategori = 1");
$makanan_row = $makanan_result->fetch_assoc();
$makanan_count = $makanan_row['total'];

$minuman_result = $conn->query("SELECT COUNT(*) as total FROM menu WHERE id_kategori = 2");
$minuman_row = $minuman_result->fetch_assoc();
$minuman_count = $minuman_row['total'];

$dessert_result = $conn->query("SELECT COUNT(*) as total FROM menu WHERE id_kategori = 3");
$dessert_row = $dessert_result->fetch_assoc();
$dessert_count = $dessert_row['total'];

$menu_display_result = $conn->query("
    SELECT m.*, k.nama_kategori 
    FROM menu m 
    LEFT JOIN kategori_menu k ON m.id_kategori = k.id_kategori 
    ORDER BY m.id_menu
");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_menu'])) {
    $nama_menu = $_POST['nama_menu'];
    $harga = $_POST['harga'];
    $id_kategori = $_POST['id_kategori'];
    
    $stmt = $conn->prepare("INSERT INTO menu (nama_menu, harga, id_kategori) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $nama_menu, $harga, $id_kategori);
    
    if ($stmt->execute()) {
        $success = "Menu berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan menu!";
    }
    header("Location: manajemen_menu.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_menu'])) {
    $id_menu = $_POST['id_menu'];
    $nama_menu = $_POST['nama_menu'];
    $harga = $_POST['harga'];
    $id_kategori = $_POST['id_kategori'];
    
    $stmt = $conn->prepare("UPDATE menu SET nama_menu = ?, harga = ?, id_kategori = ? WHERE id_menu = ?");
    $stmt->bind_param("siii", $nama_menu, $harga, $id_kategori, $id_menu);
    
    if ($stmt->execute()) {
        $success = "Menu berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate menu!";
    }
    header("Location: manajemen_menu.php");
    exit();
}

if (isset($_GET['hapus'])) {
    $id_menu = intval($_GET['hapus']);

    $stmt_del = $conn->prepare("DELETE FROM menu WHERE id_menu = ?");
    $stmt_del->bind_param("i", $id_menu);
    $stmt_del->execute();

    $max_result = $conn->query("SELECT MAX(id_menu) as max_id FROM menu");
    $max_row = $max_result->fetch_assoc();
    $next_ai = ($max_row && $max_row['max_id']) ? (intval($max_row['max_id']) + 1) : 1;
    $conn->query("ALTER TABLE menu AUTO_INCREMENT = " . $next_ai);

    header("Location: manajemen_menu.php");
    exit();
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $edit_result = $conn->query("
        SELECT m.*, k.nama_kategori 
        FROM menu m 
        LEFT JOIN kategori_menu k ON m.id_kategori = k.id_kategori 
        WHERE m.id_menu = $id_edit
    ");
    if ($edit_result->num_rows > 0) {
        $edit_data = $edit_result->fetch_assoc();
    }
}

$owner_result = $conn->query("SELECT * FROM users WHERE role = 'owner' LIMIT 1");
$owner = $owner_result->fetch_assoc();

if (!$owner) {
    $user_result = $conn->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $owner = $user_result->fetch_assoc();
}

if (!$owner) {
    $user_result = $conn->query("SELECT * FROM users LIMIT 1");
    $owner = $user_result->fetch_assoc();
}

$foto_display = 'https://ui-avatars.com/api/?name=' . urlencode($owner['nama'] ?? 'Owner') . '&background=B7A087&color=fff';
if (!empty($owner['profile_picture']) && file_exists($owner['profile_picture'])) {
    $foto_display = $owner['profile_picture'];
}

$kategori_result->data_seek(0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Menu - EasyResto Owner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'antique-white': '#F7EBDF',
                        'pale-taupe': '#B7A087',
                        'primary': '#B7A087',
                        'secondary': '#F7EBDF'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F7EBDF;
        }
        .sidebar {
            background: linear-gradient(to bottom, #B7A087, #8B7355);
        }
        .card {
            background: white;
            border: 1px solid #E5D9C8;
        }
        .btn-primary {
            background-color: #B7A087;
            color: white;
        }
        .btn-primary:hover {
            background-color: #8B7355;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
        }
    </style>
</head>
<body class="bg-antique-white">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 sidebar shadow-xl">
        <div class="flex items-center justify-center h-16 bg-pale-taupe">
            <div class="text-white">
                <h1 class="text-xl font-bold">EasyResto</h1>
                <p class="text-xs text-white opacity-90">Owner Panel</p>
            </div>
        </div>
        
        <nav class="mt-8">
            <a href="dashboard.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-chart-line w-6"></i>
                <span class="mx-3 font-medium">Dashboard</span>
            </a>
            <a href="laporan_penjualan.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-file-invoice-dollar w-6"></i>
                <span class="mx-3 font-medium">Laporan Penjualan</span>
            </a>
            <a href="manajemen_menu.php" class="flex items-center px-6 py-3 text-white bg-pale-taupe bg-opacity-40 border-l-4 border-white">
                <i class="fas fa-utensils w-6"></i>
                <span class="mx-3 font-medium">Manajemen Menu</span>
            </a>
            <a href="manajemen_pengguna.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-users w-6"></i>
                <span class="mx-3 font-medium">Manajemen Pengguna</span>
            </a>
            <a href="profil.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-user-cog w-6"></i>
                <span class="mx-3 font-medium">Profil</span>
            </a>
        </nav>

        <div class="absolute bottom-0 w-full p-4 bg-pale-taupe bg-opacity-80">
            <div class="flex items-center gap-3">
                <img src="<?= $foto_display ?>" class="w-10 h-10 rounded-full border-2 border-white object-cover">
                <div class="overflow-hidden text-white">
                    <p class="font-bold text-sm truncate leading-tight"><?= htmlspecialchars($owner['nama'] ?? 'Owner') ?></p>
                    <p class="text-xs opacity-90"><?= ucfirst($owner['role'] ?? 'Admin') ?></p>
                    <a href="logout.php" class="text-xs text-red-200 hover:text-white flex items-center gap-1 mt-1 transition-colors">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="ml-64">
        <header class="bg-white shadow-sm border-b border-pale-taupe">
            <div class="flex items-center justify-between px-8 py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Manajemen Menu</h1>
                    <p class="text-gray-600">Kelola menu dan harga makanan</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Selamat datang</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($owner['nama'] ?? 'Owner') ?></p>
                    </div>
                    <a href="profil.php" class="w-10 h-10 rounded-full flex items-center justify-center overflow-hidden border-2 border-pale-taupe">
                        <img src="<?= $foto_display ?>" alt="Profil" class="w-full h-full object-cover">
                    </a>
                </div>
            </div>
        </header>

        <main class="p-8">
            <?php
            $debug_total = $conn->query("SELECT COUNT(*) as total FROM menu")->fetch_assoc()['total'];
            ?>
            <!-- <div class="mb-4 p-4 bg-yellow-100 border border-yellow-300 rounded">
                <p class="text-sm">DEBUG: Total menu di database: <?php echo $debug_total; ?></p>
                <p class="text-sm">Total menu yang ditampilkan: <?php echo $total_menu; ?></p>
            </div> -->

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-pale-taupe to-amber-800 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium">Total Menu</p>
                            <p class="text-2xl font-bold"><?php echo $total_menu; ?></p>
                        </div>
                        <i class="fas fa-utensils text-2xl opacity-80"></i>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Menu Makanan</p>
                            <p class="text-2xl font-bold"><?php echo $makanan_count; ?></p>
                        </div>
                        <i class="fas fa-pizza-slice text-2xl opacity-80"></i>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Menu Minuman</p>
                            <p class="text-2xl font-bold"><?php echo $minuman_count; ?></p>
                        </div>
                        <i class="fas fa-cocktail text-2xl opacity-80"></i>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Menu Makanan Penutup</p>
                            <p class="text-2xl font-bold"><?php echo $dessert_count; ?></p>
                        </div>
                        <i class="fas fa-ice-cream text-2xl opacity-80"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Daftar Menu</h3>
                            <p class="text-sm text-gray-600">Semua menu yang tersedia di restoran (Total: <?php echo $total_menu; ?> menu)</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="openAddModal()" class="flex items-center px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Tambah Menu
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Menu</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($total_menu > 0): ?>
                                <?php while ($menu = $menu_display_result->fetch_assoc()): ?>
                                <tr class="hover:bg-pale-taupe hover:bg-opacity-10 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded">#<?php echo $menu['id_menu']; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $menu['nama_menu']; ?></div>
                                        <div class="text-xs text-gray-500 mt-1">ID: <?php echo $menu['id_menu']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                                            <?php 
                                            $kategori_nama = $menu['nama_kategori'];
                                            if ($kategori_nama == 'Makanan') {
                                                echo 'bg-green-100 text-green-800 border border-green-200';
                                            } elseif ($kategori_nama == 'Minuman') {
                                                echo 'bg-blue-100 text-blue-800 border border-blue-200';
                                            } elseif ($kategori_nama == 'Makanan Penutup') {
                                                echo 'bg-purple-100 text-purple-800 border border-purple-200';
                                            } else {
                                                echo 'bg-gray-100 text-gray-800 border border-gray-200';
                                            }
                                            ?>">
                                            <i class="fas 
                                                <?php 
                                                if ($kategori_nama == 'Makanan') {
                                                    echo 'fa-utensils';
                                                } elseif ($kategori_nama == 'Minuman') {
                                                    echo 'fa-glass-whiskey';
                                                } elseif ($kategori_nama == 'Makanan Penutup') {
                                                    echo 'fa-ice-cream';
                                                } else {
                                                    echo 'fa-question';
                                                }
                                                ?> 
                                                mr-1"></i>
                                            <?php echo $menu['nama_kategori']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">
                                            Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="openEditModal(
                                            <?php echo $menu['id_menu']; ?>, 
                                            '<?php echo addslashes($menu['nama_menu']); ?>',
                                            <?php echo $menu['harga']; ?>,
                                            <?php echo $menu['id_kategori']; ?>
                                        )" 
                                                class="text-blue-600 hover:text-blue-900 mr-4 transition-colors">
                                            <i class="fas fa-edit mr-1"></i>
                                            Edit
                                        </button>
                                        <button onclick="hapusMenu(<?php echo $menu['id_menu']; ?>)" 
                                                class="text-red-600 hover:text-red-900 transition-colors">
                                            <i class="fas fa-trash mr-1"></i>
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-utensils text-4xl text-gray-400 mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada menu</h3>
                                            <p class="text-gray-600 mb-4">Tambahkan menu pertama Anda untuk memulai.</p>
                                            <button onclick="openAddModal()" class="flex items-center px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                                <i class="fas fa-plus mr-2"></i>
                                                Tambah Menu Pertama
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_menu > 0): ?>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <div>
                            Menampilkan <span class="font-semibold"><?php echo $total_menu; ?></span> menu
                        </div>
                        <div class="text-gray-700">
                            Rata-rata harga: <span class="font-semibold">
                                Rp <?php 
                                // Query terpisah untuk rata-rata harga
                                $avg_result = $conn->query("SELECT AVG(harga) as avg_price FROM menu");
                                $avg_row = $avg_result->fetch_assoc();
                                $avg_price = $avg_row['avg_price'] ?? 0;
                                echo number_format($avg_price, 0, ',', '.');
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Tambah Menu Baru</h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form method="POST" onsubmit="return validateAddForm()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Menu *</label>
                        <input type="text" name="nama_menu" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors"
                               placeholder="Contoh: Nasi Goreng Spesial">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) *</label>
                        <input type="number" name="harga" required min="1000" step="500"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors"
                               placeholder="Contoh: 25000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                        <select name="id_kategori" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors">
                            <option value="">Pilih Kategori</option>
                            <?php 
                            $kategori_result->data_seek(0);
                            while ($kategori = $kategori_result->fetch_assoc()): ?>
                                <option value="<?php echo $kategori['id_kategori']; ?>">
                                    <?php echo $kategori['nama_kategori']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-8">
                    <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" name="tambah_menu" 
                            class="px-4 py-2 text-white bg-pale-taupe rounded-lg hover:bg-amber-800 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Edit Menu</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form method="POST" onsubmit="return validateEditForm()">
                <input type="hidden" name="id_menu" id="edit_id_menu">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Menu *</label>
                        <input type="text" name="nama_menu" id="edit_nama_menu" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors"
                               placeholder="Contoh: Nasi Goreng Spesial">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) *</label>
                        <input type="number" name="harga" id="edit_harga" required min="1000" step="500"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors"
                               placeholder="Contoh: 25000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                        <select name="id_kategori" id="edit_id_kategori" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors">
                            <option value="">Pilih Kategori</option>
                            <?php 
                            $kategori_result->data_seek(0);
                            while ($kategori = $kategori_result->fetch_assoc()): ?>
                                <option value="<?php echo $kategori['id_kategori']; ?>">
                                    <?php echo $kategori['nama_kategori']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-8">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" name="edit_menu" 
                            class="px-4 py-2 text-white bg-pale-taupe rounded-lg hover:bg-amber-800 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.getElementById('addModal').classList.add('flex');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('addModal').classList.remove('flex');
        }

        function openEditModal(id, nama, harga, kategori) {
            document.getElementById('edit_id_menu').value = id;
            document.getElementById('edit_nama_menu').value = nama;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('edit_id_kategori').value = kategori;
            
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        function hapusMenu(id) {
            if (confirm('Apakah Anda yakin ingin menghapus menu ini?\nTindakan ini tidak dapat dibatalkan.')) {
                window.location.href = 'manajemen_menu.php?hapus=' + id;
            }
        }

        function validateAddForm() {
            const namaMenu = document.querySelector('#addModal input[name="nama_menu"]').value;
            const harga = document.querySelector('#addModal input[name="harga"]').value;
            const kategori = document.querySelector('#addModal select[name="id_kategori"]').value;
            
            if (!namaMenu || !harga || !kategori) {
                alert('Semua field harus diisi!');
                return false;
            }
            
            if (harga < 1000) {
                alert('Harga minimal Rp 1.000');
                return false;
            }
            
            return true;
        }

        function validateEditForm() {
            const namaMenu = document.getElementById('edit_nama_menu').value;
            const harga = document.getElementById('edit_harga').value;
            const kategori = document.getElementById('edit_id_kategori').value;
            
            if (!namaMenu || !harga || !kategori) {
                alert('Semua field harus diisi!');
                return false;
            }
            
            if (harga < 1000) {
                alert('Harga minimal Rp 1.000');
                return false;
            }
            
            return true;
        }

        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.key === 'n') {
                event.preventDefault();
                openAddModal();
            }
            if (event.key === 'Escape') {
                closeAddModal();
                closeEditModal();
            }
        });

        <?php if ($edit_data): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openEditModal(
                <?php echo $edit_data['id_menu']; ?>,
                '<?php echo addslashes($edit_data['nama_menu']); ?>',
                <?php echo $edit_data['harga']; ?>,
                <?php echo $edit_data['id_kategori']; ?>
            );
        });
        <?php endif; ?>
    </script>
</body>
</html>