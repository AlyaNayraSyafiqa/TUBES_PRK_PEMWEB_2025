<?php
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$laporan = $conn->query("SELECT * FROM laporan_penjualan ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - EasyResto Admin</title>
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
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .sidebar-gradient {
            background: #B7A087; /* Solid color instead of gradient */
        }
        @media print {
            .no-print, .no-print * {
                display: none !important;
                height: 0 !important;
                width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                visibility: hidden !important;
            }
            
            html, body {
                height: auto !important;
                overflow: visible !important;
                background-color: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body > div {
                display: block !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }

            main {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                overflow: visible !important;
            }

            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            
            .shadow-xl, .shadow-lg, .shadow-md, .shadow-sm, .shadow {
                box-shadow: none !important;
                border: none !important;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color: black !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">
    <!-- Sidebar -->
    <div class="w-64 sidebar-gradient text-white flex flex-col shadow-2xl transition-all duration-300 no-print">
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
            <a href="manajemen_transaksi.php" class="flex items-center px-6 py-4 hover:bg-white/10 hover:translate-x-1 transition-all duration-200 text-white/90">
                <i class="fas fa-receipt w-6 text-lg"></i>
                <span class="mx-3 font-medium">Transaksi</span>
            </a>
            <a href="laporan_penjualan.php" class="flex items-center px-6 py-4 bg-white/20 border-l-4 border-white transition-all duration-200">
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
    <div class="flex-1 flex flex-col overflow-hidden bg-gray-50/50 content-wrapper">
        <header class="bg-white shadow-sm z-10 px-8 py-5 flex justify-between items-center glass-effect sticky top-0 no-print">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h2>
                <p class="text-sm text-gray-500">Rekapitulasi penjualan restoran</p>
            </div>
            <a href="edit_profil.php" class="flex items-center space-x-4 hover:bg-gray-50 p-2 rounded-lg transition-colors cursor-pointer group">
                <div class="flex flex-col text-right mr-2">
                    <span class="text-sm font-semibold text-gray-700"><?php echo $_SESSION['nama']; ?></span>
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
                <div class="flex justify-between items-center mb-6 no-print">
                    <h3 class="text-lg font-semibold text-gray-800">Detail Penjualan</h3>
                    <button onclick="window.print()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded shadow transition-colors">
                        <i class="fas fa-print mr-2"></i> Cetak Laporan
                    </button>
                </div>
                
                <div class="hidden print:block mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-800">Laporan Penjualan EasyResto</h1>
                    <p class="text-gray-600">Dicetak pada: <?php echo date('d/m/Y H:i'); ?></p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="py-3 border-b text-gray-600">Tgl</th>
                                <th class="py-3 border-b text-gray-600">Transaksi</th>
                                <th class="py-3 border-b text-gray-600">Pelanggan</th>
                                <th class="py-3 border-b text-gray-600">Menu</th>
                                <th class="py-3 border-b text-gray-600">Kategori</th>
                                <th class="py-3 border-b text-gray-600">Qty</th>
                                <th class="py-3 border-b text-gray-600">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($laporan->num_rows > 0): ?>
                                <?php while($row = $laporan->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 border-b">
                                    <td class="py-3 text-sm"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td class="py-3 text-sm">#<?php echo $row['id_transaksi']; ?></td>
                                    <td class="py-3 text-sm"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                    <td class="py-3 text-sm"><?php echo htmlspecialchars($row['nama_menu']); ?></td>
                                    <td class="py-3 text-sm"><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                    <td class="py-3 text-sm"><?php echo $row['jumlah']; ?></td>
                                    <td class="py-3 text-sm font-semibold">Rp <?php echo number_format($row['total_permenu'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-4 text-center text-gray-500">Tidak ada data laporan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
