<?php
session_start();
require 'functions.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// HAPUS MASAL (Logika Tetap)
if (isset($_POST['hapus_masal'])) {
    if (!empty($_POST['id_terpilih'])) {
        $ids = array_map('intval', $_POST['id_terpilih']);
        $all_id = implode(',', $ids); 
        mysqli_query($conn, "DELETE FROM aspirasi WHERE id_aspirasi IN ($all_id)");
        mysqli_query($conn, "DELETE FROM input_aspirasi WHERE Id_pelaporan IN ($all_id)");
        echo "<script>alert('Berhasil dihapus!'); document.location.href='admin.php';</script>";
    }
}

// KATEGORI
$kategori_list = query("SELECT * FROM kategori");

// QUERY UTAMA
$query_dasar = "SELECT 
    input_aspirasi.*, 
    siswa.nama, 
    kategori.ket_kategori,
    aspirasi.status,
    input_aspirasi.Id_pelaporan AS id_utama
FROM input_aspirasi
JOIN siswa ON input_aspirasi.nis = siswa.nis
JOIN kategori ON input_aspirasi.id_kategori = kategori.id_kategori
LEFT JOIN aspirasi ON input_aspirasi.Id_pelaporan = aspirasi.id_aspirasi
WHERE 1=1";

if (isset($_POST['cari'])) {
    if (!empty($_POST['id_kategori'])) {
        $id_kat = mysqli_real_escape_string($conn, $_POST['id_kategori']);
        $query_dasar .= " AND input_aspirasi.id_kategori = '$id_kat'";
    }
    if (!empty($_POST['status'])) {
        $stat = mysqli_real_escape_string($conn, $_POST['status']);
        $query_dasar .= " AND aspirasi.status = '$stat'";
    }
}

$laporan = query($query_dasar);

// STATISTIK
$total_laporan = count($laporan);
$total_pending = count(array_filter($laporan, function($item) { 
    return strtolower($item['status'] ?? '') == 'menunggu'; 
}));
$total_selesai = count(array_filter($laporan, function($item) { 
    return strtolower($item['status'] ?? '') == 'selesai'; 
}));
$persen_selesai = ($total_laporan > 0) ? ($total_selesai / $total_laporan) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Premium Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --accent: #0ea5e9;
            --bg-dark: #070b14;
            --card-bg: rgba(22, 30, 48, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --danger: #ff4757;
            --success: #2ed573;
            --warning: #ffa502;
        }
        
        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 0; }
        body { background-color: var(--bg-dark); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        .glow { position: fixed; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, transparent 70%); z-index: -1; filter: blur(50px); pointer-events: none; }
        .glow-1 { top: -10%; left: 20%; }
        .glow-2 { bottom: -10%; right: 10%; background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%); }

        /* HEADER MOBILE */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 70px;
            background: rgba(10, 15, 28, 0.9);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border);
            z-index: 999;
            padding: 0 25px;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar { 
            width: 280px; background: rgba(10, 15, 28, 0.95); border-right: 1px solid var(--border); 
            padding: 35px 25px; position: fixed; height: 100%; z-index: 1000; backdrop-filter: blur(20px); transition: 0.4s;
        }
        .sidebar h2 { font-size: 20px; font-weight: 800; margin-bottom: 45px; color: #fff; display: flex; align-items: center; gap: 12px; }
        .sidebar a { display: flex; align-items: center; gap: 14px; color: #64748b; text-decoration: none; padding: 14px 20px; border-radius: 16px; margin-bottom: 10px; transition: 0.3s; font-size: 14px; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background: linear-gradient(90deg, rgba(14, 165, 233, 0.15), transparent); color: var(--accent); }
        .sidebar a.logout { margin-top: 50px; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.1); }

        .main-content { margin-left: 280px; width: calc(100% - 280px); padding: 50px; position: relative; z-index: 1; transition: 0.4s; }
        
        /* RESPONSIVE ADJUSTMENTS */
        @media (max-width: 1024px) {
            .mobile-header { display: flex; }
            .sidebar { transform: translateX(-100%); width: 280px; }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; padding: 100px 20px 30px 20px; }
            
            .header-title h1 { font-size: 24px; }
            .stats-container { grid-template-columns: 1fr; }
            .table-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        }

        .progress-box { background: var(--card-bg); padding: 20px; border-radius: 20px; border: 1px solid var(--border); margin-bottom: 30px; }
        .progress-bar-bg { width: 100%; height: 8px; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; margin-top: 10px; }
        .progress-bar-fill { height: 100%; background: var(--accent); box-shadow: 0 0 15px var(--accent); transition: 1s; }

        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--card-bg); padding: 30px; border-radius: 28px; border: 1px solid var(--border); transition: 0.4s; }
        .stat-card:hover { border-color: var(--accent); }
        .stat-icon { font-size: 26px; margin-bottom: 15px; width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; border-radius: 18px; }

        .glass-card { background: var(--card-bg); border-radius: 30px; border: 1px solid var(--border); backdrop-filter: blur(15px); overflow: hidden; }
        .table-header { padding: 25px 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th { padding: 20px 30px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 22px 30px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 14px; color: #cbd5e1; }

        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 700; }
        .pulse { width: 6px; height: 6px; border-radius: 50%; background: currentColor; animation: pulse-anim 1.5s infinite; }
        @keyframes pulse-anim { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(2.5); opacity: 0; } }

        .selesai { background: rgba(46, 213, 115, 0.1); color: var(--success); }
        .proses { background: rgba(255, 165, 2, 0.1); color: var(--warning); }
        .menunggu { background: rgba(255, 71, 87, 0.1); color: var(--danger); }

        .btn-premium { background: var(--accent); color: white; border: none; padding: 12px 25px; border-radius: 14px; font-weight: 700; cursor: pointer; transition: 0.3s; width: fit-content; }
        
        .txt-nama { font-weight: 700; color: #fff; font-size: 15px; display: block; margin-bottom: 4px; }
        .txt-jam { font-size: 13px; color: #94a3b8; font-weight: 500; }
        
        .btn-action-icon { font-size: 18px; color: #64748b; transition: 0.3s; text-decoration: none; cursor: pointer; }
        .btn-action-icon:hover.edit { color: var(--accent); }
        .btn-action-icon:hover.del { color: var(--danger); }

        /* Overlay saat sidebar mobile aktif */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .sidebar-overlay.active { display: block; }
    </style>
</head>
<body>

    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="mobile-header">
        <h2 style="font-size: 18px;"><i class="fa-solid fa-shield-cat"></i> CORE</h2>
        <div id="menuToggle" style="font-size: 24px; cursor: pointer;"><i class="fa-solid fa-bars-staggered"></i></div>
    </div>
    <div class="sidebar-overlay" id="overlay"></div>

    <div class="sidebar" id="sidebar">
        <h2><i class="fa-solid fa-shield-cat"></i> CORE PANEL</h2>
        <a href="admin.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="print.php" target="_blank"><i class="fa-solid fa-print"></i> Cetak Laporan</a>
        <a href="logout.php" class="logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="header-section" style="margin-bottom: 40px;">
            <div class="header-title">
                <h1>Dashboard Pusat 🚀</h1>
                <p style="color: #64748b;">Pantau dan eksekusi aspirasi siswa hari ini.</p>
            </div>
        </div>

        <div class="progress-box">
            <div style="display: flex; justify-content: space-between; font-size: 13px; color: #94a3b8;">
                <span>Efektivitas Penyelesaian</span>
                <span style="color: var(--accent); font-weight: 700;"><?= round($persen_selesai); ?>% Teratasi</span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?= $persen_selesai; ?>%"></div>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(14, 165, 233, 0.1); color: var(--accent);"><i class="fa-solid fa-layer-group"></i></div>
                <div class="stat-info"><span>Total Laporan</span><h2><?= $total_laporan; ?></h2></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 71, 87, 0.1); color: var(--danger);"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="stat-info"><span>Menunggu Tindakan</span><h2><?= $total_pending; ?></h2></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(46, 213, 115, 0.1); color: var(--success);"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-info"><span>Laporan Selesai</span><h2><?= $total_selesai; ?></h2></div>
            </div>
        </div>

        <div class="glass-card" style="margin-bottom: 30px; padding: 25px;">
            <form action="" method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
                <div>
                    <label style="font-size: 11px; color: #475569; font-weight: 700; margin-bottom: 8px; display: block;">KATEGORI</label>
                    <select name="id_kategori" style="width:100%; padding:12px; background:#0a0f1c; border:1px solid var(--border); color:white; border-radius:12px; outline:none;">
                        <option value="">Semua Kategori</option>
                        <?php foreach($kategori_list as $k) : ?>
                            <option value="<?= $k['id_kategori']; ?>"><?= htmlspecialchars($k['ket_kategori']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size: 11px; color: #475569; font-weight: 700; margin-bottom: 8px; display: block;">STATUS</label>
                    <select name="status" style="width:100%; padding:12px; background:#0a0f1c; border:1px solid var(--border); color:white; border-radius:12px; outline:none;">
                        <option value="">Semua Status</option>
                        <option value="menunggu">🔴 Menunggu</option>
                        <option value="proses">🟠 Proses</option>
                        <option value="selesai">🟢 Selesai</option>
                    </select>
                </div>
                <button type="submit" name="cari" class="btn-premium"><i class="fa-solid fa-filter"></i> Filter</button>
            </form>
        </div>

        <form action="" method="post">
            <button type="submit" name="hapus_masal" class="btn-bulk" id="btnHapusMasal" 
                    style="display:none; background:var(--danger); color:white; border:none; padding:12px 20px; border-radius:12px; margin-bottom:15px; cursor:pointer; font-weight:700;">
                <i class="fa-solid fa-trash-can"></i> Hapus terpilih
            </button>
            
            <div class="glass-card">
                <div class="table-header">
                    <h3 style="font-size: 16px; font-weight: 700;">List Laporan</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>Pelapor</th>
                                <th>Kategori</th>
                                <th>Foto</th>
                                <th>Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($laporan)): ?>
                                <tr><td colspan="6" style="text-align:center; padding:50px; color:#64748b;">Data tidak ditemukan.</td></tr>
                            <?php endif; ?>

                            <?php foreach($laporan as $row) : ?>
                            <tr>
                                <td><input type="checkbox" name="id_terpilih[]" value="<?= $row['id_utama']; ?>" class="checkItem"></td>
                                <td>
                                    <span class="txt-nama"><?= htmlspecialchars($row['nama']); ?></span>
                                    <span class="txt-jam">NIS: <?= htmlspecialchars($row['nis']); ?></span>
                                </td>
                                <td><span style="background: rgba(14, 165, 233, 0.1); color: var(--accent); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;"><?= htmlspecialchars($row['ket_kategori']); ?></span></td>
                                <td>
                                    <?php if (!empty($row['foto'])): ?>
                                        <img src="./assets/img/<?= htmlspecialchars($row['foto']); ?>" 
                                             style="width:50px; height:50px; object-fit:cover; border-radius:10px; border: 1px solid var(--border);">
                                    <?php else: ?>
                                        <span style="color:#475569; font-size:12px;">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $st = strtolower($row['status'] ?? 'menunggu'); ?>
                                    <span class="badge <?= $st; ?>">
                                        <?php if($st == 'menunggu') echo '<span class="pulse"></span>'; ?>
                                        <?= strtoupper($st); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 20px; justify-content: center;">
                                        <a href="edit_laporan.php?id=<?= $row['id_utama']; ?>" class="btn-action-icon edit" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="hapus_laporan.php?id=<?= $row['id_utama']; ?>" onclick="return confirm('Hapus data ini?')" class="btn-action-icon del" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <script>
        // TOGGLE SIDEBAR MOBILE
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        menuToggle.onclick = () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        };

        overlay.onclick = () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        };

        // CHECKBOX LOGIC (TETAP)
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.checkItem');
        const btnBulk = document.getElementById('btnHapusMasal');

        if(checkAll) {
            checkAll.onclick = function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                toggleBtn();
            };
        }
        
        checkboxes.forEach(cb => {
            cb.onclick = toggleBtn;
        });

        function toggleBtn() {
            const checkedCount = document.querySelectorAll('.checkItem:checked').length;
            btnBulk.style.display = checkedCount > 0 ? 'inline-block' : 'none';
        }
    </script>
</body>
</html>