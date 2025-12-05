<?php
$host = "localhost";
$username = "root"; // Sesuaikan dengan username MySQL Anda
$password = ""; // Sesuaikan dengan password MySQL Anda
$database = "easyresto";

// Buat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter set
$conn->set_charset("utf8mb4");

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>