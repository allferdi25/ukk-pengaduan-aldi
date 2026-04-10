<?php
$conn = mysqli_connect("localhost", "root", "", "db_pengaduan_sekolah");

function registrasi($data) {
    global $conn;

    $nis = htmlspecialchars($data["nis"]);
    $nama = htmlspecialchars($data["nama"]);
    $kelas = htmlspecialchars($data["kelas"]); // Mengambil data kelas
    $password = mysqli_real_escape_string($conn, $data["password"]);

    // Cek apakah NIS sudah ada
    $result = mysqli_query($conn, "SELECT nis FROM siswa WHERE nis = '$nis'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('NIS sudah terdaftar!');</script>";
        return false;
    }

    // Enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Query INSERT harus menyertakan kolom kelas
    $query = "INSERT INTO siswa (nis, nama, kelas, password) 
              VALUES ('$nis', '$nama', '$kelas', '$password')";
    
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// Fungsi cek_login untuk index.php
function cek_login($username, $password) {
    global $conn;
    $q_admin = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username'");
    if (mysqli_num_rows($q_admin) > 0) {
        $data = mysqli_fetch_assoc($q_admin);
        if ($password == $data['password']) {
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'admin';
            $_SESSION['nama'] = $data['nama_petugas'];
            $_SESSION['id'] = $data['id_admin'];
            return "admin";
        }
    }

    $q_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nis = '$username'");
    if (mysqli_num_rows($q_siswa) > 0) {
        $data = mysqli_fetch_assoc($q_siswa);
        if (password_verify($password, $data['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'siswa';
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['id'] = $data['nis'];
            return "siswa";
        }
    }
    return false;
}

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while( $row = mysqli_fetch_assoc($result) ) { 
        $rows[] = $row;
    }
    return $rows;
}
?>