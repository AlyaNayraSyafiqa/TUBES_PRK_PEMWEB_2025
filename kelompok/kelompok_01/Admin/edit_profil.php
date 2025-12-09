<?php
include 'config.php';

// Ambil data admin berdasarkan session
$user_id = $_SESSION['id_user'];
$user_result = $conn->query("SELECT * FROM users WHERE id_user = $user_id LIMIT 1");
$admin = $user_result->fetch_assoc();

// Update profil jika ada data POST
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update informasi profil
    if (isset($_POST['update_profil'])) {
        $nama = $_POST['nama'];
        $username = $_POST['username'];
        $phone_number = $_POST['phone_number'] ?? '';
        
        // Cek jika username sudah digunakan oleh user lain
        $check_username = $conn->prepare("SELECT id_user FROM users WHERE username = ? AND id_user != ?");
        $check_username->bind_param("si", $username, $user_id);
        $check_username->execute();
        $check_result = $check_username->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $stmt = $conn->prepare("UPDATE users SET nama = ?, username = ?, phone_number = ? WHERE id_user = ?");
            $stmt->bind_param("sssi", $nama, $username, $phone_number, $user_id);
            
            if ($stmt->execute()) {
                $success = "Profil berhasil diperbarui!";
                $_SESSION['nama'] = $nama;
                // Refresh data
                $user_result = $conn->query("SELECT * FROM users WHERE id_user = $user_id LIMIT 1");
                $admin = $user_result->fetch_assoc();
            } else {
                $error = "Gagal memperbarui profil!";
            }
        }
    }
    
    // Update password
    if (isset($_POST['update_password'])) {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];
        
        if (md5($password_lama) == $admin['password'] || $password_lama == $admin['password']) {
            if ($password_baru == $konfirmasi_password) {
                if (strlen($password_baru) >= 6) {
                    $hashed_password = md5($password_baru);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = "Password berhasil diubah!";
                        $admin['password'] = $hashed_password;
                    } else {
                        $error = "Gagal mengubah password!";
                    }
                } else {
                    $error = "Password baru minimal 6 karakter!";
                }
            } else {
                $error = "Password baru dan konfirmasi tidak cocok!";
            }
        } else {
            $error = "Password lama salah!";
        }
    }
    
    // Handle upload foto profil
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profil/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = uniqid() . '_' . basename($_FILES['foto_profil']['name']);
        $target_file = $upload_dir . $file_name;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if (in_array($file_type, $allowed_types)) {
            if ($_FILES['foto_profil']['size'] <= 5 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
                    if (!empty($admin['profile_picture']) && file_exists($admin['profile_picture'])) {
                        unlink($admin['profile_picture']);
                    }
                    
                    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id_user = ?");
                    $stmt->bind_param("si", $target_file, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = "Foto profil berhasil diupload!";
                        $user_result = $conn->query("SELECT * FROM users WHERE id_user = $user_id LIMIT 1");
                        $admin = $user_result->fetch_assoc();
                    } else {
                        $error = "Gagal menyimpan informasi foto profil!";
                    }
                } else {
                    $error = "Gagal mengupload file!";
                }
            } else {
                $error = "Ukuran file terlalu besar! Maksimal 5MB.";
            }
        } else {
            $error = "Format file tidak didukung! Hanya JPG, JPEG, PNG, dan GIF.";
        }
    }
    
    // Hapus foto profil
    if (isset($_POST['hapus_foto'])) {
        if (!empty($admin['profile_picture']) && file_exists($admin['profile_picture'])) {
            unlink($admin['profile_picture']);
        }
        
        $conn->query("UPDATE users SET profile_picture = NULL WHERE id_user = $user_id");
        $success = "Foto profil berhasil dihapus!";
        
        $user_result = $conn->query("SELECT * FROM users WHERE id_user = $user_id LIMIT 1");
        $admin = $user_result->fetch_assoc();
    }
}

$foto_profil = !empty($admin['profile_picture']) ? $admin['profile_picture'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - EasyResto</title>
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
        .avatar-upload { position: relative; display: inline-block; }
        .avatar-upload:hover .avatar-overlay { opacity: 1; }
        .avatar-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(183, 160, 135, 0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; cursor: pointer; }
        .file-input { display: none; }
        .preview-image { object-fit: cover; border-radius: 50%; }
        body { background-color: #F7EBDF; }
        .sidebar { background: linear-gradient(to bottom, #B7A087, #8B7355); }
        .card { background: white; border: 1px solid #E5D9C8; }
        .btn-primary { background-color: #B7A087; color: white; }
        .btn-primary:hover { background-color: #8B7355; }
        .btn-secondary { background-color: #F7EBDF; color: #8B7355; border: 1px solid #B7A087; }
        .btn-secondary:hover { background-color: #E5D9C8; }
        .required:after { content: " *"; color: #ef4444; }
        .no-photo { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background-color: #B7A087; }
        .no-photo i { font-size: 3rem; color: white; }
    </style>
</head>
<body class="bg-antique-white">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 sidebar shadow-xl">
        <div class="flex items-center justify-center h-16 bg-pale-taupe">
            <div class="text-white">
                <h1 class="text-xl font-bold">EasyResto</h1>
                <p class="text-xs text-white opacity-90">Admin Panel</p>
            </div>
        </div>
        
        <nav class="mt-8">
            <a href="dashboard.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-chart-line w-6"></i>
                <span class="mx-3 font-medium">Dashboard</span>
            </a>
            <a href="manajemen_pengguna.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-users w-6"></i>
                <span class="mx-3 font-medium">Manajemen Pengguna</span>
            </a>
            <a href="manajemen_menu.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-utensils w-6"></i>
                <span class="mx-3 font-medium">Manajemen Menu</span>
            </a>
            <a href="manajemen_transaksi.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-cash-register w-6"></i>
                <span class="mx-3 font-medium">Manajemen Transaksi</span>
            </a>
            <a href="laporan_penjualan.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                <i class="fas fa-file-invoice-dollar w-6"></i>
                <span class="mx-3 font-medium">Laporan Penjualan</span>
            </a>
            <a href="profil.php" class="flex items-center px-6 py-3 text-white bg-pale-taupe bg-opacity-40 border-l-4 border-white">
                <i class="fas fa-user-cog w-6"></i>
                <span class="mx-3 font-medium">Profil</span>
            </a>
        </nav>
        
        <div class="absolute bottom-0 w-full p-4 bg-pale-taupe bg-opacity-80">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <?php if (!empty($admin['profile_picture'])): ?>
                        <img src="<?php echo $admin['profile_picture']; ?>" alt="Profil" class="w-8 h-8 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full no-photo">
                            <i class="fas fa-user text-white" style="font-size: 1rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white"><?php echo htmlspecialchars($admin['nama'] ?? 'Admin'); ?></p>
                    <p class="text-xs text-white opacity-90">Admin</p>
                    <a href="../logout.php" class="text-xs text-red-200 hover:text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                    <h1 class="text-2xl font-bold text-gray-800">Profil Admin</h1>
                    <p class="text-gray-600">Kelola informasi akun dan pengaturan</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Selamat datang</p>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($admin['nama'] ?? 'Admin'); ?></p>
                    </div>
                    <a href="profil.php" class="w-10 h-10 rounded-full flex items-center justify-center overflow-hidden border-2 border-pale-taupe">
                        <?php if (!empty($admin['profile_picture'])): ?>
                            <img src="<?php echo $admin['profile_picture']; ?>" alt="Profil" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-pale-taupe flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </header>

        <main class="p-8">
            <!-- Notifikasi -->
            <?php if (!empty($success)): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
                <i class="fas fa-check-circle text-green-500 text-lg mr-3"></i>
                <div>
                    <p class="text-green-800 font-medium">Berhasil!</p>
                    <p class="text-green-600 text-sm"><?php echo $success; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-lg mr-3"></i>
                <div>
                    <p class="text-red-800 font-medium">Error!</p>
                    <p class="text-red-600 text-sm"><?php echo $error; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Kolom Kiri - Informasi Profil -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Card Foto Profil -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-6">Foto Profil</h3>
                        <div class="flex flex-col md:flex-row items-center space-y-6 md:space-y-0 md:space-x-8">
                            <div class="avatar-upload">
                                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                                    <?php if (!empty($admin['profile_picture'])): ?>
                                        <img id="previewAvatar" src="<?php echo $admin['profile_picture']; ?>" alt="Foto Profil" class="preview-image w-full h-full">
                                    <?php else: ?>
                                        <div id="noPhotoPreview" class="no-photo w-full h-full">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="avatar-overlay" onclick="document.getElementById('fileInput').click()">
                                    <i class="fas fa-camera text-white text-2xl"></i>
                                </div>
                            </div>
                            
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-4">
                                    Upload foto profil Anda. Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal ukuran file: 5MB.
                                </p>
                                <form method="POST" enctype="multipart/form-data" id="fotoForm" class="space-y-4">
                                    <input type="file" id="fileInput" name="foto_profil" accept="image/*" class="file-input" onchange="previewImage(this)">
                                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                                        <button type="button" onclick="document.getElementById('fileInput').click()" class="btn-secondary px-4 py-2 rounded-lg font-medium flex items-center justify-center">
                                            <i class="fas fa-upload mr-2"></i>Pilih Foto
                                        </button>
                                        <button type="submit" name="upload_foto" class="btn-primary px-4 py-2 rounded-lg font-medium flex items-center justify-center">
                                            <i class="fas fa-save mr-2"></i>Upload Foto
                                        </button>
                                        <?php if (!empty($admin['profile_picture'])): ?>
                                        <button type="submit" name="hapus_foto" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium flex items-center justify-center">
                                            <i class="fas fa-trash mr-2"></i>Hapus Foto
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <p id="fileName" class="text-sm text-gray-500 mt-2"></p>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Card Informasi Profil -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">Informasi Profil</h3>
                            <span class="px-3 py-1 bg-pale-taupe bg-opacity-20 text-gray-700 text-xs font-medium rounded-full">
                                <i class="fas fa-user-shield mr-1"></i>Admin
                            </span>
                        </div>
                        
                        <form method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">Nama Lengkap</label>
                                    <input type="text" name="nama" value="<?php echo htmlspecialchars($admin['nama'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">Username</label>
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">Nomor Telepon</label>
                                    <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($admin['phone_number'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors" placeholder="+62 xxx xxx xxx" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                    <input type="text" value="Admin" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-500" disabled readonly>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
                                    <input type="text" value="#<?php echo $admin['id_user'] ?? 'N/A'; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-500" disabled readonly>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <button type="submit" name="update_profil" class="btn-primary px-6 py-3 rounded-lg font-medium">
                                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Card Ubah Password -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-6">Ubah Password</h3>
                        
                        <form method="POST">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">Password Lama</label>
                                    <input type="password" name="password_lama" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors" placeholder="Masukkan password lama" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">Password Baru</label>
                                    <input type="password" name="password_baru" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors" placeholder="Minimal 6 karakter" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">Konfirmasi Password Baru</label>
                                    <input type="password" name="konfirmasi_password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pale-taupe focus:border-pale-taupe transition-colors" placeholder="Konfirmasi password baru" required>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <button type="submit" name="update_password" class="btn-primary px-6 py-3 rounded-lg font-medium">
                                    <i class="fas fa-key mr-2"></i>Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Kolom Kanan - Statistik & Info -->
                <div class="space-y-6">
                    <!-- Card Profil Summary -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="text-center">
                            <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-white shadow-lg mx-auto mb-4">
                                <?php if (!empty($admin['profile_picture'])): ?>
                                    <img src="<?php echo $admin['profile_picture']; ?>" alt="Foto Profil" class="preview-image w-full h-full">
                                <?php else: ?>
                                    <div class="no-photo w-full h-full">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($admin['nama'] ?? 'Admin'); ?></h3>
                            <p class="text-gray-600 mb-2">Admin Restoran</p>
                            <span class="inline-flex items-center px-3 py-1 bg-pale-taupe bg-opacity-20 text-gray-700 text-sm font-medium rounded-full">
                                <i class="fas fa-user-shield mr-1"></i>Admin Account
                            </span>
                        </div>
                        
                        <div class="mt-6 space-y-3">
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Username</span>
                                <span id="username-display" class="font-medium"><?php echo htmlspecialchars($admin['username'] ?? '-'); ?></span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Telepon</span>
                                <span id="phone-display" class="font-medium"><?php echo !empty($admin['phone_number']) ? htmlspecialchars($admin['phone_number']) : 'Belum diatur'; ?></span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">User ID</span>
                                <span id="userid-display" class="font-medium">#<?php echo $admin['id_user'] ?? '-'; ?></span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-gray-600">Status</span>
                                <span class="font-medium text-green-600">Aktif</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Informasi Akun</h4>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600"><i class="fas fa-calendar-alt mr-2"></i>Terdaftar: <?php echo date('d M Y'); ?></p>
                                <p class="text-sm text-gray-600"><i class="fas fa-user-tag mr-2"></i>Hak akses: Admin</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Aktivitas Terbaru -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-pale-taupe bg-opacity-20 rounded-full flex items-center justify-center mt-1">
                                    <i class="fas fa-user-edit text-pale-taupe text-xs"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Mengelola profil</p>
                                    <p class="text-xs text-gray-500">Saat ini</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mt-1">
                                    <i class="fas fa-chart-line text-green-600 text-xs"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Melihat laporan</p>
                                    <p class="text-xs text-gray-500">Hari ini</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mt-1">
                                    <i class="fas fa-utensils text-purple-600 text-xs"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Update menu</p>
                                    <p class="text-xs text-gray-500">Kemarin</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Quick Actions -->
                    <div class="bg-gradient-to-r from-pale-taupe to-amber-800 rounded-xl shadow-lg p-6 text-white">
                        <h3 class="text-lg font-semibold mb-4">Aksi Cepat</h3>
                        <div class="space-y-3">
                            <a href="dashboard.php" class="flex items-center p-3 bg-white bg-opacity-10 rounded-lg hover:bg-opacity-20 transition-colors">
                                <i class="fas fa-tachometer-alt mr-3"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="laporan_penjualan.php" class="flex items-center p-3 bg-white bg-opacity-10 rounded-lg hover:bg-opacity-20 transition-colors">
                                <i class="fas fa-chart-bar mr-3"></i>
                                <span>Laporan</span>
                            </a>
                            <a href="manajemen_menu.php" class="flex items-center p-3 bg-white bg-opacity-10 rounded-lg hover:bg-opacity-20 transition-colors">
                                <i class="fas fa-cog mr-3"></i>
                                <span>Kelola Menu</span>
                            </a>
                            <a href="manajemen_pengguna.php" class="flex items-center p-3 bg-white bg-opacity-10 rounded-lg hover:bg-opacity-20 transition-colors">
                                <i class="fas fa-users mr-3"></i>
                                <span>Kelola Pengguna</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatarContainer = document.querySelector('.avatar-upload > div');
                    avatarContainer.innerHTML = `<img id="previewAvatar" src="${e.target.result}" alt="Foto Profil" class="preview-image w-full h-full">`;
                }
                reader.readAsDataURL(input.files[0]);
                document.getElementById('fileName').textContent = 'File terpilih: ' + input.files[0].name;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.querySelector('input[name="password_baru"]');
            const confirmInput = document.querySelector('input[name="konfirmasi_password"]');
            
            if (passwordInput && confirmInput) {
                function checkPasswordMatch() {
                    const password = passwordInput.value;
                    const confirm = confirmInput.value;
                    
                    if (confirm && password !== confirm) {
                        confirmInput.classList.add('border-red-500');
                        confirmInput.classList.remove('border-green-500');
                    } else if (confirm && password === confirm) {
                        confirmInput.classList.remove('border-red-500');
                        confirmInput.classList.add('border-green-500');
                    } else {
                        confirmInput.classList.remove('border-red-500', 'border-green-500');
                    }
                }
                
                passwordInput.addEventListener('input', checkPasswordMatch);
                confirmInput.addEventListener('input', checkPasswordMatch);
            }
        });

        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
                    submitBtn.disabled = true;
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan Perubahan';
                    }, 3000);
                }
            });
        });

        const hapusFotoBtn = document.querySelector('button[name="hapus_foto"]');
        if (hapusFotoBtn) {
            hapusFotoBtn.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus foto profil?\nFoto akan dihapus permanen.')) {
                    e.preventDefault();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const namaInput = document.querySelector('input[name="nama"]');
            const usernameInput = document.querySelector('input[name="username"]');
            const phoneInput = document.querySelector('input[name="phone_number"]');
            
            if (namaInput) {
                namaInput.addEventListener('input', function() {
                    document.querySelector('.text-center h3').textContent = this.value || 'Admin';
                });
            }
            
            if (usernameInput) {
                usernameInput.addEventListener('input', function() {
                    document.getElementById('username-display').textContent = this.value || '-';
                });
            }
            
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    document.getElementById('phone-display').textContent = this.value || 'Belum diatur';
                });
            }
        });
    </script>
</body>
</html>
