<?php
session_start();

// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_drowsiness';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ==========================================
    // PROSES LOGIN
    // ==========================================
    if ($action == 'login') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Cari user berdasarkan username atau email
        $stmt = $conn->prepare("SELECT id, nama_lengkap, password, role, foto_profil FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Verifikasi Password Hashing
            if (password_verify($password, $row['password'])) {
                // Set Session
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['foto_profil'] = $row['foto_profil'];

                header("Location: dashboard.php");
                exit;
            } else {
                echo "<script>alert('Password yang Anda masukkan salah!'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('Username atau Email tidak ditemukan di sistem.'); window.location.href='index.php';</script>";
        }
        $stmt->close();
    }

    // ==========================================
    // PROSES REGISTER
    // ==========================================
    elseif ($action == 'register') {
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $no_hp = trim($_POST['no_hp']);

        // Hash password demi keamanan privasi pengemudi
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'driver';

        // Cek apakah username/email sudah dipakai
        $cek_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $cek_stmt->bind_param("ss", $username, $email);
        $cek_stmt->execute();
        if ($cek_stmt->get_result()->num_rows > 0) {
            echo "<script>alert('Gagal! Username atau Email sudah terdaftar.'); window.location.href='index.php';</script>";
            $cek_stmt->close();
            exit;
        }
        $cek_stmt->close();

        // Insert Data User (Driver)
        $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, username, email, no_hp, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nama_lengkap, $username, $email, $no_hp, $password, $role);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Insert Data Kontak Darurat (Emergency Contact)
            $em_name = trim($_POST['em_name']);
            $em_phone = trim($_POST['em_phone']);
            $em_relation = trim($_POST['em_relation']);

            $stmt_em = $conn->prepare("INSERT INTO emergency_contacts (user_id, nama_kontak, no_hp, hubungan, is_primary) VALUES (?, ?, ?, ?, 1)");
            $stmt_em->bind_param("isss", $user_id, $em_name, $em_phone, $em_relation);
            $stmt_em->execute();
            $stmt_em->close();

            echo "<script>alert('Registrasi Berhasil! Silakan Login menggunakan kredensial Anda.'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan pada server. Coba lagi.'); window.location.href='index.php';</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>