<?php
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$total_penjualan = $conn->query("SELECT SUM(total) as total FROM transaksi")->fetch_assoc()['total'] ?? 0;
$total_transaksi = $conn->query("SELECT COUNT(*) as total FROM transaksi")->fetch_assoc()['total'] ?? 0;
$total_menu = $conn->query("SELECT COUNT(*) as total FROM menu")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;

$recent_transactions = $conn->query("SELECT * FROM transaksi ORDER BY tanggal DESC LIMIT 5");

$cat_sales_query = $conn->query("
    SELECT k.nama_kategori, SUM(d.subtotal) as total
    FROM detail_transaksi d
    JOIN menu m ON d.id_menu = m.id_menu
    JOIN kategori_menu k ON m.id_kategori = k.id_kategori
    GROUP BY k.id_kategori
");
$cat_labels = [];
$cat_data = [];
while ($row = $cat_sales_query->fetch_assoc()) {
    $cat_labels[] = $row['nama_kategori'];
    $cat_data[] = $row['total'];
}

$daily_sales_query = $conn->query("
    SELECT DATE(tanggal) as tgl, SUM(total) as total 
    FROM transaksi 
    GROUP BY DATE(tanggal) 
    ORDER BY tanggal DESC 
    LIMIT 7
");
$daily_labels = [];
$daily_data = [];
$temp_daily = [];
while ($row = $daily_sales_query->fetch_assoc()) {
    $temp_daily[] = $row;
}
$temp_daily = array_reverse($temp_daily);
foreach ($temp_daily as $row) {
    $daily_labels[] = date('d M', strtotime($row['tgl']));
    $daily_data[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - EasyResto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'antique-white': '#F7EBDF',
                        'pale-taupe': '#B7A087',
                        'primary': '#B7A087',
                        'secondary': '#F7EBDF',
                        'dark-brown': '#5D4037'
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
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">
    <div class="w-64 sidebar-gradient text-white flex flex-col shadow-2xl transition-all duration-300">
        <div class="h-20 flex items-center justify-center font-bold text-2xl border-b border-white/10 tracking-wide">
            <i class="fas fa-utensils mr-3 opacity-80"></i> EasyResto
        </div>
        <nav class="flex-1 overflow-y-auto py-6 space-y-1">
            <a href="dashboard.php" class="flex items-center px-6 py-4 bg-white/20 border-l-4 border-white transition-all duration-200">
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

    <div class="flex-1 flex flex-col overflow-hidden bg-gray-50/50">
        <header class="bg-white shadow-sm z-10 px-8 py-5 flex justify-between items-center glass-effect sticky top-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Dashboard Overview</h2>
                <p class="text-sm text-gray-500">Selamat datang kembali, Admin</p>
            </div>
            <a href="edit_profil.php" class="flex items-center space-x-4 hover:bg-gray-50 p-2 rounded-lg transition-colors cursor-pointer group">
                <div class="flex flex-col text-right mr-2">
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-amber-800 transition-colors"><?php echo $_SESSION['nama']; ?></span>
                    <span class="text-xs text-pale-taupe font-medium uppercase tracking-wider">Administrator</span>
                </div>
                <!-- Profile Picture with fallback -->
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-green-500 uppercase tracking-wider mb-1">Total Pendapatan</p>
                            <h3 class="text-2xl font-extrabold text-gray-800">Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="p-3 rounded-xl bg-green-50 text-green-500 shadow-sm">
                            <i class="fas fa-wallet text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Total Transaksi</p>
                            <h3 class="text-2xl font-extrabold text-gray-800"><?php echo number_format($total_transaksi, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-50 text-blue-500 shadow-sm">
                            <i class="fas fa-shopping-bag text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-orange-500 uppercase tracking-wider mb-1">Total Menu</p>
                            <h3 class="text-2xl font-extrabold text-gray-800"><?php echo $total_menu; ?></h3>
                        </div>
                        <div class="p-3 rounded-xl bg-orange-50 text-orange-500 shadow-sm">
                            <i class="fas fa-utensils text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-purple-500 uppercase tracking-wider mb-1">Total Pengguna</p>
                            <h3 class="text-2xl font-extrabold text-gray-800"><?php echo $total_users; ?></h3>
                        </div>
                        <div class="p-3 rounded-xl bg-purple-50 text-purple-500 shadow-sm">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-pale-taupe"></i> Penjualan per Kategori
                    </h3>
                    <div class="relative h-64">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-pale-taupe"></i> Tren Penjualan (7 Hari Terakhir)
                    </h3>
                    <div class="relative h-64">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-800">Transaksi Terbaru</h3>
                    <a href="manajemen_transaksi.php" class="text-sm font-semibold text-pale-taupe hover:text-amber-800 flex items-center transition-colors">
                        Lihat Semua <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                <th class="py-4 px-6 md:w-16">ID</th>
                                <th class="py-4 px-6">Pelanggan</th>
                                <th class="py-4 px-6">Tanggal</th>
                                <th class="py-4 px-6 text-right">Total</th>
                                <th class="py-4 px-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($recent_transactions->num_rows > 0): ?>
                                <?php while($row = $recent_transactions->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                                        <td class="py-4 px-6 text-sm font-medium text-gray-900">#<?php echo $row['id_transaksi']; ?></td>
                                        <td class="py-4 px-6 text-sm text-gray-600 font-medium"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                        <td class="py-4 px-6 text-sm text-gray-500">
                                            <div class="flex flex-col">
                                                <span><?php echo date('d M Y', strtotime($row['tanggal'])); ?></span>
                                                <span class="text-xs text-gray-400"><?php echo date('H:i', strtotime($row['tanggal'])); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-sm font-bold text-gray-800 text-right">Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Selesai
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500 italic">Belum ada transaksi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        const salesCtx = document.getElementById('salesChart').getContext('2d');

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($cat_data); ?>,
                    backgroundColor: [
                        '#A0C4FF', 
                        '#FFADAD', 
                        '#CAFFBF', 
                        '#FDFFB6', 
                        '#BDB2FF', 
                        '#FFD6A5', 
                        '#9BF6FF'
                    ],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, pointStyle: 'circle' } }
                },
                cutout: '70%',
            }
        });

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($daily_labels); ?>,
                datasets: [{
                    label: 'Pendapatan Harian',
                    data: <?php echo json_encode($daily_data); ?>,
                    borderColor: '#A0C4FF', 
                    backgroundColor: 'rgba(160, 196, 255, 0.2)', 
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#A0C4FF',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#333',
                        bodyColor: '#333',
                        borderColor: '#E5E7EB',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#F3F4F6' },
                        ticks: { font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    </script>
</body>
</html>
