<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "easyresto");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Jakarta');
?>

