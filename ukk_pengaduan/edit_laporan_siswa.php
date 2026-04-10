<?php
session_start();
require 'functions.php';

// 1. KEAMANAN (Logika Asli)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit;
}

$nis_siswa = $_SESSION['id'];

// 2. VALIDASI ID (Logika Asli)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: siswa.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// 3. AMBIL DATA (Logika Asli)
$query_data = "SELECT * FROM input_aspirasi WHERE Id_pelaporan = '$id'";
$result = mysqli_query($conn, $query_data);
$aspirasi = mysqli_fetch_assoc($result);

// CEK DATA (Logika Asli)
if (!$aspirasi) {
    echo "<script>alert('Data tidak ditemukan!'); document.location.href='siswa.php';</script>";
    exit;
}

// 4. AMBIL KATEGORI (Logika Asli)
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori");

// 5. PROSES UPDATE (Logika Asli)
if (isset($_POST['update'])) {
    $id_aspirasi = $_POST['id_aspirasi'];
    $id_kategori = $_POST['id_kategori'];
    $lokasi      = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $keterangan  = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $foto_lama   = $_POST['foto_lama'];

    if ($_FILES['foto']['error'] === 4) {
        $foto_final = $foto_lama;
    } else {
        $nama_file = $_FILES['foto']['name'];
        $tmp_name  = $_FILES['foto']['tmp_name'];
        $ekstensi  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $valid     = ['jpg', 'jpeg', 'png'];

        if (!in_array($ekstensi, $valid)) {
            echo "<script>alert('Format harus JPG/PNG');</script>";
            $foto_final = $foto_lama;
        } else {
            $foto_final = uniqid() . "." . $ekstensi;
            move_uploaded_file($tmp_name, './assets/img/' . $foto_final);

            if ($foto_lama != "" && file_exists('./assets/img/' . $foto_lama)) {
                unlink('./assets/img/' . $foto_lama);
            }
        }
    }

    $sql_update = "UPDATE input_aspirasi SET 
                    id_kategori = '$id_kategori',
                    lokasi      = '$lokasi',
                    ket         = '$keterangan',
                    foto        = '$foto_final'
                   WHERE Id_pelaporan = '$id_aspirasi'";

    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Berhasil diupdate!'); document.location.href='siswa.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan | Suara Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --accent: #0ea5e9;
            --bg-dark: #070b14;
            --card-bg: rgba(22, 30, 48, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 0; }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Dekorasi Glow */
        .glow { position: fixed; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, transparent 70%); z-index: -1; filter: blur(60px); top: -10%; right: -10%; }

        .container {
            width: 100%;
            max-width: 550px;
            background: var(--card-bg);
            padding: 30px;
            border-radius: 28px;
            border: 1px solid var(--border);
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        h2 {
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 25px;
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select, textarea {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            background: rgba(10, 15, 28, 0.6);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: white;
            font-size: 14px;
            transition: 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--accent);
            outline: none;
            background: rgba(10, 15, 28, 0.8);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
        }

        .img-preview {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid var(--border);
            margin-bottom: 15px;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        button {
            flex: 1;
            padding: 16px;
            border: none;
            border-radius: 16px;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-update {
            background: var(--accent);
            color: white;
            box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3);
        }

        .btn-update:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            border: 1px solid var(--border);
            text-decoration: none;
            display: flex;
            flex: 1;
        }

        .btn-back:hover { background: rgba(255, 255, 255, 0.1); color: white; }

        @media (max-width: 480px) {
            .container { padding: 20px; }
            h2 { font-size: 20px; }
            .btn-group { flex-direction: column; }
        }
    </style>
</head>

<body>

<div class="glow"></div>

<div class="container">
    <h2><i class="fa-solid fa-pen-to-square"></i> Edit Laporan</h2>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_aspirasi" value="<?= $aspirasi['Id_pelaporan']; ?>">
        <input type="hidden" name="foto_lama" value="<?= $aspirasi['foto']; ?>">

        <label>Kategori Laporan</label>
        <select name="id_kategori">
            <?php while($k = mysqli_fetch_assoc($kategori_list)) : ?>
                <option value="<?= $k['id_kategori']; ?>"
                    <?= ($k['id_kategori'] == $aspirasi['id_kategori']) ? 'selected' : ''; ?>>
                    <?= $k['ket_kategori']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Lokasi Kejadian</label>
        <input type="text" name="lokasi" value="<?= $aspirasi['lokasi']; ?>" placeholder="Misal: Kantin, Lapangan">

        <label>Keterangan Aspirasi</label>
        <textarea name="keterangan" rows="4" placeholder="Detail laporan..."><?= $aspirasi['ket']; ?></textarea>

        <label>Foto Saat Ini</label>
        <?php if ($aspirasi['foto'] != ""): ?>
            <img src="./assets/img/<?= $aspirasi['foto']; ?>" class="img-preview" alt="Preview">
        <?php else: ?>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px;">Tidak ada foto.</p>
        <?php endif; ?>

        <label>Ganti Foto (Opsional)</label>
        <input type="file" name="foto" style="padding: 10px; border: 1px dashed var(--border);">

        <div class="btn-group">
            <a href="siswa.php" class="btn-back">
                <button type="button" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </button>
            </a>
            <button type="submit" name="update" class="btn-update">
                Simpan Perubahan <i class="fa-solid fa-check"></i>
            </button>
        </div>
    </form>
</div>

</body>
</html>