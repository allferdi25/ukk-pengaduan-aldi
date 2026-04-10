<?php
session_start();
require 'functions.php';

// Cek keamanan login admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil dan bersihkan ID dari URL
$id = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Hapus dulu dari tabel aspirasi (tabel tanggapan/anak)
$hapus_tanggapan = mysqli_query($conn, "DELETE FROM aspirasi WHERE id_aspirasi = '$id'");

// 2. Hapus dari tabel input_aspirasi (tabel utama/induk)
$hapus_utama = mysqli_query($conn, "DELETE FROM input_aspirasi WHERE Id_pelaporan = '$id'");

if ($hapus_utama) {
    echo "
        <script>
            alert('Data berhasil dihapus dari sistem!');
            document.location.href = 'admin.php';
        </script>
    ";
} else {
    echo "
        <script>
            alert('Gagal menghapus data utama: " . mysqli_error($conn) . "');
            document.location.href = 'admin.php';
        </script>
    ";
}
?>