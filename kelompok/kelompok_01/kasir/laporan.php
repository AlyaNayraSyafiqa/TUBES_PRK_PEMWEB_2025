<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kasir') {
    header("Location: ../login.php");
    exit();
}

$jenis_laporan = isset($_GET['jenis']) ? $_GET['jenis'] : 'harian';
$tanggal_input = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$bulan_input   = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_input   = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$judul_laporan = "";
$query_where = "";

if ($jenis_laporan == 'harian') {
    $judul_laporan = "Laporan Harian: " . date('d F Y', strtotime($tanggal_input));
    $query_where = "DATE(t.tanggal) = '$tanggal_input'";
} elseif ($jenis_laporan == 'bulanan') {
    $judul_laporan = "Laporan Bulan: " . date('F', mktime(0, 0, 0, $bulan_input, 10)) . " " . $tahun_input;
    $query_where = "MONTH(t.tanggal) = '$bulan_input' AND YEAR(t.tanggal) = '$tahun_input'";
} elseif ($jenis_laporan == 'tahunan') {
    $judul_laporan = "Laporan Tahun: " . $tahun_input;
    $query_where = "YEAR(t.tanggal) = '$tahun_input'";
}

$sql = "
    SELECT m.nama_menu, SUM(dt.jumlah) as total_terjual, SUM(dt.subtotal) as total_pendapatan
    FROM detail_transaksi dt
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
    JOIN menu m ON dt.id_menu = m.id_menu
    WHERE $query_where
    GROUP BY m.id_menu
    ORDER BY total_terjual DESC
";

$laporan = $conn->query($sql);
$foto = !empty($_SESSION['profile_picture']) ? '../'.$_SESSION['profile_picture'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['nama']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - EasyResto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'antique-white': '#F7EBDF',
                        'pale-taupe': '#B7A087',
                        'dark-taupe': '#8B7355',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #F7EBDF; }
        .sidebar { background: linear-gradient(to bottom, #B7A087, #8B7355); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #B7A087; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #8B7355; }
        input:focus, select:focus, button:focus {
            outline: none !important; box-shadow: none !important; border-color: #B7A087 !important;
        }
    </style>
</head>
<body class="bg-antique-white h-screen flex overflow-hidden font-sans text-gray-800">

    <div class="w-64 sidebar shadow-xl flex flex-col justify-between z-20 flex-shrink-0">
        <div>
            <div class="h-16 flex items-center justify-center bg-pale-taupe">
                <div class="text-white text-center">
                    <h1 class="text-xl font-bold">EasyResto</h1>
                    <p class="text-xs text-white opacity-90">Cashier Panel</p>
                </div>
            </div>
            
            <nav class="mt-8">
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-cash-register w-6"></i>
                    <span class="mx-3 font-medium">Transaksi</span>
                </a>
                <a href="riwayat.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-history w-6"></i>
                    <span class="mx-3 font-medium">Riwayat</span>
                </a>
                <a href="laporan.php" class="flex items-center px-6 py-3 text-white bg-pale-taupe bg-opacity-40 border-l-4 border-white transition-all">
                    <i class="fas fa-chart-line w-6"></i>
                    <span class="mx-3 font-medium">Laporan</span>
                </a>
                <a href="profil_kasir.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-user-cog w-6"></i>
                    <span class="mx-3 font-medium">Profil</span>
                </a>
            </nav>
        </div>
        
        <div class="p-4 bg-pale-taupe bg-opacity-80">
            <div class="flex items-center gap-3">
                <img src="<?= $foto ?>" class="w-10 h-10 rounded-full border-2 border-white object-cover">
                <div class="overflow-hidden text-white">
                    <p class="font-bold text-sm truncate leading-tight"><?= htmlspecialchars($_SESSION['nama']) ?></p>
                    <p class="text-xs opacity-90">Role: Kasir</p>
                    <a href="../logout.php" class="text-xs text-red-200 hover:text-white flex items-center gap-1 mt-1 transition-colors">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="bg-white shadow-sm border-b border-[#E5D9C8] flex-shrink-0 px-8 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h1>
                <p class="text-gray-500 text-sm mt-1">Analisis penjualan menu (Harian/Bulanan/Tahunan)</p>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="bg-white rounded-xl shadow-sm border border-[#E5D9C8] p-6 mb-8">
                <form method="GET" class="flex flex-wrap items-end gap-5">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Jenis Laporan</label>
                        <div class="relative">
                            <i class="fas fa-file-alt absolute left-3 top-3 text-gray-400"></i>
                            <select name="jenis" id="jenisSelect" onchange="toggleFilter()" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-[#E5D9C8] bg-gray-50 focus:bg-white transition-all text-sm font-medium">
                                <option value="harian" <?= $jenis_laporan == 'harian' ? 'selected' : '' ?>>Harian</option>
                                <option value="bulanan" <?= $jenis_laporan == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                                <option value="tahunan" <?= $jenis_laporan == 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                            </select>
                        </div>
                    </div>

                    <div id="filterHarian" class="<?= $jenis_laporan != 'harian' ? 'hidden' : '' ?> flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pilih Tanggal</label>
                        <input type="date" name="tanggal" value="<?= $tanggal_input ?>" class="w-full px-4 py-2.5 rounded-lg border border-[#E5D9C8] bg-gray-50 focus:bg-white transition-all text-sm">
                    </div>

                    <div id="filterBulanan" class="<?= $jenis_laporan != 'bulanan' ? 'hidden' : '' ?> flex-1 flex gap-4 min-w-[300px]">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Bulan</label>
                            <select name="bulan" class="w-full px-4 py-2.5 rounded-lg border border-[#E5D9C8] bg-gray-50 focus:bg-white transition-all text-sm">
                                <?php for($i=1; $i<=12; $i++): ?>
                                    <option value="<?= $i ?>" <?= $i == $bulan_input ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$i,10)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tahun</label>
                            <input type="number" name="tahun" value="<?= $tahun_input ?>" class="w-full px-4 py-2.5 rounded-lg border border-[#E5D9C8] bg-gray-50 focus:bg-white transition-all text-sm">
                        </div>
                    </div>

                    <div id="filterTahunan" class="<?= $jenis_laporan != 'tahunan' ? 'hidden' : '' ?> flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tahun</label>
                        <input type="number" name="tahun" value="<?= $tahun_input ?>" class="w-full px-4 py-2.5 rounded-lg border border-[#E5D9C8] bg-gray-50 focus:bg-white transition-all text-sm">
                    </div>

                    <button type="submit" class="bg-pale-taupe hover:bg-dark-taupe text-white px-8 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg transition-all text-sm h-[42px] flex items-center">
                        <i class="fas fa-filter mr-2"></i> Tampilkan
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-[#E5D9C8] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700"><?= $judul_laporan ?></h3>
                    </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left w-16">No</th>
                                <th class="px-6 py-4 text-left">Nama Menu</th>
                                <th class="px-6 py-4 text-center">Jumlah Terjual</th>
                                <th class="px-6 py-4 text-right">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            $no = 1; 
                            $grand_total = 0;
                            if ($laporan->num_rows > 0): 
                                while($row = $laporan->fetch_assoc()): 
                                    $grand_total += $row['total_pendapatan'];
                            ?>
                                <tr class="hover:bg-pale-taupe/5 transition-colors">
                                    <td class="px-6 py-4 text-gray-400 font-mono text-sm"><?= $no++ ?></td>
                                    <td class="px-6 py-4 font-bold text-gray-700 text-sm"><?= htmlspecialchars($row['nama_menu']) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                                            <?= $row['total_terjual'] ?> Porsi
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono text-gray-700 text-sm">
                                        Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">Tidak ada data penjualan pada periode ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if ($grand_total > 0): ?>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-bold text-gray-600 uppercase tracking-wider">Total Omset</td>
                                <td class="px-6 py-4 text-right text-lg font-bold text-pale-taupe font-mono">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <div class="h-10"></div>
        </main>
    </div>

    <script>
        function toggleFilter() {
            const jenis = document.getElementById('jenisSelect').value;
            
            document.getElementById('filterHarian').classList.add('hidden');
            document.getElementById('filterBulanan').classList.add('hidden');
            document.getElementById('filterTahunan').classList.add('hidden');
            
            document.querySelector('#filterHarian input').setAttribute('disabled', 'true');
            document.querySelectorAll('#filterBulanan select, #filterBulanan input').forEach(el => el.setAttribute('disabled', 'true'));
            document.querySelector('#filterTahunan input').setAttribute('disabled', 'true');

            if (jenis === 'harian') {
                document.getElementById('filterHarian').classList.remove('hidden');
                document.querySelector('#filterHarian input').removeAttribute('disabled');
            } else if (jenis === 'bulanan') {
                document.getElementById('filterBulanan').classList.remove('hidden');
                document.querySelectorAll('#filterBulanan select, #filterBulanan input').forEach(el => el.removeAttribute('disabled'));
            } else {
                document.getElementById('filterTahunan').classList.remove('hidden');
                document.querySelector('#filterTahunan input').removeAttribute('disabled');
            }
        }
        window.addEventListener('DOMContentLoaded', toggleFilter);
    </script>
</body>
</html>