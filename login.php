<?php
// Import class PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load file PHPMailer (Mencari di DALAM folder proyek)
require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

// ========================================================
// LOGIKA PHP UNTUK FITUR LUPA KATA SANDI DENGAN PHPMAILER
// ========================================================
if (isset($_POST['kirim_reset'])) {
    $email_tujuan = $_POST['email_reset'];

    // Membuat Token Unik Acak untuk link reset
    $token = bin2hex(random_bytes(50));
    $link_reset = "http://localhost/driver-drowsiness-system/reset_password.php?token=" . $token . "&email=" . $email_tujuan;

    $mail = new PHPMailer(true);

    try {
        // Konfigurasi Server SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        // MASUKKAN EMAIL GMAIL & APP PASSWORD ANDA DI SINI
        $mail->Username = 'GANTI_DENGAN_EMAIL_ANDA@gmail.com';
        $mail->Password = 'GANTI_DENGAN_16_DIGIT_APP_PASSWORD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Pengirim & Penerima
        $mail->setFrom('GANTI_DENGAN_EMAIL_ANDA@gmail.com', 'Admin AWAS 2.0');
        $mail->addAddress($email_tujuan);

        // Konten Email
        $mail->isHTML(true);
        $mail->Subject = 'Reset Kata Sandi - AWAS 2.0';
        $mail->Body = "Halo,<br><br>Kami menerima permintaan untuk mereset kata sandi Anda. Klik tautan di bawah ini untuk membuat kata sandi baru:<br><br><a href='$link_reset'>$link_reset</a><br><br>Jika Anda tidak meminta ini, abaikan pesan ini.";

        // Kirim Email
        $mail->send();

        // Trigger Notifikasi Sukses Animasi JS
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    if(typeof showSuccessModal === 'function') {
                        showSuccessModal('$email_tujuan');
                    }
                });
              </script>";
    } catch (Exception $e) {
        // Jika gagal, munculkan alert error bawaan
        echo "<script>alert('Pesan gagal terkirim. Pastikan koneksi internet & App Password benar. Error: {$mail->ErrorInfo}');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWAS 2.0 - Login & Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================================
           BAGIAN 1: CSS ROOT & STYLING DASAR
           ======================================================== */
        :root {
            /* Tema Biru agak Ungu (Indigo) menyesuaikan Landing Page */
            --primary: #4F46E5;
            --primary-dark: #3730A3;
            --bg-color: #eef2f9;
            --text-color: #333;
            --light-text: #777;
            --white: #ffffff;
            --input-bg: #f0f4fc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* --- FITUR KEMBALI KE BERANDA (DI POJOK KOTAK FORM) --- */
        .back-home-btn {
            position: absolute;
            top: 25px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--light-text);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .back-home-btn:hover {
            color: var(--primary);
        }

        /* --- ANIMASI BACKGROUND AESTHETIC --- */
        .bg-shape {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            animation: float 10s ease-in-out infinite;
        }

        .shape1 {
            width: 400px;
            height: 400px;
            background: rgba(79, 70, 229, 0.3);
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }

        .shape2 {
            width: 300px;
            height: 300px;
            background: rgba(59, 130, 246, 0.2);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
            animation-delay: -5s;
        }

        @keyframes float {
            0% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(30px) scale(1.1);
            }

            100% {
                transform: translateY(0) scale(1);
            }
        }

        /* --- CONTAINER UTAMA (GLASSMORPHISM) --- */
        .container {
            background-color: var(--white);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            min-height: 550px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        @keyframes show {

            0%,
            49.99% {
                opacity: 0;
                z-index: 1;
            }

            50%,
            100% {
                opacity: 1;
                z-index: 5;
            }
        }

        /* --- FORM STYLING --- */
        form {
            background-color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
        }

        h1 {
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-color);
        }

        p.subtitle {
            font-size: 13px;
            color: var(--light-text);
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin-bottom: 15px;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0a5b1;
            transition: 0.3s;
        }

        .input-group input {
            background-color: var(--input-bg);
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 12px 15px 12px 45px;
            width: 100%;
            font-size: 14px;
            transition: all 0.3s ease;
            color: var(--text-color);
        }

        .input-group input:focus {
            border-color: var(--primary);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .input-group input:focus+i {
            color: var(--primary);
        }

        /* PERBAIKAN ICON MATA */
        .eye-icon {
            position: absolute;
            right: 15px;
            left: auto !important;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0a5b1;
        }

        .grid-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
        }

        a.forgot-pass {
            color: var(--light-text);
            font-size: 12px;
            text-decoration: none;
            margin-top: -5px;
            margin-bottom: 20px;
            align-self: flex-end;
            transition: 0.3s;
        }

        a.forgot-pass:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        button {
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 600;
            padding: 14px 45px;
            letter-spacing: 0.5px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.6);
        }

        button:active {
            transform: translateY(1px);
        }

        button.ghost {
            background: transparent;
            border: 2px solid #FFFFFF;
            box-shadow: none;
        }

        button.ghost:hover {
            background: #FFFFFF;
            color: var(--primary);
        }
    </style>
</head>

<body>

    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>

    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <!-- TOMBOL KEMBALI KE BERANDA -->
            <a href="index.php" class="back-home-btn">
                <i class="fas fa-home"></i> Beranda
            </a>
            <form action="#">
                <h1>Buat Akun Baru</h1>
                <p class="subtitle">Lengkapi data identitas & kendaraan Anda di bawah ini.</p>

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Nama Lengkap" />
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="text" placeholder="Email / Username" />
                </div>

                <div class="grid-inputs">
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="text" placeholder="No. HP (Darurat)" />
                    </div>
                    <div class="input-group">
                        <i class="fas fa-car"></i>
                        <input type="text" placeholder="Plat Kendaraan" />
                    </div>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="••••••••" />
                    <i class="fas fa-eye eye-icon"></i>
                </div>

                <button type="submit">Daftar Akun <i class="fas fa-user-plus"></i></button>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <!-- TOMBOL KEMBALI KE BERANDA -->
            <a href="index.php" class="back-home-btn">
                <i class="fas fa-home"></i> Beranda
            </a>
            <form action="#">
                <h1>Selamat Datang!</h1>
                <p class="subtitle">Silakan masukkan kredensial Anda untuk melanjutkan.</p>

                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="text" placeholder="adminupt" />
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="••••••••" />
                    <i class="fas fa-eye eye-icon"></i>
                </div>

                <a href="#" class="forgot-pass" id="forgotPassTrigger">Lupa Kata Sandi?</a>

                <button type="submit">Masuk Sekarang <i class="fas fa-arrow-right"></i></button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <div class="logo-area">
                        <i class="fas fa-shield-alt logo-icon"></i>
                        <h2>AWAS 2.0</h2>
                    </div>
                    <h3>Sistem Keselamatan<br>Pengemudi Cerdas</h3>
                    <p>Masuk ke dashboard Anda untuk memantau perjalanan, mendeteksi tingkat kewaspadaan, dan menjaga
                        keselamatan berkendara.</p>
                    <button class="ghost" id="signIn">Pindah ke Login</button>
                    <i class="fas fa-car-side floating-illustration"></i>
                </div>

                <div class="overlay-panel overlay-right">
                    <div class="logo-area">
                        <i class="fas fa-shield-alt logo-icon"></i>
                        <h2>AWAS 2.0</h2>
                    </div>
                    <h3>Pengguna Baru?</h3>
                    <p>Daftarkan diri Anda beserta kendaraan untuk mengaktifkan pemantauan Computer Vision dan Live
                        Tracking secara real-time.</p>
                    <button class="ghost" id="signUp">Daftar Sekarang</button>
                    <i class="fas fa-video floating-illustration"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- STRUKTUR HTML MODAL LUPA KATA SANDI (DIJADIKAN FORM UNTUK PHP POST) -->
    <div class="modal-overlay" id="forgotPassModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Reset Kata Sandi</h2>
                <button type="button" class="close-modal-btn" id="closeModalBtn"><i class="fas fa-times"></i></button>
            </div>
            <!-- Menggunakan inline CSS pada form agar tidak bentrok dengan CSS form utama -->
            <form method="POST" action="" style="padding: 0; height: auto; display: block; background: transparent;">
                <div class="modal-body">
                    <p>Masukkan alamat email Anda yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata
                        sandi.</p>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <!-- name="email_reset" wajib ada agar ditangkap PHP -->
                        <input type="email" name="email_reset" placeholder="Alamat Email Anda" required />
                    </div>
                    <!-- type="submit" dan name="kirim_reset" wajib ada sebagai pemicu (trigger) PHP -->
                    <button type="submit" name="kirim_reset" style="margin-top: 10px;">Kirim Tautan Reset <i
                            class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- STRUKTUR HTML MODAL NOTIFIKASI SUKSES -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-box success-box">
            <div class="animated-icon-wrapper">
                <i class="fas fa-paper-plane plane-icon"></i>
                <i class="fas fa-check-circle check-icon"></i>
            </div>
            <h2>Tautan Terkirim!</h2>
            <p>Silakan periksa kotak masuk email <b id="userEmailSpan" style="color: var(--primary);"></b> untuk
                mengatur ulang kata sandi Anda.</p>
            <button type="button" id="closeSuccessBtn" style="margin-top: 20px;">Tutup & Kembali</button>
        </div>
    </div>

    <!-- ========================================================
         BAGIAN 2: SISA CSS (OVERLAY & MODAL) & JAVASCRIPT
         ======================================================== -->
    <style>
        /* --- OVERLAY CONTAINER (Panel Biru Ungu Gradasi) --- */
        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 100;
        }

        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: #FFFFFF;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* --- TAMBAHAN DEKORASI OVERLAY (GLOWING ORBS DALAM PANEL) --- */
        .overlay::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            z-index: 0;
        }

        .overlay::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            z-index: 0;
        }

        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            text-align: left;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 1;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        /* --- TYPOGRAPHY & ICON DI OVERLAY --- */
        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .logo-area h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 1px;
        }

        .overlay-panel h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.3;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .overlay-panel p {
            font-size: 14px;
            font-weight: 300;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        /* Ilustrasi transparan raksasa di pojok */
        .floating-illustration {
            position: absolute;
            bottom: -30px;
            right: -20px;
            font-size: 250px;
            opacity: 0.12;
            transform: rotate(-15deg);
            pointer-events: none;
            z-index: -1;
        }

        .overlay-left .floating-illustration {
            right: auto;
            left: -20px;
            transform: rotate(15deg);
        }

        /* Responsive Mobile */
        @media (max-width: 768px) {
            .container {
                min-height: 600px;
            }

            .grid-inputs {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        /* --- CSS MODAL LUPA KATA SANDI --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.show-modal {
            opacity: 1;
            visibility: visible;
        }

        .modal-box {
            background: var(--white);
            width: 400px;
            max-width: 90%;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(-30px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            text-align: center;
        }

        .modal-overlay.show-modal .modal-box {
            transform: translateY(0) scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .modal-header h2 {
            font-size: 20px;
            color: var(--text-color);
            margin: 0;
        }

        .close-modal-btn {
            background: transparent !important;
            border: none !important;
            color: #a0a5b1 !important;
            font-size: 20px !important;
            cursor: pointer;
            padding: 0 !important;
            width: auto !important;
            box-shadow: none !important;
        }

        .close-modal-btn:hover {
            color: var(--primary) !important;
            transform: none !important;
        }

        .modal-body p {
            font-size: 13px;
            color: var(--light-text);
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* --- ANIMASI IKON SUKSES --- */
        .animated-icon-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .plane-icon {
            font-size: 40px;
            color: var(--primary);
            position: absolute;
            opacity: 1;
        }

        .check-icon {
            font-size: 50px;
            color: #10B981;
            /* Warna hijau sukses */
            position: absolute;
            opacity: 0;
            transform: scale(0);
        }

        /* Keyframes untuk Pesawat Terbang */
        @keyframes flyAway {
            0% {
                transform: translate(0, 0) scale(1);
                opacity: 1;
            }

            40% {
                transform: translate(15px, -15px) scale(1.1);
                opacity: 1;
            }

            100% {
                transform: translate(100px, -100px) scale(0);
                opacity: 0;
            }
        }

        /* Keyframes untuk Centang Muncul */
        @keyframes popIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            80% {
                transform: scale(1.2);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Kelas pemicu animasi (ditambahkan JS) */
        .show-modal .plane-icon {
            animation: flyAway 1s forwards ease-in-out;
        }

        .show-modal .check-icon {
            /* Muncul setelah pesawat terbang (delay 0.8s) */
            animation: popIn 0.5s 0.8s forwards cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
    </style>

    <!-- Script Javascript untuk Animasi Form, Toggle Password & Logika Modal -->
    <script>
        // 1. Animasi Slider Form
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        // 2. Fitur klik Icon Mata untuk melihat password
        const eyeIcons = document.querySelectorAll('.eye-icon');
        eyeIcons.forEach(icon => {
            icon.addEventListener('click', function () {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                    this.style.color = '#4F46E5';
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                    this.style.color = '#a0a5b1';
                }
            });
        });

        // 3. Logika Modal Popup Lupa Kata Sandi (Manual Klik)
        const forgotPassTrigger = document.getElementById('forgotPassTrigger');
        const forgotPassModal = document.getElementById('forgotPassModal');
        const closeModalBtn = document.getElementById('closeModalBtn');

        // Buka Modal Form Lupa Password
        forgotPassTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            forgotPassModal.classList.add('show-modal');
        });

        // Tutup Modal Form Lupa Password
        closeModalBtn.addEventListener('click', () => {
            forgotPassModal.classList.remove('show-modal');
        });

        // 4. Logika Modal Sukses (Dipanggil oleh PHP)
        const successModal = document.getElementById('successModal');
        const closeSuccessBtn = document.getElementById('closeSuccessBtn');

        // Fungsi ini akan dipanggil otomatis oleh blok PHP di baris teratas jika tombol 'Kirim' ditekan
        function showSuccessModal(email) {
            // Sembunyikan modal form jika masih terbuka
            if (forgotPassModal) forgotPassModal.classList.remove('show-modal');

            // Masukkan email ke dalam text span
            document.getElementById('userEmailSpan').textContent = email;

            // Tampilkan modal sukses untuk trigger animasi
            successModal.classList.add('show-modal');
        }

        // Tutup Modal Sukses
        closeSuccessBtn.addEventListener('click', () => {
            successModal.classList.remove('show-modal');
        });

        // Tutup Modal jika area blur di luar box diklik (berlaku untuk kedua modal)
        window.addEventListener('click', (e) => {
            if (e.target === forgotPassModal) {
                forgotPassModal.classList.remove('show-modal');
            }
            if (e.target === successModal) {
                successModal.classList.remove('show-modal');
            }
        });
    </script>
</body>

</html>