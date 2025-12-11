<?php
include 'config.php';

// Ambil data kategori untuk dropdown
$kategori_result = $conn->query("SELECT * FROM kategori_menu");

// Ambil data menu
$menu_result = $conn->query("
    SELECT m.*, k.nama_kategori 
    FROM menu m 
    LEFT JOIN kategori_menu k ON m.id_kategori = k.id_kategori 
    ORDER BY m.id_menu
");

// Tambah menu baru
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

// Hapus menu
if (isset($_GET['hapus'])) {
    $id_menu = $_GET['hapus'];
    $conn->query("DELETE FROM menu WHERE id_menu = $id_menu");
    header("Location: manajemen_menu.php");
    exit();
}

// Hitung statistik berdasarkan data aktual
$total_menu = $menu_result->num_rows;
$makanan_count = $conn->query("SELECT COUNT(*) as total FROM menu WHERE id_kategori = 1")->fetch_assoc()['total'];
$minuman_count = $conn->query("SELECT COUNT(*) as total FROM menu WHERE id_kategori = 2")->fetch_assoc()['total'];
$dessert_count = $conn->query("SELECT COUNT(*) as total FROM menu WHERE id_kategori = 3")->fetch_assoc()['total'];

// Reset pointer untuk loop menu
$menu_result->data_seek(0);
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
    <div class="fixed inset-y-0 left-0 w-64 sidebar shadow-xl flex flex-col justify-between">
        <div>
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
        </div>
        
        <div class="p-4 bg-pale-taupe bg-opacity-80">
            <div class="flex items-center gap-3">
                <i class="fas fa-user-circle text-2xl text-white"></i>
                <div class="text-white">
                    <p class="font-bold text-sm">Owner</p>
                    <p class="text-xs opacity-90">Administrator</p>
                    <a href="../logout.php" class="text-xs text-red-200 hover:text-white flex items-center gap-1 mt-1 transition-colors">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-pale-taupe">
            <div class="flex items-center justify-between px-8 py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Manajemen Menu</h1>
                    <p class="text-gray-600">Kelola menu dan harga makanan</p>
                </div>
                <button onclick="openAddModal()" class="flex items-center px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Menu
                </button>
            </div>
        </header>

        <main class="p-8">
            <!-- Stats -->
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
                            <p class="text-purple-100 text-sm font-medium">Menu Dessert</p>
                            <p class="text-2xl font-bold"><?php echo $dessert_count; ?></p>
                        </div>
                        <i class="fas fa-ice-cream text-2xl opacity-80"></i>
                    </div>
                </div>
            </div>

            <!-- Menu Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Daftar Menu</h3>
                            <p class="text-sm text-gray-600">Semua menu yang tersedia di restoran (Total: <?php echo $total_menu; ?> menu)</p>
                        </div>
                        <div class="text-sm text-gray-600">
                            <button onclick="exportMenu()" class="flex items-center px-3 py-1 text-sm text-green-600 hover:text-green-800 border border-green-300 rounded-lg hover:bg-green-50 transition-colors">
                                <i class="fas fa-file-export mr-1"></i>
                                Export
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
                                <?php while ($menu = $menu_result->fetch_assoc()): ?>
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
                                            <?php echo $menu['nama_kategori'] == 'Makanan' ? 'bg-green-100 text-green-800 border border-green-200' : 
                                                  ($menu['nama_kategori'] == 'Minuman' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200'); ?>">
                                            <i class="fas 
                                                <?php echo $menu['nama_kategori'] == 'Makanan' ? 'fa-utensils' : 
                                                      ($menu['nama_kategori'] == 'Minuman' ? 'fa-glass-whiskey' : 'fa-ice-cream'); ?> 
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
                                        <button onclick="editMenu(<?php echo $menu['id_menu']; ?>)" 
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
                                $avg_price = $conn->query("SELECT AVG(harga) as avg_price FROM menu")->fetch_assoc()['avg_price'];
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

    <!-- Add Menu Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Tambah Menu Baru</h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form method="POST" onsubmit="return validateForm()">
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

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.getElementById('addModal').classList.add('flex');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('addModal').classList.remove('flex');
        }

        function editMenu(id) {
            // Arahkan ke halaman edit atau tampilkan modal edit
            window.location.href = 'edit_menu.php?id=' + id;
            // Atau bisa juga dengan modal:
            // alert('Fitur edit untuk menu ID: ' + id + ' akan diimplementasikan');
        }

        function hapusMenu(id) {
            if (confirm('Apakah Anda yakin ingin menghapus menu ini?\nTindakan ini tidak dapat dibatalkan.')) {
                window.location.href = 'manajemen_menu.php?hapus=' + id;
            }
        }

        function exportMenu() {
            const totalMenu = <?php echo $total_menu; ?>;
            const makanan = <?php echo $makanan_count; ?>;
            const minuman = <?php echo $minuman_count; ?>;
            const dessert = <?php echo $dessert_count; ?>;
            
            alert(`Ekspor Data Menu:\n\nTotal Menu: ${totalMenu}\nMakanan: ${makanan}\nMinuman: ${minuman}\nDessert: ${dessert}\n\nData akan diexport dalam format CSV.`);
        }

        function validateForm() {
            const namaMenu = document.querySelector('input[name="nama_menu"]').value;
            const harga = document.querySelector('input[name="harga"]').value;
            const kategori = document.querySelector('select[name="id_kategori"]').value;
            
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addModal');
            if (event.target === modal) {
                closeAddModal();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            // Ctrl + N untuk tambah menu baru
            if (event.ctrlKey && event.key === 'n') {
                event.preventDefault();
                openAddModal();
            }
            // Escape untuk tutup modal
            if (event.key === 'Escape') {
                closeAddModal();
            }
        });
    </script>
</body>
</html>