<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'config.php';

$kategori_result = $conn->query("SELECT * FROM kategori_menu");
$menu_result = $conn->query("SELECT m.*, k.nama_kategori FROM menu m LEFT JOIN kategori_menu k ON m.id_kategori = k.id_kategori ORDER BY m.id_menu");

// Tambah menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_menu'])) {
    $nama_menu = trim($_POST['nama_menu'] ?? '');
    $harga = intval($_POST['harga'] ?? 0);
    $id_kategori = intval($_POST['id_kategori'] ?? 0);
    
    $foto_paths = [];
    $upload_dir = __DIR__ . '/uploads/menu/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    for ($i = 0; $i < 5; $i++) {
        $field = 'cropped_image_' . $i;
        if (!empty($_POST[$field])) {
            $base64 = $_POST[$field];
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $m)) {
                $ext = ($m[1] === 'jpeg') ? 'jpg' : $m[1];
                $data = base64_decode(substr($base64, strpos($base64, ',') + 1));
                if ($data) {
                    $name = 'menu_' . uniqid() . '_' . $i . '.' . $ext;
                    if (file_put_contents($upload_dir . $name, $data)) {
                        $foto_paths[] = 'uploads/menu/' . $name;
                    }
                }
            }
        }
    }
    
    $foto_json = !empty($foto_paths) ? json_encode($foto_paths) : null;
    $stmt = $conn->prepare("INSERT INTO menu (nama_menu, harga, id_kategori, foto) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("siis", $nama_menu, $harga, $id_kategori, $foto_json);
        if ($stmt->execute()) {
            header("Location: manajemen_menu.php?success=1");
            exit();
        } else {
            $error_msg = urlencode("Execute error: " . $stmt->error);
            header("Location: manajemen_menu.php?error=" . $error_msg);
            exit();
        }
    } else {
        $error_msg = urlencode("Prepare error: " . $conn->error);
        header("Location: manajemen_menu.php?error=" . $error_msg);
        exit();
    }
}

// Hapus menu
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $r = $conn->query("SELECT foto FROM menu WHERE id_menu = $id");
    if ($row = $r->fetch_assoc()) {
        $fotos = json_decode($row['foto'], true);
        if (is_array($fotos)) {
            foreach ($fotos as $f) {
                $path = __DIR__ . '/' . $f;
                if (file_exists($path)) unlink($path);
            }
        }
    }
    $conn->query("DELETE FROM menu WHERE id_menu = $id");
    header("Location: manajemen_menu.php");
    exit();
}

$total_menu = $menu_result->num_rows;
$menu_result->data_seek(0);

$admin_id = $_SESSION['id_user'] ?? 0;
$admin = [];
if ($admin_id > 0) {
    $ar = $conn->query("SELECT * FROM users WHERE id_user = $admin_id");
    if ($ar && $ar->num_rows > 0) $admin = $ar->fetch_assoc();
}
$foto_profil = !empty($admin['profile_picture']) ? '../' . $admin['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin['nama'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Menu - EasyResto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <style>
        body { background: #F7EBDF; }
        .sidebar { background: linear-gradient(180deg, #B7A087, #8B7355); }
        
        /* Photo slots */
        .photo-slot {
            width: 80px; height: 80px;
            border: 2px dashed #B7A087;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; position: relative; overflow: hidden;
            background: #faf8f5; transition: all 0.2s;
        }
        .photo-slot:hover { border-color: #8B7355; transform: scale(1.05); }
        .photo-slot.filled { border: 2px solid #22c55e; }
        .photo-slot img { width: 100%; height: 100%; object-fit: cover; position: absolute; }
        .photo-slot .label { 
            position: absolute; bottom: 2px; font-size: 9px; 
            background: rgba(0,0,0,0.6); color: white; padding: 1px 5px; border-radius: 3px;
        }
        .photo-slot .delete-btn {
            position: absolute; top: 2px; right: 2px;
            background: #ef4444; color: white; border-radius: 50%;
            width: 18px; height: 18px; font-size: 10px;
            display: none; align-items: center; justify-content: center; cursor: pointer;
        }
        .photo-slot:hover .delete-btn { display: flex; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 sidebar shadow-xl flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-center h-16 bg-[#B7A087]">
                <h1 class="text-xl font-bold text-white">EasyResto</h1>
            </div>
            <nav class="mt-6 space-y-1">
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-white hover:bg-white/20"><i class="fas fa-chart-line w-5"></i><span class="ml-3">Dashboard</span></a>
                <a href="manajemen_pengguna.php" class="flex items-center px-6 py-3 text-white hover:bg-white/20"><i class="fas fa-users w-5"></i><span class="ml-3">Manajemen Pengguna</span></a>
                <a href="manajemen_menu.php" class="flex items-center px-6 py-3 text-white bg-white/30 border-l-4 border-white"><i class="fas fa-utensils w-5"></i><span class="ml-3">Manajemen Menu</span></a>
                <a href="manajemen_transaksi.php" class="flex items-center px-6 py-3 text-white hover:bg-white/20"><i class="fas fa-cash-register w-5"></i><span class="ml-3">Manajemen Transaksi</span></a>
                <a href="laporan_penjualan.php" class="flex items-center px-6 py-3 text-white hover:bg-white/20"><i class="fas fa-file-invoice-dollar w-5"></i><span class="ml-3">Laporan Penjualan</span></a>
                <a href="profil.php" class="flex items-center px-6 py-3 text-white hover:bg-white/20"><i class="fas fa-user-cog w-5"></i><span class="ml-3">Profil</span></a>
            </nav>
        </div>
        <div class="p-4 bg-white/20">
            <div class="flex items-center gap-3">
                <img src="<?= $foto_profil ?>" class="w-10 h-10 rounded-full border-2 border-white object-cover">
                <div class="text-white text-sm">
                    <p class="font-bold"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></p>
                    <a href="../logout.php" class="text-red-200 hover:text-white text-xs"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Menu</h1>
                <p class="text-gray-600">Kelola menu restoran</p>
            </div>
            <button onclick="openModal()" class="px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Menu
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>Menu berhasil ditambahkan!
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>Error: <?= htmlspecialchars(urldecode($_GET['error'])) ?>
        </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Foto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Menu</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php while ($m = $menu_result->fetch_assoc()): 
                        $fotos = !empty($m['foto']) ? json_decode($m['foto'], true) : [];
                        if (!is_array($fotos)) $fotos = [];
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">#<?= $m['id_menu'] ?></td>
                        <td class="px-6 py-4">
                            <?php if (!empty($fotos)): ?>
                            <div class="flex -space-x-2">
                                <?php foreach (array_slice($fotos, 0, 3) as $i => $f): ?>
                                <img src="<?= $f ?>" class="w-10 h-10 rounded-lg object-cover border-2 border-white" style="z-index:<?= 3-$i ?>">
                                <?php endforeach; ?>
                                <?php if (count($fotos) > 3): ?>
                                <div class="w-10 h-10 rounded-lg bg-gray-300 border-2 border-white flex items-center justify-center text-xs font-bold">+<?= count($fotos)-3 ?></div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center"><i class="fas fa-image text-gray-400"></i></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 font-medium"><?= htmlspecialchars($m['nama_menu']) ?></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full <?= $m['nama_kategori']=='Makanan'?'bg-green-100 text-green-800':($m['nama_kategori']=='Minuman'?'bg-blue-100 text-blue-800':'bg-purple-100 text-purple-800') ?>"><?= $m['nama_kategori'] ?></span></td>
                        <td class="px-6 py-4 font-semibold">Rp <?= number_format($m['harga'],0,',','.') ?></td>
                        <td class="px-6 py-4"><button onclick="if(confirm('Hapus menu ini?')) location='?hapus=<?= $m['id_menu'] ?>'" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="bg-gradient-to-r from-[#B7A087] to-[#8B7355] px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white"><i class="fas fa-utensils mr-2"></i>Tambah Menu Baru</h3>
                <button onclick="closeModal()" class="text-white hover:bg-white/20 rounded-full p-2"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" id="menuForm" class="p-6 space-y-5">
                <!-- Photos -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-images mr-1 text-[#B7A087]"></i>Foto Menu (1-5)</label>
                    <div class="flex gap-3" id="photoSlots">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                        <div class="photo-slot" id="slot<?= $i ?>" onclick="pickPhoto(<?= $i ?>)">
                            <i class="fas fa-plus text-[#B7A087]" id="icon<?= $i ?>"></i>
                            <span class="label"><?= $i+1 ?></span>
                            <div class="delete-btn" id="del<?= $i ?>" onclick="removePhoto(<?= $i ?>, event)"><i class="fas fa-times"></i></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <input type="file" id="fileInput" accept="image/*" class="hidden" onchange="onFileSelected(event)">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <input type="hidden" name="cropped_image_<?= $i ?>" id="data<?= $i ?>">
                    <?php endfor; ?>
                </div>
                
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Menu *</label>
                    <input type="text" name="nama_menu" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-[#B7A087] outline-none" placeholder="Contoh: Nasi Goreng">
                </div>
                
                <!-- Price & Category -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Harga *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" name="harga" required min="1000" class="w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-[#B7A087] outline-none" placeholder="25000">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori *</label>
                        <select name="id_kategori" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-[#B7A087] outline-none bg-white">
                            <option value="">Pilih...</option>
                            <?php $kategori_result->data_seek(0); while ($k = $kategori_result->fetch_assoc()): ?>
                            <option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Batal</button>
                    <button type="submit" name="tambah_menu" class="px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium">
                        <i class="fas fa-plus mr-1"></i>Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Crop Modal - Completely separate page overlay -->
    <div id="cropModal" class="fixed inset-0 z-[99999] hidden" style="background:#1a1a1a;">
        <div class="h-full flex flex-col">
            <!-- Header -->
            <div class="bg-gray-900 px-6 py-4 flex justify-between items-center border-b border-gray-700">
                <div>
                    <h3 class="text-lg font-bold text-white">Crop Foto (1:1)</h3>
                    <p class="text-gray-400 text-sm">Sesuaikan posisi dan ukuran foto</p>
                </div>
                <button onclick="cancelCrop()" class="text-gray-400 hover:text-white p-2"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <!-- Cropper Area -->
            <div class="flex-1 flex items-center justify-center p-4 overflow-hidden">
                <div class="max-w-3xl w-full h-full flex items-center justify-center">
                    <img id="cropImage" src="" class="max-w-full max-h-[70vh] block">
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-900 px-6 py-4 flex justify-between items-center border-t border-gray-700">
                <div class="flex gap-2">
                    <button onclick="rotateLeft()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600"><i class="fas fa-undo"></i></button>
                    <button onclick="rotateRight()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600"><i class="fas fa-redo"></i></button>
                    <button onclick="resetCrop()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div class="flex gap-3">
                    <button onclick="cancelCrop()" class="px-5 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-500">Batal</button>
                    <button onclick="applyCrop()" class="px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium">
                        <i class="fas fa-check mr-1"></i>Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        let cropper = null;
        let currentSlot = 0;

        function openModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('menuForm').reset();
            for (let i = 0; i < 5; i++) {
                document.getElementById('data' + i).value = '';
                const slot = document.getElementById('slot' + i);
                const icon = document.getElementById('icon' + i);
                const img = slot.querySelector('img');
                if (img) img.remove();
                slot.classList.remove('filled');
                icon.style.display = '';
            }
        }

        function pickPhoto(slot) {
            currentSlot = slot;
            document.getElementById('fileInput').click();
        }

        function onFileSelected(e) {
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            
            const reader = new FileReader();
            reader.onload = function(ev) {
                openCropModal(ev.target.result);
            };
            reader.readAsDataURL(file);
            e.target.value = '';
        }

        function openCropModal(src) {
            // Hide add modal
            document.getElementById('addModal').style.display = 'none';
            
            // Show crop modal
            document.getElementById('cropModal').classList.remove('hidden');
            
            const img = document.getElementById('cropImage');
            img.src = src;
            
            if (cropper) cropper.destroy();
            
            // Initialize cropper after image loads
            img.onload = function() {
                cropper = new Cropper(img, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    responsive: true,
                    background: false,
                });
            };
        }

        function cancelCrop() {
            document.getElementById('cropModal').classList.add('hidden');
            document.getElementById('addModal').style.display = '';
            if (cropper) { cropper.destroy(); cropper = null; }
        }

        function rotateLeft() { if (cropper) cropper.rotate(-90); }
        function rotateRight() { if (cropper) cropper.rotate(90); }
        function resetCrop() { if (cropper) cropper.reset(); }

        function applyCrop() {
            if (!cropper) return;
            
            const canvas = cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingQuality: 'high'
            });
            
            const data = canvas.toDataURL('image/jpeg', 0.85);
            
            // Save to hidden input
            document.getElementById('data' + currentSlot).value = data;
            
            // Update slot preview
            const slot = document.getElementById('slot' + currentSlot);
            const icon = document.getElementById('icon' + currentSlot);
            
            // Remove existing image
            const existingImg = slot.querySelector('img');
            if (existingImg) existingImg.remove();
            
            // Add new preview
            const preview = document.createElement('img');
            preview.src = data;
            slot.insertBefore(preview, slot.firstChild);
            slot.classList.add('filled');
            icon.style.display = 'none';
            
            // Close crop modal
            cancelCrop();
        }

        function removePhoto(slot, e) {
            e.stopPropagation();
            document.getElementById('data' + slot).value = '';
            const slotEl = document.getElementById('slot' + slot);
            const icon = document.getElementById('icon' + slot);
            const img = slotEl.querySelector('img');
            if (img) img.remove();
            slotEl.classList.remove('filled');
            icon.style.display = '';
        }
    </script>
</body>
</html>
