<?php
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $nama = $conn->real_escape_string($_POST['nama']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        $check = $conn->query("SELECT id_user FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $error = "Username sudah ada!";
        } else {
            $sql = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')";
            if ($conn->query($sql)) {
                $success = "Pengguna berhasil ditambahkan.";
            } else {
                $error = "Gagal menambahkan pengguna.";
            }
        }
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id_user'];
        $nama = $conn->real_escape_string($_POST['nama']);
        $username = $conn->real_escape_string($_POST['username']);
        $role = $_POST['role'];
        $password = $_POST['password'];

        $sql = "UPDATE users SET nama='$nama', username='$username', role='$role'";
        if (!empty($password)) {
            $sql .= ", password='$password'";
        }
        $sql .= " WHERE id_user=$id";

        if ($conn->query($sql)) {
            $success = "Pengguna berhasil diperbarui.";
        } else {
            $error = "Gagal memperbarui pengguna.";
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id_user'];
        if ($conn->query("DELETE FROM users WHERE id_user=$id")) {
            $success = "Pengguna berhasil dihapus.";
        } else {
            $error = "Gagal menghapus pengguna.";
        }
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY id_user DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - EasyResto Admin</title>
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
        function openEditModal(user) {
            document.getElementById('edit_id_user').value = user.id_user;
            document.getElementById('edit_nama').value = user.nama;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }
        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
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
            <a href="manajemen_pengguna.php" class="flex items-center px-6 py-4 bg-white/20 border-l-4 border-white transition-all duration-200">
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
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
                <p class="text-sm text-gray-500">Kelola data pengguna sistem</p>
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
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Pengguna</h3>
                    <button onclick="openAddModal()" class="bg-pale-taupe hover:bg-opacity-90 text-white px-4 py-2 rounded shadow transition-colors">
                        <i class="fas fa-plus mr-2"></i> Tambah Pengguna
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="py-3 border-b text-gray-600">ID</th>
                                <th class="py-3 border-b text-gray-600">Nama</th>
                                <th class="py-3 border-b text-gray-600">Username</th>
                                <th class="py-3 border-b text-gray-600">Role</th>
                                <th class="py-3 border-b text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $users->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 border-b">#<?php echo $user['id_user']; ?></td>
                                <td class="py-3 border-b"><?php echo htmlspecialchars($user['nama']); ?></td>
                                <td class="py-3 border-b"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="py-3 border-b">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        <?php 
                                            echo $user['role'] == 'admin' ? 'bg-purple-100 text-purple-800' :
                                                ($user['role'] == 'owner' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800');
                                        ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="py-3 border-b">
                                    <button onclick='openEditModal(<?php echo json_encode($user); ?>)' class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id_user'] != $_SESSION['id_user']): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                        <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">
                                        <button type="submit" name="delete" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="addModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Tambah Pengguna</h3>
            <form method="POST">
                <input type="hidden" name="add" value="1">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                    <input type="text" name="nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                    <select name="role" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="kasir">Kasir</option>
                        <option value="owner">Owner</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeAddModal()" class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">Batal</button>
                    <button type="submit" class="bg-pale-taupe text-white px-4 py-2 rounded hover:bg-opacity-90">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Edit Pengguna</h3>
            <form method="POST">
                <input type="hidden" name="edit" value="1">
                <input type="hidden" name="id_user" id="edit_id_user">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                    <input type="text" name="nama" id="edit_nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" name="username" id="edit_username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                    <select name="role" id="edit_role" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="kasir">Kasir</option>
                        <option value="owner">Owner</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">Batal</button>
                    <button type="submit" class="bg-pale-taupe text-white px-4 py-2 rounded hover:bg-opacity-90">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
