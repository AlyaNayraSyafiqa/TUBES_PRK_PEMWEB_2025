<?php
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$transactions = $conn->query("SELECT * FROM transaksi ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi - EasyResto Admin</title>
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
        function toggleDetail(id) {
            const row = document.getElementById('detail-' + id);
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        }
    </script>
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .sidebar-gradient {
            background: #B7A087; /* Solid color instead of gradient */
        }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">
    <!-- Sidebar -->
    <div class="w-64 sidebar-gradient text-white flex flex-col shadow-2xl transition-all duration-300">
        <div class="h-20 flex items-center justify-center font-bold text-2xl border-b border-white/10 tracking-wide">
            <i class="fas fa-utensils mr-3 opacity-80"></i> EasyResto
        </div>
        <nav class="flex-1 overflow-y-auto py-6 space-y-1">
            <a href="dashboard.php" class="flex items-center px-6 py-4 hover:bg-white/10 hover:translate-x-1 transition-all duration-200 text-white/90">
                <i class="fas fa-chart-pie w-6 text-lg"></i>
                <span class="mx-3 font-medium">Dashboard</span>
            </a>
            <a href="manajemen_pengguna.php" class="flex items-center px-6 py-4 hover:bg-white/10 hover:translate-x-1 transition-all duration-200 text-white/90">
                <i class="fas fa-users w-6 text-lg"></i>
                <span class="mx-3 font-medium">Kelola Pengguna</span>
            </a>
            <a href="manajemen_menu.php" class="flex items-center px-6 py-4 hover:bg-white/10 hover:translate-x-1 transition-all duration-200 text-white/90">
                <i class="fas fa-book-open w-6 text-lg"></i>
                <span class="mx-3 font-medium">Kelola Menu</span>
            </a>
            <a href="manajemen_transaksi.php" class="flex items-center px-6 py-4 bg-white/20 border-l-4 border-white transition-all duration-200">
                <i class="fas fa-receipt w-6 text-lg"></i>
                <span class="mx-3 font-medium">Transaksi</span>
            </a>
            <a href="laporan_penjualan.php" class="flex items-center px-6 py-4 hover:bg-white/10 hover:translate-x-1 transition-all duration-200 text-white/90">
                <i class="fas fa-file-invoice-dollar w-6 text-lg"></i>
                <span class="mx-3 font-medium">Laporan</span>
            </a>

        </nav>
        <div class="p-6 border-t border-white/10">
            <a href="../logout.php" class="flex items-center justify-center px-4 py-3 bg-red-500/80 hover:bg-red-600 rounded-xl shadow-lg transition-all duration-200 transform hover:scale-105 backdrop-blur-sm">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden bg-gray-50/50">
        <header class="bg-white shadow-sm z-10 px-8 py-5 flex justify-between items-center glass-effect sticky top-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Transaksi</h2>
                <p class="text-sm text-gray-500">Pantau dan kelola transaksi harian</p>
            </div>
            <a href="edit_profil.php" class="flex items-center space-x-4 hover:bg-gray-50 p-2 rounded-lg transition-colors cursor-pointer group">
                <div class="flex flex-col text-right mr-2">
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-amber-800 transition-colors"><?php echo $_SESSION['nama']; ?></span>
                    <span class="text-xs text-pale-taupe font-medium uppercase tracking-wider">Administrator</span>
                </div>
                <div class="w-12 h-12 rounded-full bg-pale-taupe flex items-center justify-center text-white font-bold text-lg shadow-md ring-2 ring-offset-2 ring-pale-taupe overflow-hidden">
                    <?php if (!empty($_SESSION['profile_picture']) && file_exists('../' . $_SESSION['profile_picture'])): ?>
                        <img src="../<?php echo $_SESSION['profile_picture']; ?>" alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo substr($_SESSION['nama'], 0, 1); ?>
                    <?php endif; ?>
                </div>
            </a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-8 scroll-smooth">
            <div class="bg-white rounded-xl shadow p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Riwayat Transaksi</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="py-3 border-b text-gray-600">ID</th>
                                <th class="py-3 border-b text-gray-600">Pelanggan</th>
                                <th class="py-3 border-b text-gray-600">Tanggal</th>
                                <th class="py-3 border-b text-gray-600">Total</th>
                                <th class="py-3 border-b text-gray-600">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($trans = $transactions->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 border-b">
                                <td class="py-3">#<?php echo $trans['id_transaksi']; ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($trans['nama_pelanggan']); ?></td>
                                <td class="py-3"><?php echo date('d/m/Y H:i', strtotime($trans['tanggal'])); ?></td>
                                <td class="py-3 font-semibold">Rp <?php echo number_format($trans['total'], 0, ',', '.'); ?></td>
                                <td class="py-3">
                                    <button onclick="toggleDetail(<?php echo $trans['id_transaksi']; ?>)" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                </td>
                            </tr>
                            <tr id="detail-<?php echo $trans['id_transaksi']; ?>" class="hidden bg-gray-50">
                                <td colspan="5" class="p-4">
                                    <div class="pl-4 border-l-2 border-pale-taupe">
                                        <h4 class="font-semibold text-sm text-gray-700 mb-2">Detail Pesanan:</h4>
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr>
                                                    <th class="text-left pb-2">Menu</th>
                                                    <th class="text-left pb-2">Jumlah</th>
                                                    <th class="text-left pb-2">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $details = $conn->query("
                                                    SELECT d.*, m.nama_menu 
                                                    FROM detail_transaksi d 
                                                    JOIN menu m ON d.id_menu = m.id_menu 
                                                    WHERE d.id_transaksi = " . $trans['id_transaksi']
                                                );
                                                while($det = $details->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td class="py-1 text-gray-600"><?php echo htmlspecialchars($det['nama_menu']); ?></td>
                                                    <td class="py-1 text-gray-600"><?php echo $det['jumlah']; ?>x</td>
                                                    <td class="py-1 text-gray-600">Rp <?php echo number_format($det['subtotal'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                        <div class="mt-2 text-sm text-gray-600">
                                            <p>Subtotal: Rp <?php echo number_format($trans['subtotal'], 0, ',', '.'); ?></p>
                                            <p>PPN (10%): Rp <?php echo number_format($trans['ppn'], 0, ',', '.'); ?></p>
                                            <p>Service: Rp <?php echo number_format($trans['service'], 0, ',', '.'); ?></p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
