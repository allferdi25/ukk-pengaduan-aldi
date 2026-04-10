<?php
session_start();
require 'functions.php';

// 1. KEAMANAN: Cek apakah yang masuk benar-benar Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// 2. AMBIL DATA: Ambil ID dari URL dan detail laporannya
$id = $_GET["id"];
$result = query("SELECT aspirasi.*, siswa.nama, kategori.nama_kategori 
                  FROM aspirasi 
                  JOIN siswa ON aspirasi.nis = siswa.nis 
                  JOIN kategori ON aspirasi.id_kategori = kategori.id_kategori 
                  WHERE id_aspirasi = $id");

if(!$result) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='admin.php';</script>";
    exit;
}
$laporan = $result[0]; 

// 3. LOGIKA UPDATE: Ketika tombol Simpan ditekan
if (isset($_POST["update"])) {
    $status_baru = $_POST["status"];
    $feedback_baru = mysqli_real_escape_string($conn, $_POST["feedback"]);
    
    $query = "UPDATE aspirasi SET 
              status = '$status_baru', 
              feedback = '$feedback_baru' 
              WHERE id_aspirasi = $id";
              
    mysqli_query($conn, $query);

    if (mysqli_affected_rows($conn) > 0) {
        echo "<script>
                alert('Laporan Berhasil Ditanggapi!');
                document.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script>
                alert('Tidak ada perubahan data');
                document.location.href = 'admin.php';
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Laporan | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #6c5ce7; 
            --primary-light: #a29bfe;
            --text: #2d3436; 
            --white: #ffffff; 
        }

        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text); 
            padding: 40px 20px; 
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Dekorasi Bola di Background */
        body::before, body::after {
            content: "";
            position: fixed;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: -1;
        }
        body::before { top: -100px; left: -100px; }
        body::after { bottom: -100px; right: -100px; }

        .container { max-width: 800px; margin: 0 auto; position: relative; z-index: 1; }
        
        .header { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            margin-bottom: 30px; 
            color: white;
        }

        .btn-back { 
            text-decoration: none; 
            color: white; 
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 500; 
            font-size: 13px; 
            transition: 0.3s;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .btn-back:hover { background: rgba(255,255,255,0.3); border-color: white; transform: translateX(-5px); }

        /* Card Utama dengan Border */
        .card { 
            background: var(--white); 
            padding: 40px; 
            border-radius: 30px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.2); 
            border: 2px solid rgba(108, 92, 231, 0.1);
        }
        
        .section-title { 
            font-size: 13px; 
            font-weight: 700; 
            color: var(--primary); 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            margin-bottom: 25px; 
            display: block; 
            border-bottom: 2px solid var(--primary-light); 
            padding-bottom: 10px; 
        }

        /* Info Grid dengan Aksen Border */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 35px; }
        .info-item {
            padding: 12px;
            background: #fbfaff;
            border-left: 4px solid var(--primary-light);
            border-radius: 0 12px 12px 0;
        }
        .info-item label { display: block; font-size: 11px; color: #999; margin-bottom: 5px; font-weight: 600; }
        .info-item p { font-weight: 600; font-size: 15px; color: #333; }

        /* Kotak Detail Laporan Berwarna */
        .report-content { 
            background: #f8faff; 
            border: 2px solid var(--primary-light); 
            padding: 25px; 
            border-radius: 20px; 
            margin-bottom: 35px; 
            box-shadow: inset 0 0 15px rgba(108, 92, 231, 0.05);
        }
        .report-content label { display: block; font-size: 12px; color: var(--primary); font-weight: 700; margin-bottom: 10px; }
        .report-content p { line-height: 1.8; font-size: 14px; color: #444; }

        /* Preview Foto dengan Border Tebal */
        .photo-preview { 
            margin-top: 20px; 
            border-radius: 15px; 
            overflow: hidden; 
            border: 4px solid var(--primary-light); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            width: fit-content; 
        }
        .photo-preview img { display: block; max-width: 100%; height: auto; max-height: 400px; }

        /* Form Styling dengan Fokus Border Berwarna */
        form label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 12px; color: var(--text); }
        select, textarea { 
            width: 100%; 
            padding: 15px; 
            border: 2px solid #e0e0e0; 
            border-radius: 15px; 
            outline: none; 
            transition: 0.3s; 
            background: #fafafa; 
            font-size: 14px; 
            margin-bottom: 25px; 
        }
        select:focus, textarea:focus { 
            border-color: var(--primary); 
            background: #fff; 
            box-shadow: 0 0 15px rgba(108, 92, 231, 0.2); 
        }

        /* Button Simpan */
        .btn-save { 
            width: 100%; 
            padding: 18px; 
            background: linear-gradient(to right, #6c5ce7, #a29bfe); 
            color: white; 
            border: none; 
            border-radius: 15px; 
            font-weight: 600; 
            font-size: 16px; 
            cursor: pointer; 
            transition: 0.4s; 
            box-shadow: 0 10px 20px rgba(108, 92, 231, 0.3); 
        }
        .btn-save:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(108, 92, 231, 0.4); filter: brightness(1.1); }

        /* Badge Status dengan Border */
        .status-badge { padding: 6px 15px; border-radius: 50px; font-size: 11px; font-weight: 700; display: inline-block; text-transform: uppercase; border: 1px solid rgba(0,0,0,0.05); }
        .Menunggu { background: #fee2e2; color: #dc2626; border-color: #fecdd3; }
        .Proses { background: #fffbeb; color: #d97706; border-color: #fef3c7; }
        .Selesai { background: #f0fdf4; color: #16a34a; border-color: #dcfce7; }

        @media (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
            .card { padding: 25px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <a href="admin.php" class="btn-back">← Kembali</a>
        <h1>Proses Aduan</h1>
        <div style="width: 40px;"></div>
    </div>

    <div class="card">
        <span class="section-title">Informasi Pengirim</span>
        <div class="info-grid">
            <div class="info-item">
                <label>Nama Siswa</label>
                <p><?= $laporan['nama']; ?></p>
            </div>
            <div class="info-item">
                <label>Kategori Masalah</label>
                <p><?= $laporan['nama_kategori']; ?></p>
            </div>
            <div class="info-item">
                <label>Tanggal Laporan</label>
                <p><?= date('d M Y', strtotime($laporan['tanggal'])); ?></p>
            </div>
            <div class="info-item">
                <label>Status Saat Ini</label>
                <div><span class="status-badge <?= $laporan['status']; ?>"><?= $laporan['status']; ?></span></div>
            </div>
        </div>

        <div class="report-content">
            <label>Pesan Aduan:</label>
            <p><?= nl2br($laporan['keterangan']); ?></p>
            
            <?php if($laporan['foto']): ?>
                <div class="photo-preview">
                    <img src="assets/img/<?= $laporan['foto']; ?>" alt="Bukti Foto">
                </div>
            <?php else: ?>
                <p style="margin-top:15px; color: #bbb; font-style: italic;">Siswa tidak melampirkan foto.</p>
            <?php endif; ?>
        </div>

        <span class="section-title">Berikan Tanggapan</span>
        <form action="" method="post">
            <label>Update Status</label>
            <select name="status" required>
                <option value="Menunggu" <?= ($laporan['status'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                <option value="Proses" <?= ($laporan['status'] == 'Proses') ? 'selected' : ''; ?>>Sedang Diproses</option>
                <option value="Selesai" <?= ($laporan['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai / Teratasi</option>
            </select>

            <label>Tanggapan Anda</label>
            <textarea name="feedback" rows="5" placeholder="Tuliskan pesan balasan atau solusi untuk siswa..." required><?= $laporan['feedback']; ?></textarea>

            <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
        </form>
    </div>
</div>

</body>
</html>