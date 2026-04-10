<?php
session_start();
require 'functions.php';

// Keamanan: Hanya admin yang bisa akses
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// QUERY DIPERBAIKI: Menggunakan Alias (AS) agar nama kolom sesuai dengan pemanggilan di HTML
// Dan memperbaiki relasi JOIN agar data status muncul dengan benar
$laporan = query("SELECT 
                    input_aspirasi.*, 
                    siswa.nama, 
                    kategori.ket_kategori AS nama_kategori,
                    COALESCE(aspirasi.status, 'MENUNGGU') AS status_laporan
                FROM input_aspirasi
                JOIN siswa ON input_aspirasi.nis = siswa.nis
                JOIN kategori ON input_aspirasi.id_kategori = kategori.id_kategori
                LEFT JOIN aspirasi ON input_aspirasi.Id_pelaporan = aspirasi.id_aspirasi");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Pengaduan</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; color: #333; }
        header { text-align: center; border-bottom: 3px double #6c5ce7; padding-bottom: 15px; margin-bottom: 30px; }
        header h2 { margin: 0; color: #6c5ce7; text-transform: uppercase; }
        header p { margin: 5px 0; font-size: 14px; color: #666; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        
        th { 
            background-color: #6c5ce7 !important; 
            color: white !important; 
            padding: 12px 8px; 
            font-size: 13px; 
            text-transform: uppercase;
            border: 1px solid #5b4bc4;
        }

        td { 
            border: 1px solid #ddd; 
            padding: 10px 8px; 
            font-size: 12px; 
            vertical-align: top;
        }

        tr:nth-child(even) { background-color: #f9f8ff; }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            display: inline-block;
        }
        /* Styling dinamis berdasarkan text status */
        .SELESAI { background-color: #d1fae5 !important; color: #065f46 !important; border: 1px solid #10b981; }
        .MENUNGGU { background-color: #fee2e2 !important; color: #991b1b !important; border: 1px solid #ef4444; }
        .PROSES { background-color: #fff7ed !important; color: #9a3412 !important; border: 1px solid #f97316; }

        .info-cetak { margin-top: 30px; text-align: right; font-size: 11px; color: #777; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
            th { background-color: #6c5ce7 !important; color: white !important; }
        }
    </style>
</head>
<body>

    <header>
        <h2>Laporan Aspirasi Siswa</h2>
        <p>SMK NEGERI REKAYASA PERANGKAT LUNAK</p>
        <p style="font-size: 12px;">Jl. Pendidikan No. 123, Kota Anda</p>
    </header>

    <div style="margin-bottom: 10px; font-size: 13px;">
        Daftar laporan masuk hingga: <strong><?= date('d F Y'); ?></strong>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="15%">Pelapor</th>
                <th width="12%">Kategori</th>
                <th width="12%">Lokasi</th>
                <th>Keterangan Laporan</th>
                <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach($laporan as $row) : ?>
            <tr>
                <td align="center"><?= $i++; ?></td>
                <td><?= !empty($row['tgl_input']) ? date('d/m/Y', strtotime($row['tgl_input'])) : '-'; ?></td>
                <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                <td><?= htmlspecialchars($row['lokasi'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['ket'] ?? $row['isi_laporan'] ?? '-'); ?></td>
                <td align="center">
                    <?php $st = strtoupper($row['status_laporan']); ?>
                    <span class="badge <?= $st; ?>">
                        <?= $st; ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="info-cetak">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Pengaduan Sekolah pada <?= date('d/m/Y H:i'); ?>
    </div>

    <script>
        window.print();
    </script>

</body>
</html>