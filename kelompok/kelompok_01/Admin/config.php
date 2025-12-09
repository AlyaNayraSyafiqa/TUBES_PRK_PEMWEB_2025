<?php
session_start();

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Koneksi database
$conn = new mysqli("localhost", "root", "", "easyresto");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');
?>
