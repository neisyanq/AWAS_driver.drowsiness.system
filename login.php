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
            --primary: #4e44e8;
            --primary-dark: #372fba;
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
            background: rgba(78, 68, 232, 0.3);
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }

        .shape2 {
            width: 300px;
            height: 300px;
            background: rgba(100, 200, 255, 0.3);
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
            box-shadow: 0 0 0 4px rgba(78, 68, 232, 0.1);
            outline: none;
        }

        .input-group input:focus+i {
            color: var(--primary);
        }

        .eye-icon {
            position: absolute;
            right: 15px;
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
            box-shadow: 0 5px 15px rgba(78, 68, 232, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(78, 68, 232, 0.6);
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
            <form action="#">
                <h1>Buat Akun Baru</h1>
                <p class="subtitle">Lengkapi data identitas & kendaraan Anda di bawah ini.</p>

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Nama Lengkap" />
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="text" placeholder="adminupt" />
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

                <a href="#" class="forgot-pass">Lupa Kata Sandi?</a>

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
    <!-- ========================================================
         BAGIAN 2: SISA CSS (OVERLAY) & JAVASCRIPT
         ======================================================== -->
    <style>
        /* --- OVERLAY CONTAINER (Panel Biru/Ungu) --- */
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
            opacity: 0.06;
            transform: rotate(-15deg);
            pointer-events: none;
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
    </style>

    <!-- Script Javascript untuk Animasi Form & Toggle Password -->
    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        // Trigger animasi slider
        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        // Fitur klik Icon Mata untuk melihat password
        const eyeIcons = document.querySelectorAll('.eye-icon');
        eyeIcons.forEach(icon => {
            icon.addEventListener('click', function () {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                    this.style.color = '#4e44e8'; // Menyala saat password terlihat
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                    this.style.color = '#a0a5b1';
                }
            });
        });
    </script>
</body>

</html>