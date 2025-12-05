<?php
// Buat tabel users
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id_user INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','owner','kasir') NOT NULL,
    nama VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_user),
    UNIQUE KEY username (username)
)";

if ($conn->query($sql_users) === FALSE) {
    echo "Error creating table users: " . $conn->error;
}

// Buat tabel kategori_menu
$sql_kategori = "CREATE TABLE IF NOT EXISTS kategori_menu (
    id_kategori INT(11) NOT NULL AUTO_INCREMENT,
    nama_kategori VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_kategori)
)";

if ($conn->query($sql_kategori) === FALSE) {
    echo "Error creating table kategori_menu: " . $conn->error;
}

// Buat tabel menu
$sql_menu = "CREATE TABLE IF NOT EXISTS menu (
    id_menu INT(11) NOT NULL AUTO_INCREMENT,
    nama_menu VARCHAR(100) NOT NULL,
    harga INT(11) NOT NULL,
    id_kategori INT(11) DEFAULT NULL,
    PRIMARY KEY (id_menu),
    KEY id_kategori (id_kategori)
)";

if ($conn->query($sql_menu) === FALSE) {
    echo "Error creating table menu: " . $conn->error;
}

// Buat tabel transaksi
$sql_transaksi = "CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi INT(11) NOT NULL AUTO_INCREMENT,
    nama_pelanggan VARCHAR(100) NOT NULL,
    tanggal TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal INT(11) DEFAULT 0,
    ppn INT(11) DEFAULT 0,
    service INT(11) DEFAULT 0,
    total INT(11) DEFAULT 0,
    PRIMARY KEY (id_transaksi)
)";

if ($conn->query($sql_transaksi) === FALSE) {
    echo "Error creating table transaksi: " . $conn->error;
}

// Buat tabel detail_transaksi
$sql_detail = "CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail INT(11) NOT NULL AUTO_INCREMENT,
    id_transaksi INT(11) DEFAULT NULL,
    id_menu INT(11) DEFAULT NULL,
    jumlah INT(11) NOT NULL,
    subtotal INT(11) DEFAULT 0,
    PRIMARY KEY (id_detail),
    KEY id_transaksi (id_transaksi),
    KEY id_menu (id_menu)
)";

if ($conn->query($sql_detail) === FALSE) {
    echo "Error creating table detail_transaksi: " . $conn->error;
}

// Insert data sample
insertSampleData($conn);

function insertSampleData($conn) {
    // Cek apakah data sudah ada
    $check = $conn->query("SELECT COUNT(*) as total FROM users");
    $row = $check->fetch_assoc();
    
    if ($row['total'] == 0) {
        // Insert users
        $conn->query("INSERT INTO users (username, password, role, nama) VALUES 
            ('owner', 'owner123', 'owner', 'Pemilik Restoran'),
            ('admin', 'admin123', 'admin', 'Administrator'),
            ('kasir', 'kasir123', 'kasir', 'Kasir Restoran')");
        
        // Insert kategori
        $conn->query("INSERT INTO kategori_menu (id_kategori, nama_kategori) VALUES 
            (1, 'Makanan'),
            (2, 'Minuman'),
            (3, 'Makanan Penutup')");
        
        // Insert menu
        $conn->query("INSERT INTO menu (id_menu, nama_menu, harga, id_kategori) VALUES 
            (1, 'Nasi Goreng Spesial', 25000, 1),
            (2, 'Ayam Bakar Madu', 30000, 1),
            (3, 'Sate Ayam', 28000, 1),
            (11, 'Es Teh Manis', 8000, 2),
            (12, 'Es Jeruk Segar', 10000, 2),
            (13, 'Jus Alpukat', 15000, 2),
            (16, 'Puding Coklat', 15000, 3),
            (17, 'Cheesecake', 20000, 3)");
        
        // Insert transaksi sample
        $conn->query("INSERT INTO transaksi (id_transaksi, nama_pelanggan, tanggal, subtotal, ppn, service, total) VALUES 
            (1, 'Andi', '2025-11-05 09:41:28', 73000, 7300, 1825, 82125),
            (2, 'Budi', '2025-11-05 09:41:28', 76000, 7600, 1900, 85500)");
        
        // Insert detail transaksi
        $conn->query("INSERT INTO detail_transaksi (id_detail, id_transaksi, id_menu, jumlah, subtotal) VALUES 
            (1, 1, 1, 2, 50000),
            (2, 1, 11, 1, 8000),
            (3, 1, 16, 1, 15000),
            (4, 2, 3, 1, 28000),
            (5, 2, 13, 2, 30000),
            (6, 2, 17, 1, 20000)");
    }
}
?>