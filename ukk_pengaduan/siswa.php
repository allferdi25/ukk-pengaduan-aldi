<?php
session_start();
require 'functions.php';

// 1. KEAMANAN AKSES (Logika Asli)
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header("Location: index.php");
    exit;
}

$nis = $_SESSION['id'];
$kategori = mysqli_query($conn, "SELECT * FROM kategori");

// 2. LOGIKA HAPUS (Logika Asli)
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    $cek = mysqli_query($conn, "SELECT foto FROM input_aspirasi WHERE id_pelaporan = '$id_hapus' AND nis = '$nis'");
    $data = mysqli_fetch_assoc($cek);

    if ($data) {
        if ($data['foto'] != "" && file_exists("./assets/img/" . $data['foto'])) {
            unlink("./assets/img/" . $data['foto']);
        }
        mysqli_query($conn, "DELETE FROM input_aspirasi WHERE id_pelaporan = '$id_hapus'");
        echo "<script>alert('Laporan berhasil dihapus!'); document.location.href='siswa.php';</script>";
    }
}

// 3. LOGIKA KIRIM LAPORAN (Logika Asli)
if (isset($_POST["kirim"])) {
    $isi_laporan = htmlspecialchars($_POST["isi_laporan"]);
    $id_kategori = $_POST["id_kategori"];
    $lokasi = htmlspecialchars($_POST["lokasi"]);
    $tanggal = date("Y-m-d");

    $nama_foto = $_FILES['foto']['name'];
    if($nama_foto != "") {
        $nama_foto_baru = uniqid() . "." . pathinfo($nama_foto, PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['foto']['tmp_name'], './assets/img/' . $nama_foto_baru);
    } else { $nama_foto_baru = ""; }

    mysqli_query($conn, "INSERT INTO input_aspirasi (nis, id_kategori, lokasi, ket, foto) 
                         VALUES ('$nis', '$id_kategori', '$lokasi', '$isi_laporan', '$nama_foto_baru')");
    header("Location: siswa.php");
}

// 4. PENGAMBILAN DATA RIWAYAT (Logika Asli)
$id_siswa = $_SESSION['id']; 
$query = "SELECT 
            input_aspirasi.*, 
            ket_kategori, 
            input_aspirasi.ket AS keterangan,
            COALESCE(aspirasi.status, 'MENUNGGU') AS status,
            aspirasi.feedback,
            input_aspirasi.id_pelaporan AS id_aspirasi,
            NOW() as tanggal 
        FROM input_aspirasi 
        JOIN kategori ON input_aspirasi.id_kategori = kategori.id_kategori 
        LEFT JOIN aspirasi ON input_aspirasi.id_pelaporan = aspirasi.id_aspirasi 
        WHERE input_aspirasi.nis = '$id_siswa'";

$riwayat = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard | Premium Suara Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        overflow-x: hidden;
    }

    .glow { position: fixed; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(14, 165, 233, 0.08) 0%, transparent 70%); z-index: -1; filter: blur(80px); top: -10%; right: -10%; }
    .glow-2 { bottom: -10%; left: -10%; background: radial-gradient(circle, rgba(139, 92, 246, 0.05) 0%, transparent 70%); }

    /* Navigation */
    nav { 
        background: rgba(13, 18, 30, 0.8);
        backdrop-filter: blur(15px);
        padding: 15px 5%; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        position: sticky; top: 0; z-index: 1000;
        border-bottom: 1px solid var(--border);
    }
    .logo { font-weight: 800; font-size: 18px; color: var(--accent); display: flex; align-items: center; gap: 8px; }
    .logout-btn { color: #fb7185; text-decoration: none; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 5px; }

    /* Layout Container */
    .container { 
        max-width: 1200px; 
        margin: 20px auto; 
        padding: 0 20px; 
        display: grid; 
        grid-template-columns: 380px 1fr; /* Sidebar form dan Main content */
        gap: 30px; 
    }

    .welcome-box { grid-column: span 2; margin-bottom: 10px; }
    .welcome-box h1 { font-size: 28px; font-weight: 800; background: linear-gradient(to right, #fff, var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    
    .stats-grid { display: flex; gap: 15px; margin-top: 20px; }
    .stat-card { 
        background: var(--card-bg); padding: 15px 20px; border-radius: 18px; 
        border: 1px solid var(--border); flex: 1; backdrop-filter: blur(10px);
    }
    .stat-card div { font-size: 20px; font-weight: 800; color: var(--accent); }

    /* Form Card */
    .card-form { 
        background: var(--card-bg); padding: 25px; border-radius: 24px; 
        border: 1px solid var(--border); backdrop-filter: blur(20px);
        height: fit-content; position: sticky; top: 100px;
    }
    .card-form h3 { margin-bottom: 20px; font-size: 18px; }

    input, select, textarea { 
        width: 100%; padding: 12px 15px; margin-bottom: 18px; 
        background: rgba(10, 15, 28, 0.6); border: 1px solid var(--border); 
        border-radius: 12px; color: white; font-size: 14px; outline: none;
    }
    input:focus, textarea:focus { border-color: var(--accent); }

    .btn-submit { 
        width: 100%; padding: 15px; background: var(--accent); color: white; border: none; 
        border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.3s;
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }

    /* History Cards */
    .history-item { 
        background: var(--card-bg); padding: 20px; border-radius: 24px; margin-bottom: 20px;
        border: 1px solid var(--border); backdrop-filter: blur(10px);
    }
    .item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; gap: 10px; }
    
    .status-pill { padding: 6px 12px; border-radius: 10px; font-size: 10px; font-weight: 800; white-space: nowrap; }
    .status-menunggu { background: rgba(244, 63, 94, 0.1); color: #fb7185; }
    .status-proses { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
    .status-selesai { background: rgba(16, 185, 129, 0.1); color: #34d399; }

    .content-body { display: flex; gap: 20px; }
    .content-text { flex: 1; min-width: 0; /* Mencegah teks meluap */ }
    .report-img { width: 100px; height: 100px; border-radius: 16px; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; }

    .desc { font-size: 14px; color: #cbd5e1; line-height: 1.6; word-wrap: break-word; }

    .actions { margin-top: 15px; display: flex; gap: 10px; padding-top: 15px; border-top: 1px solid var(--border); }
    .act-btn { flex: 1; padding: 10px; border-radius: 10px; font-size: 11px; text-decoration: none; font-weight: 700; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px; }
    .act-edit { background: rgba(251, 191, 36, 0.1); color: #fbbf24; }
    .act-del { background: rgba(244, 63, 94, 0.1); color: #fb7185; }

    /* =========================================
       RESPONSIVE LOGIC (Perbaikan Utama)
       ========================================= */
    
    /* Tablet & Small Desktop */
    @media (max-width: 1024px) {
        .container {
            grid-template-columns: 1fr; /* Ubah jadi 1 kolom saja */
            gap: 20px;
        }
        .welcome-box { grid-column: span 1; }
        .card-form { position: static; width: 100%; } /* Hilangkan sticky di mobile */
    }

    /* Mobile Phone */
    @media (max-width: 600px) {
        nav { padding: 15px 5%; }
        .nav-text { font-size: 14px; }
        .user-profile span { display: none; } /* Sembunyikan nama user di nav agar ringkas */

        .welcome-box h1 { font-size: 22px; }
        .stats-grid { flex-direction: row; } /* Biar card kecil-kecil ke samping */
        .stat-card { padding: 12px; }
        .stat-card div { font-size: 16px; }

        .content-body { 
            flex-direction: column-reverse; /* Foto pindah ke atas teks atau bawah teks */
            gap: 15px;
        }
        .report-img { 
            width: 100%; 
            height: 180px; /* Foto jadi lebar penuh di HP */
        }

        .item-header {
            flex-direction: row;
            align-items: center;
        }

        .actions {
            flex-wrap: wrap; /* Tombol edit/hapus tetap rapi */
        }
    }
</style>
</head>
<body>

<div class="glow"></div>
<div class="glow glow-2"></div>

<nav>
    <div class="logo">
        <i class="fa-solid fa-bolt-lightning"></i> <span class="nav-text">SUARA SEKOLAH</span>
    </div>
    <div class="user-profile">
        <span style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-right: 10px;">
            Siswa: <span style="color: var(--text-main)"><?= $_SESSION['nama']; ?></span>
        </span>
        <a href="logout.php" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar
        </a>
    </div>
</nav>

<div class="container">
    <div class="welcome-box">
        <h1>Dashboard Siswa</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Kontribusi untuk kenyamanan sekolah.</p>
        
        <div class="stats-grid">
            <div class="stat-card">
                <small>Laporan</small>
                <div><?= count($riwayat); ?></div>
            </div>
            <div class="stat-card">
                <small>Proses</small>
                <?php 
                    $prosesCount = count(array_filter($riwayat, function($r){ return strtoupper($r['status']) != 'SELESAI'; }));
                ?>
                <div><?= $prosesCount; ?></div>
            </div>
        </div>
    </div>

    <div class="card-form">
        <h3><i class="fa-solid fa-circle-plus"></i> Buat Laporan</h3>
        <form action="" method="post" enctype="multipart/form-data">
            <label>Kategori</label>
            <select name="id_kategori" required>
                <option value="">Pilih Kategori...</option>
                <?php foreach($kategori as $k) : ?>
                    <option value="<?= $k['id_kategori']; ?>"><?= $k['ket_kategori']; ?></option>
                <?php endforeach; ?>
            </select>

            <label>Lokasi</label>
            <input type="text" name="lokasi" placeholder="Lokasi kejadian..." required>

            <label>Detail</label>
            <textarea name="isi_laporan" rows="4" placeholder="Apa yang ingin dilaporkan?" required></textarea>

            <label>Foto Bukti</label>
            <input type="file" name="foto" style="border: 1px dashed var(--border); padding: 8px;">

            <button type="submit" name="kirim" class="btn-submit">
                Kirim <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <div class="history-section">
        <h3 style="margin-bottom: 20px; font-weight: 700;">Riwayat Aspirasi</h3>
        
        <?php if(empty($riwayat)) : ?>
            <div style="text-align: center; padding: 50px 20px; background: var(--card-bg); border-radius: 24px; border: 1px dashed var(--border);">
                <p style="color: var(--text-muted);">Belum ada laporan.</p>
            </div>
        <?php endif; ?>

        <?php foreach($riwayat as $row) : 
            $status_val = strtolower($row['status']);
        ?>
            <div class="history-item">
                <div class="item-header">
                    <div>
                        <span class="cat-tag"><?= $row['ket_kategori']; ?></span>
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">
                            <?= date('d M Y', strtotime($row['tanggal'])); ?>
                        </div>
                    </div>
                    <div class="status-pill status-<?= $status_val; ?>">
                        <?= strtoupper($row['status']); ?>
                    </div>
                </div>

                <div class="content-body">
                    <div class="content-text">
                        <div class="location"><i class="fa-solid fa-map-pin"></i> <?= $row['lokasi']; ?></div>
                        <p class="desc"><?= $row['keterangan']; ?></p>
                    </div>
                    <?php if($row['foto'] != ""): ?>
                        <img src="./assets/img/<?= $row['foto']; ?>" class="report-img" alt="Bukti">
                    <?php endif; ?>
                </div>

                <?php if($row['feedback'] != ""): ?>
                    <div class="feedback">
                        <strong style="color: var(--accent); font-size: 10px; display: block; margin-bottom: 5px;">RESPON ADMIN:</strong>
                        <?= $row['feedback']; ?>
                    </div>
                <?php endif; ?>

                <?php if(strtoupper($row['status']) == 'MENUNGGU') : ?>
                <div class="actions">
                    <a href="edit_laporan_siswa.php?id=<?= $row['id_aspirasi']; ?>" class="act-btn act-edit">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <a href="siswa.php?hapus=<?= $row['id_aspirasi']; ?>" class="act-btn act-del" onclick="return confirm('Hapus?')">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                    </a>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>