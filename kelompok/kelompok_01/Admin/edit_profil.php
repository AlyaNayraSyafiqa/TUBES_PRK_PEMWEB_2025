<?php
session_start();
include '../config.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user']; 

$stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profil'])) {
        $nama = $_POST['nama'];
        $username = $_POST['username'];
        $phone_number = $_POST['phone_number'] ?? '';
        
    
        $check = $conn->prepare("SELECT id_user FROM users WHERE username = ? AND id_user != ?");
        $check->bind_param("si", $username, $id_user);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $stmt = $conn->prepare("UPDATE users SET nama = ?, username = ?, phone_number = ? WHERE id_user = ?");
            $stmt->bind_param("sssi", $nama, $username, $phone_number, $id_user);
            
            if ($stmt->execute()) {
                $success = "Profil berhasil diperbarui!";
                $_SESSION['nama'] = $nama;
                
                $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
                $stmt->bind_param("i", $id_user);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error = "Gagal memperbarui profil!";
            }
        }
    }

    if (isset($_POST['update_password'])) {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi = $_POST['konfirmasi_password'];

        if (md5($password_lama) == $user['password'] || $password_lama == $user['password']) {
            if ($password_baru == $konfirmasi) {
                if (strlen($password_baru) >= 6) {
                    $hashed = md5($password_baru);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
                    $stmt->bind_param("si", $hashed, $id_user);
                    if ($stmt->execute()) $success = "Password berhasil diubah!";
                } else {
                    $error = "Password baru minimal 6 karakter!";
                }
            } else {
                $error = "Konfirmasi password tidak cocok!";
            }
        } else {
            $error = "Password lama salah!";
        }
    }

    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profil/'; 
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_info = pathinfo($_FILES['foto_profil']['name']);
        $ext = strtolower($file_info['extension']);
        $file_name = uniqid() . '_' . time() . '.' . $ext;
        
        $target_file = $upload_dir . $file_name;
        $db_path = 'uploads/profil/' . $file_name; 
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $allowed)) {
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
                if (!empty($user['profile_picture']) && file_exists('../' . $user['profile_picture'])) {
                    unlink('../' . $user['profile_picture']);
                }
            
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id_user = ?");
                $stmt->bind_param("si", $db_path, $id_user);
                
                if ($stmt->execute()) {
                    $_SESSION['profile_picture'] = $db_path; 
                    $success = "Foto berhasil diupload!";
                    
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
                    $stmt->bind_param("i", $id_user);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                } else {
                    $error = "Gagal menyimpan ke database: " . $conn->error;
                }
            } else {
                $error = "Gagal memindahkan file upload.";
            }
        } else {
            $error = "Format file tidak didukung! Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    }
}

$foto_display = !empty($user['profile_picture']) && file_exists('../' . $user['profile_picture']) 
    ? '../' . $user['profile_picture'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Admin</title> 
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
        .btn-primary { background-color: #B7A087; color: white; }
        .btn-primary:hover { background-color: #8B7355; }
        input:focus { outline: none !important; border-color: #B7A087 !important; box-shadow: none !important; }
    </style>
</head>
<body class="bg-antique-white h-screen flex overflow-hidden font-sans text-gray-800">

    <div class="w-64 sidebar shadow-xl flex flex-col justify-between z-20 flex-shrink-0">
        <div>
            <div class="h-16 flex items-center justify-center bg-pale-taupe">
                <div class="text-white text-center">
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
                    <span class="mx-3 font-medium">Kelola Pengguna</span>
                </a>
                <a href="manajemen_menu.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-utensils w-6"></i>
                    <span class="mx-3 font-medium">Kelola Menu</span>
                </a>
                <a href="riwayat_transaksi.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-history w-6"></i>
                    <span class="mx-3 font-medium">Semua Transaksi</span>
                </a>
                <a href="laporan_penjualan.php" class="flex items-center px-6 py-3 text-white hover:bg-pale-taupe hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="mx-3 font-medium">Laporan Penjualan</span>
                </a>
                <a href="profil_admin.php" class="flex items-center px-6 py-3 text-white bg-pale-taupe bg-opacity-40 border-l-4 border-white transition-all">
                    <i class="fas fa-user-cog w-6"></i>
                    <span class="mx-3 font-medium">Profil</span>
                </a>
            </nav>
        </div>
        
        <div class="p-4 bg-pale-taupe bg-opacity-80">
            <div class="flex items-center gap-3">
                <img src="<?= $foto_display ?>" class="w-10 h-10 rounded-full border-2 border-white object-cover">
                <div class="overflow-hidden text-white">
                    <p class="font-bold text-sm truncate leading-tight"><?= htmlspecialchars($_SESSION['nama']) ?></p>
                    <p class="text-xs opacity-90">Role: Admin</p> 
                    <a href="../logout.php" class="text-xs text-red-200 hover:text-white flex items-center gap-1 mt-1 transition-colors">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="bg-white shadow-sm border-b border-[#E5D9C8] flex-shrink-0 px-8 py-4">
            <h1 class="text-2xl font-bold text-gray-800">Pengaturan Profil Admin</h1>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-[#E5D9C8] text-center">
                        <div class="relative w-32 h-32 mx-auto mb-4 group cursor-pointer" onclick="document.getElementById('fotoInput').click()">
                            <img src="<?= $foto_display ?>" class="w-full h-full rounded-full object-cover border-4 border-[#F7EBDF]">
                            <div class="absolute inset-0 bg-black bg-opacity-40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-camera text-white text-2xl"></i>
                            </div>
                        </div>
                        <h2 class="font-bold text-lg"><?= htmlspecialchars($user['nama']) ?></h2>
                        <p class="text-gray-500 text-sm mb-4">Role: Admin</p> 
                        
                        <form method="POST" enctype="multipart/form-data">
                            <input type="file" name="foto_profil" id="fotoInput" class="hidden" onchange="this.form.submit()">
                        </form>
                        <p class="text-xs text-gray-400 mt-2">Klik foto untuk mengganti</p>
                    </div>

                    <div class="bg-gradient-to-br from-pale-taupe to-[#8B7355] rounded-xl p-6 text-white shadow-lg">
                        <h3 class="font-bold mb-4 border-b border-white/20 pb-2">Aksi Cepat Admin</h3>
                        <div class="space-y-2">
                            <a href="manajemen_pengguna.php" class="block bg-white/10 hover:bg-white/20 px-3 py-2 rounded text-sm transition"><i class="fas fa-users mr-2"></i> Kelola Pengguna</a>
                            <a href="manajemen_menu.php" class="block bg-white/10 hover:bg-white/20 px-3 py-2 rounded text-sm transition"><i class="fas fa-utensils mr-2"></i> Kelola Menu</a>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-[#E5D9C8]">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-user-edit text-pale-taupe"></i> Edit Informasi Admin</h3>
                        <form method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-pale-taupe" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-pale-taupe" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                                    <input type="text" name="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-pale-taupe">
                                </div>
                            </div>
                            <div class="mt-4 text-right">
                                <button type="submit" name="update_profil" class="btn-primary px-6 py-2 rounded-lg font-bold shadow-sm transition-transform active:scale-95">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-[#E5D9C8]">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-key text-pale-taupe"></i> Ganti Password</h3>
                        <form method="POST">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                                    <input type="password" name="password_lama" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-pale-taupe" required>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                                        <input type="password" name="password_baru" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-pale-taupe" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                                        <input type="password" name="konfirmasi_password" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-pale-taupe" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-right">
                                <button type="submit" name="update_password" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg font-bold shadow-sm transition-transform active:scale-95">Ubah Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="h-10"></div>
        </main>
    </div>
</body>
</html>
