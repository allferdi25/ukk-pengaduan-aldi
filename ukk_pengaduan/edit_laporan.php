<?php
session_start();
require 'functions.php';

// DEBUG BIAR ERROR KELIHATAN (Logika Asli)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PROTEKSI ADMIN (Logika Asli)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// VALIDASI ID (Logika Asli)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// AMBIL DATA (Logika Asli)
$query = mysqli_query($conn, "
    SELECT 
        input_aspirasi.*, 
        kategori.ket_kategori, 
        siswa.nama,
        aspirasi.status,
        aspirasi.feedback
    FROM input_aspirasi
    JOIN siswa ON input_aspirasi.nis = siswa.nis
    JOIN kategori ON input_aspirasi.id_kategori = kategori.id_kategori
    LEFT JOIN aspirasi ON input_aspirasi.Id_pelaporan = aspirasi.id_aspirasi
    WHERE input_aspirasi.Id_pelaporan = '$id'
");

$data = mysqli_fetch_assoc($query);

// CEK DATA (Logika Asli)
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); document.location.href='admin.php';</script>";
    exit;
}

// DEFAULT BIAR TIDAK NULL (Logika Asli)
$status_sekarang = $data['status'] ? $data['status'] : 'MENUNGGU';
$feedback_sekarang = $data['feedback'] ? $data['feedback'] : '';

// PROSES SUBMIT (Logika Asli)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $status = $_POST['status'];
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

    $cek = mysqli_query($conn, "SELECT * FROM aspirasi WHERE id_aspirasi = '$id'");

    if (mysqli_num_rows($cek) > 0) {
        $update = mysqli_query($conn, "
            UPDATE aspirasi 
            SET status='$status', feedback='$feedback'
            WHERE id_aspirasi='$id'
        ");
    } else {
        $update = mysqli_query($conn, "
            INSERT INTO aspirasi (id_aspirasi, id_kategori, nis, status, feedback)
            VALUES (
                '$id',
                '".$data['id_kategori']."',
                '".$data['nis']."',
                '$status',
                '$feedback'
            )
        ");
    }

    if ($update) {
        echo "<script>alert('Berhasil disimpan!'); document.location.href='admin.php';</script>";
    } else {
        echo "ERROR: " . mysqli_error($conn);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderasi Aspirasi | Admin</title>
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

        /* Dekorasi Glow Background */
        .glow { position: fixed; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, transparent 70%); z-index: -1; filter: blur(60px); top: -10%; right: -10%; }

        .container {
            width: 100%;
            max-width: 600px;
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
            margin-bottom: 5px;
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .pelapor-info {
            background: rgba(14, 165, 233, 0.1);
            padding: 12px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            border: 1px solid rgba(14, 165, 233, 0.2);
            font-size: 14px;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            margin-top: 15px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box {
            background: rgba(10, 15, 28, 0.6);
            padding: 14px;
            border-radius: 14px;
            border: 1px solid var(--border);
            font-size: 14px;
            color: #cbd5e1;
            line-height: 1.6;
        }

        img {
            margin-top: 10px;
            border-radius: 18px;
            border: 2px solid var(--border);
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }

        form {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid var(--border);
        }

        select, textarea {
            width: 100%;
            padding: 14px;
            background: rgba(10, 15, 28, 0.8);
            border: 1px solid var(--accent);
            border-radius: 14px;
            color: white;
            font-size: 14px;
            margin-top: 5px;
            transition: 0.3s;
        }

        textarea { height: 100px; resize: vertical; }

        button {
            margin-top: 25px;
            padding: 16px;
            width: 100%;
            border: none;
            background: var(--accent);
            color: white;
            font-weight: 800;
            border-radius: 16px;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        button:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-link:hover { color: white; }

        @media (max-width: 480px) {
            .container { padding: 20px; }
            h2 { font-size: 20px; }
        }
    </style>
</head>
<body>

<div class="glow"></div>

<div class="container">
    <h2>Moderasi Aspirasi</h2>
    <p class="subtitle">Kelola status dan berikan tanggapan laporan.</p>

    <div class="pelapor-info">
        <i class="fa-solid fa-user-tag" style="color: var(--accent); margin-right: 8px;"></i>
        <b>Pelapor:</b> <?php echo $data['nama']; ?>
    </div>

    <label>Kategori</label>
    <div class="info-box"><?php echo $data['ket_kategori']; ?></div>

    <label>Lokasi</label>
    <div class="info-box"><?php echo $data['lokasi']; ?></div>

    <label>Keterangan Laporan</label>
    <div class="info-box"><?php echo $data['ket']; ?></div>

    <label>Foto Bukti</label>
    <?php if (!empty($data['foto']) && file_exists("./assets/img/" . $data['foto'])) { ?>
        <img src="./assets/img/<?php echo $data['foto']; ?>" alt="Foto Laporan">
    <?php } else { ?>
        <div class="info-box" style="font-style: italic; color: #64748b;">Tidak ada foto bukti yang diunggah.</div>
    <?php } ?>

    <form method="POST" action="">
        <label><i class="fa-solid fa-signal" style="margin-right: 5px;"></i> Update Status</label>
        <select name="status" required>
            <option value="MENUNGGU" <?php if($status_sekarang=='MENUNGGU') echo 'selected'; ?>>Menunggu</option>
            <option value="PROSES" <?php if($status_sekarang=='PROSES') echo 'selected'; ?>>Sedang Diproses</option>
            <option value="SELESAI" <?php if($status_sekarang=='SELESAI') echo 'selected'; ?>>Selesai / Tuntas</option>
        </select>

        <label><i class="fa-solid fa-comment-dots" style="margin-right: 5px;"></i> Feedback / Balasan Admin</label>
        <textarea name="feedback" required placeholder="Tuliskan alasan atau tindak lanjut laporan ini..."><?php echo $feedback_sekarang; ?></textarea>

        <button type="submit">
            Simpan Perubahan <i class="fa-solid fa-paper-plane"></i>
        </button>
    </form>

    <a href="admin.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Panel Admin
    </a>
</div>

</body>
</html>