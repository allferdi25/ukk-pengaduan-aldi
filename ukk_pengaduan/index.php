<?php
session_start();
require 'functions.php';

// --- LOGIKA REGISTER ---
if (isset($_POST["register"])) {
    $nis = htmlspecialchars($_POST["nis"]);
    $nama = htmlspecialchars($_POST["nama"]);
    $kelas = htmlspecialchars($_POST["kelas"]);
    $password = $_POST["password"];
    
    $cek_nis = mysqli_query($conn, "SELECT nis FROM siswa WHERE nis = '$nis'");
    if (mysqli_num_rows($cek_nis) > 0) {
        $msg = "NIS sudah terdaftar!"; $icon = "error";
    } else {
        $query_reg = "INSERT INTO siswa (nis, nama, kelas, password) VALUES ('$nis', '$nama', '$kelas', '$password')";
        mysqli_query($conn, $query_reg);
        if (mysqli_affected_rows($conn) > 0) {
            $msg = "Registrasi Berhasil! Silakan Login."; $icon = "success";
        }
    }
}

// --- LOGIKA LOGIN ---
if (isset($_POST["login"])) {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = $_POST["password"];
    
    // Cek Admin
    $cek_admin = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username' AND password = '$password'");
    if (mysqli_num_rows($cek_admin) === 1) {
        $row = mysqli_fetch_assoc($cek_admin);
        $_SESSION["login"] = true; 
        $_SESSION["id"] = $row["id_admin"]; 
        $_SESSION["nama"] = $row["username"]; 
        $_SESSION["role"] = "admin";
        header("Location: admin.php"); 
        exit;
    } 
    
    // Cek Siswa
    $cek_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nis = '$username' AND password = '$password'");
    if (mysqli_num_rows($cek_siswa) === 1) {
        $row = mysqli_fetch_assoc($cek_siswa);
        $_SESSION["login"] = true; 
        $_SESSION["id"] = $row["nis"]; 
        $_SESSION["nama"] = $row["nama"]; 
        $_SESSION["role"] = "siswa";
        header("Location: siswa.php"); 
        exit;
    }
    $error_login = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Aspirasi | Login & Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { 
            --accent: #0ea5e9;
            --accent-hover: #0284c7;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --text-main: #f8fafc;
        }

        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 0; }
        body { 
            background: var(--bg-gradient); 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; padding: 20px; overflow: hidden; color: var(--text-main); 
        }

        .circle { position: absolute; border-radius: 50%; filter: blur(80px); z-index: -1; }
        .circle-1 { width: 300px; height: 300px; background: rgba(14, 165, 233, 0.2); top: -10%; left: -10%; }
        .circle-2 { width: 400px; height: 400px; background: rgba(16, 185, 129, 0.1); bottom: -15%; right: -5%; }

        .auth-card { 
            background: rgba(30, 41, 59, 0.7); width: 100%; max-width: 420px; border-radius: 28px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); padding: 40px; border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(12px); transition: all 0.5s ease;
        }

        .hidden { display: none; opacity: 0; transform: translateY(20px); }
        .visible { display: block; opacity: 1; transform: translateY(0); animation: slideUp 0.4s ease-out; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .header-box h2 { font-size: 26px; font-weight: 700; margin-bottom: 8px; }
        .header-box p { color: #94a3b8; font-size: 14px; margin-bottom: 30px; }

        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i.prefix { position: absolute; left: 15px; top: 16px; color: #64748b; font-size: 14px; }
        .input-group i.toggle-eye { position: absolute; right: 15px; top: 16px; color: #64748b; cursor: pointer; z-index: 10; }

        input { 
            width: 100%; padding: 14px 15px 14px 45px; border: 1.5px solid rgba(255,255,255,0.1); 
            border-radius: 14px; outline: none; background: rgba(15, 23, 42, 0.5);
            font-size: 14px; transition: all 0.3s ease; color: white;
        }
        input:focus { border-color: var(--accent); background: rgba(15, 23, 42, 0.8); box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15); }

        .btn { 
            width: 100%; padding: 16px; background: var(--accent); color: white; border: none; border-radius: 14px; 
            font-weight: 600; cursor: pointer; margin-top: 10px; font-size: 15px; transition: 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn:hover { background: var(--accent-hover); transform: translateY(-2px); }

        .footer-link { margin-top: 25px; text-align: center; font-size: 13px; color: #94a3b8; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .footer-link a { color: var(--accent); font-weight: 600; text-decoration: none; cursor: pointer; }
    </style>
</head>
<body>

    <?php if(isset($msg)) : ?>
        <script>
            Swal.fire({ icon: '<?= $icon ?>', title: '<?= $msg ?>', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
        </script>
    <?php endif; ?>

    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="auth-card">
        <div id="login-box" class="visible">
            <div class="header-box">
                <h2>Selamat datang 👋</h2>
                <p>Masuk ke akun aspirasi sekolah</p>
            </div>

            <?php if(isset($error_login)) : ?>
                <script>Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: 'NIS/Username atau Password salah!', background: '#1e293b', color: '#fff' });</script>
            <?php endif; ?>
            
            <form action="" method="post" onsubmit="return handleLoading(this)">
                <div class="input-group">
                    <i class="fa-solid fa-user prefix"></i>
                    <input type="text" name="username" placeholder="NIS atau Username" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-lock prefix"></i>
                    <input type="password" name="password" id="loginPass" placeholder="Password" required>
                    <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('loginPass', this)"></i>
                </div>
                <button type="submit" name="login" class="btn">
                    <span>Masuk Sekarang</span> <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>
            <div class="footer-link">Belum punya akun? <a onclick="switchBox('register')">Daftar Akun Siswa</a></div>
        </div>

        <div id="register-box" class="hidden">
            <div class="header-box">
                <h2>Daftar Baru 📝</h2>
                <p>Lengkapi data diri siswa</p>
            </div>

            <form action="" method="post" onsubmit="return handleLoading(this)">
                <div class="input-group">
                    <i class="fa-solid fa-id-card prefix"></i>
                    <input type="text" name="nis" placeholder="NIS (Angka)" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-signature prefix"></i>
                    <input type="text" name="nama" placeholder="Nama Lengkap" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-graduation-cap prefix"></i>
                    <input type="text" name="kelas" placeholder="Kelas (Misal: XII RPL 1)" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-key prefix"></i>
                    <input type="password" name="password" id="regPass" placeholder="Password" minlength="4" required>
                    <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('regPass', this)"></i>
                </div>
                <button type="submit" name="register" class="btn">
                    <span>Daftar Sekarang</span> <i class="fa-solid fa-user-plus"></i>
                </button>
            </form>
            <div class="footer-link">Sudah punya akun? <a onclick="switchBox('login')">Kembali ke Login</a></div>
        </div>
    </div>

    <script>
        function togglePassword(id, el) {
            const input = document.getElementById(id);
            input.type = (input.type === "password") ? "text" : "password";
            el.classList.toggle('fa-eye');
            el.classList.toggle('fa-eye-slash');
        }

        function switchBox(target) {
            const lb = document.getElementById('login-box');
            const rb = document.getElementById('register-box');
            if (target === 'register') {
                lb.classList.replace('visible', 'hidden');
                setTimeout(() => { lb.style.display = 'none'; rb.style.display = 'block'; setTimeout(() => rb.classList.replace('hidden', 'visible'), 50); }, 400);
            } else {
                rb.classList.replace('visible', 'hidden');
                setTimeout(() => { rb.style.display = 'none'; lb.style.display = 'block'; setTimeout(() => lb.classList.replace('hidden', 'visible'), 50); }, 400);
            }
        }

        function handleLoading(form) {
            const btn = form.querySelector('.btn');
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menghubungkan...';
            return true;
        }
    </script>
</body>
</html>