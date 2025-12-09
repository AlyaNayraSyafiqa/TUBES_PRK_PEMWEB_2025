<?php
include 'config.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

$sql = "SELECT * FROM laporan_penjualan WHERE DATE(tanggal) BETWEEN ? AND ? ORDER BY tanggal DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$summary_sql = "SELECT SUM(subtotal) as total_subtotal, SUM(ppn) as total_ppn, SUM(service) as total_service, SUM(total_permenu) as total_final, COUNT(DISTINCT id_transaksi) as total_transaksi, SUM(jumlah) as total_item FROM laporan_penjualan WHERE DATE(tanggal) BETWEEN ? AND ?";
$stmt_summary = $conn->prepare($summary_sql);
$stmt_summary->bind_param("ss", $start_date, $end_date);
$stmt_summary->execute();
$summary = $stmt_summary->get_result()->fetch_assoc();

$avg_transaction = $summary['total_transaksi'] > 0 ? $summary['total_final'] / $summary['total_transaksi'] : 0;

$admin_id = $_SESSION['id_user'];
$admin = $conn->query("SELECT * FROM users WHERE id_user = $admin_id")->fetch_assoc();
$foto_profil = !empty($admin['profile_picture']) ? '../' . $admin['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin['nama'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - EasyResto Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>tailwind.config={theme:{extend:{colors:{'antique-white':'#F7EBDF','pale-taupe':'#B7A087','primary':'#B7A087','secondary':'#F7EBDF'}}}}</script>
    <style>body{background-color:#F7EBDF}.sidebar{background:linear-gradient(to bottom,#B7A087,#8B7355)}.btn-primary{background-color:#B7A087;color:white}.btn-primary:hover{background-color:#8B7355}@media print{.no-print{display:none!important}.ml-64{margin-left:0!important}}</style>
</head>
<body class="bg-antique-white">
    <div class="fixed inset-y-0 left-0 w-64 sidebar shadow-xl flex flex-col justify-between no-print">
        <div>
            <div class="flex items-center justify-center h-16 bg-pale-taupe"><div class="text-white text-center"><h1 class="text-xl font-bold">EasyResto</h1><p class="text-xs opacity-90">Admin Panel</p></div></div>
            <nav class="mt-8">
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-chart-line w-6"></i><span class="mx-3">Dashboard</span></a>
                <a href="manajemen_pengguna.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-users w-6"></i><span class="mx-3">Manajemen Pengguna</span></a>
                <a href="manajemen_menu.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-utensils w-6"></i><span class="mx-3">Manajemen Menu</span></a>
                <a href="manajemen_transaksi.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-cash-register w-6"></i><span class="mx-3">Manajemen Transaksi</span></a>
                <a href="laporan_penjualan.php" class="flex items-center px-6 py-3 text-white bg-pale-taupe bg-opacity-40 border-l-4 border-white"><i class="fas fa-file-invoice-dollar w-6"></i><span class="mx-3">Laporan Penjualan</span></a>
                <a href="profil.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30"><i class="fas fa-user-cog w-6"></i><span class="mx-3">Profil</span></a>
            </nav>
        </div>
        <div class="p-4 bg-pale-taupe bg-opacity-80"><div class="flex items-center gap-3"><img src="<?=$foto_profil?>" class="w-10 h-10 rounded-full border-2 border-white object-cover"><div class="text-white"><p class="font-bold text-sm truncate"><?=htmlspecialchars($_SESSION['nama'])?></p><p class="text-xs opacity-90">Role: Admin</p><a href="../logout.php" class="text-xs text-red-200 hover:text-white"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div></div>
    </div>

    <div class="ml-64">
        <header class="bg-white shadow-sm border-b border-pale-taupe no-print"><div class="flex items-center justify-between px-8 py-4"><div><h1 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h1><p class="text-gray-600">Detail laporan transaksi dan penjualan</p></div><button onclick="window.print()" class="btn-primary px-4 py-2 rounded-lg"><i class="fas fa-print mr-2"></i>Cetak</button></div></header>
        <main class="p-8">
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-8 no-print">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Laporan</h3>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label><input type="date" name="start_date" value="<?=$start_date?>" class="w-full px-3 py-2 border rounded-lg"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label><input type="date" name="end_date" value="<?=$end_date?>" class="w-full px-3 py-2 border rounded-lg"></div>
                    <div class="flex items-end"><button type="submit" class="w-full btn-primary px-4 py-2 rounded-lg"><i class="fas fa-filter mr-2"></i>Filter</button></div>
                    <div class="flex items-end"><a href="laporan_penjualan.php" class="w-full px-4 py-2 text-center bg-gray-100 rounded-lg hover:bg-gray-200">Reset</a></div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 no-print">
                <div class="bg-gradient-to-r from-pale-taupe to-amber-800 rounded-xl shadow-lg p-6 text-white"><div class="flex items-center justify-between"><div><p class="text-sm">Total Transaksi</p><p class="text-2xl font-bold"><?=number_format($summary['total_transaksi']??0,0)?></p></div><i class="fas fa-shopping-cart text-2xl opacity-80"></i></div></div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white"><div class="flex items-center justify-between"><div><p class="text-sm">Item Terjual</p><p class="text-2xl font-bold"><?=number_format($summary['total_item']??0,0)?></p></div><i class="fas fa-boxes text-2xl opacity-80"></i></div></div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white"><div class="flex items-center justify-between"><div><p class="text-sm">Rata-rata</p><p class="text-2xl font-bold">Rp <?=number_format($avg_transaction,0,',','.')?></p></div><i class="fas fa-chart-bar text-2xl opacity-80"></i></div></div>
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white"><div class="flex items-center justify-between"><div><p class="text-sm">Total Pendapatan</p><p class="text-2xl font-bold">Rp <?=number_format($summary['total_final']??0,0,',','.')?></p></div><i class="fas fa-money-bill-wave text-2xl opacity-80"></i></div></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50"><h3 class="text-lg font-semibold text-gray-800">Detail Laporan</h3><p class="text-sm text-gray-600">Periode: <?=date('d M Y',strtotime($start_date))?> - <?=date('d M Y',strtotime($end_date))?></p></div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">No</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">ID</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Tanggal</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Menu</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Kategori</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Jumlah</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Subtotal</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Total</th></tr></thead>
                        <tbody class="divide-y">
                            <?php if($result->num_rows>0):$c=1;while($row=$result->fetch_assoc()):?>
                            <tr class="hover:bg-pale-taupe hover:bg-opacity-10">
                                <td class="px-4 py-3 text-sm"><?=$c++?></td>
                                <td class="px-4 py-3"><span class="bg-pale-taupe bg-opacity-20 px-2 py-1 rounded text-sm">#<?=$row['id_transaksi']?></span></td>
                                <td class="px-4 py-3 text-sm"><?=date('d/m/Y H:i',strtotime($row['tanggal']))?></td>
                                <td class="px-4 py-3 text-sm"><?=$row['nama_menu']?></td>
                                <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full <?=$row['nama_kategori']=='Makanan'?'bg-green-100 text-green-800':($row['nama_kategori']=='Minuman'?'bg-blue-100 text-blue-800':'bg-purple-100 text-purple-800')?>"><?=$row['nama_kategori']?></span></td>
                                <td class="px-4 py-3 text-sm"><?=$row['jumlah']?> pcs</td>
                                <td class="px-4 py-3 text-sm">Rp <?=number_format($row['subtotal'],0,',','.')?></td>
                                <td class="px-4 py-3 text-sm font-semibold text-green-600">Rp <?=number_format($row['total_permenu'],0,',','.')?></td>
                            </tr>
                            <?php endwhile;else:?>
                            <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-inbox text-4xl mb-4"></i><p>Tidak ada data</p></td></tr>
                            <?php endif;?>
                        </tbody>
                        <?php if($result->num_rows>0):?>
                        <tfoot class="bg-gray-50 font-bold"><tr><td colspan="6" class="px-4 py-3 text-right">TOTAL:</td><td class="px-4 py-3 text-sm">Rp <?=number_format($summary['total_subtotal']??0,0,',','.')?></td><td class="px-4 py-3 text-sm text-green-600">Rp <?=number_format($summary['total_final']??0,0,',','.')?></td></tr></tfoot>
                        <?php endif;?>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
