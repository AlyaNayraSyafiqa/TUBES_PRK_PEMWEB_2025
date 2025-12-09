<?php
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';

$id_user = $_SESSION['id_user'];
$user = $conn->query("SELECT * FROM users WHERE id_user = $id_user")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $username = $conn->real_escape_string($_POST['username']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi Password
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $error = "Konfirmasi password tidak cocok!";
        } else {
            // Update dengan password baru
            $sql = "UPDATE users SET nama='$nama', username='$username', phone_number='$phone_number', password='$password' WHERE id_user=$id_user";
        }
    } else {
        // Update tanpa password
        $sql = "UPDATE users SET nama='$nama', username='$username', phone_number='$phone_number' WHERE id_user=$id_user";
    }

    if (!$error) {
        if ($conn->query($sql)) {
            // Update Session Data
            $_SESSION['nama'] = $nama;
            $_SESSION['username'] = $username;
            $success = "Profil berhasil diperbarui.";
            
            // Handle File Upload
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $target_dir = "../uploads/profile/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
                $new_filename = "profile_" . $id_user . "_" . time() . "." . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_extension, $allowed_types)) {
                    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                        $db_path = "uploads/profile/" . $new_filename;
                        $conn->query("UPDATE users SET profile_picture='$db_path' WHERE id_user=$id_user");
                        $_SESSION['profile_picture'] = $db_path;
                        $success .= " Foto profil berhasil diunggah.";
                    } else {
                        $error = "Gagal mengunggah foto.";
                    }
                } else {
                    $error = "Hanya file JPG, JPEG, PNG, & GIF yang diperbolehkan.";
                }
            }
            
            // Refresh data user
            $user = $conn->query("SELECT * FROM users WHERE id_user = $id_user")->fetch_assoc();
            
        } else {
            $error = "Gagal memperbarui profil: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - EasyResto Admin</title>
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

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden bg-gray-50/50">
        <header class="bg-white shadow-sm z-10 px-8 py-5 flex justify-between items-center glass-effect sticky top-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Profil</h2>
                <p class="text-sm text-gray-500">Perbarui informasi akun Anda</p>
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
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="md:flex">
                        <!-- Left Side: Profile Picture & Basic Info -->
                        <div class="md:w-1/3 bg-pale-taupe p-8 text-white flex flex-col items-center justify-center text-center">
                            <div class="w-32 h-32 rounded-full bg-white/20 backdrop-blur-sm p-1 shadow-2xl mb-6 relative group cursor-pointer overflow-hidden transition-all duration-300 hover:scale-105">
                                <?php if (!empty($user['profile_picture']) && file_exists('../' . $user['profile_picture'])): ?>
                                    <img src="../<?php echo $user['profile_picture']; ?>" alt="Profile" class="w-full h-full object-cover rounded-full">
                                <?php else: ?>
                                    <div class="w-full h-full rounded-full bg-white/10 flex items-center justify-center text-4xl font-bold text-white">
                                        <?php echo substr($user['nama'], 0, 1); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Overlay for upload hint -->
                                <div onclick="document.getElementById('profile_picture').click()" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <i class="fas fa-camera text-2xl"></i>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold mb-1"><?php echo htmlspecialchars($user['nama']); ?></h3>
                            <p class="text-white/80 font-medium bg-white/10 px-3 py-1 rounded-full text-sm mb-6"><?php echo ucfirst($user['role']); ?></p>
                            
                            <div class="w-full text-left space-y-3 text-sm">
                                <div class="flex items-center">
                                    <i class="fas fa-user w-6 opacity-70"></i>
                                    <span class="opacity-90"><?php echo htmlspecialchars($user['username']); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-phone w-6 opacity-70"></i>
                                    <span class="opacity-90"><?php echo htmlspecialchars($user['phone_number'] ?? '-'); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-id-badge w-6 opacity-70"></i>
                                    <span class="opacity-90">ID: #<?php echo $user['id_user']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Edit Form -->
                        <div class="md:w-2/3 p-8">
                            <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Edit Informasi</h3>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <input type="file" name="profile_picture" id="profile_picture" class="hidden" accept="image/*" onchange="document.querySelector('.submit-btn').click()">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                                        <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pale-taupe transition-all bg-gray-50 focus:bg-white" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pale-taupe transition-all bg-gray-50 focus:bg-white" required>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                                    <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pale-taupe transition-all bg-gray-50 focus:bg-white" placeholder="08xxxxxxxxxx">
                                </div>

                                <div class="border-t border-gray-100 my-6 pt-6">
                                    <h4 class="text-md font-bold text-gray-700 mb-4 bg-yellow-50 inline-block px-3 py-1 rounded text-yellow-700"><i class="fas fa-lock mr-2"></i> Ganti Password</h4>
                                    <p class="text-xs text-gray-500 mb-4">Kosongkan jika tidak ingin mengubah password.</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-gray-700 text-sm font-bold mb-2">Password Baru</label>
                                            <input type="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pale-taupe transition-all bg-gray-50 focus:bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password</label>
                                            <input type="password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pale-taupe transition-all bg-gray-50 focus:bg-white">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <button type="submit" class="submit-btn bg-pale-taupe hover:bg-opacity-90 text-white font-bold py-3 px-8 rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300">
                                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
