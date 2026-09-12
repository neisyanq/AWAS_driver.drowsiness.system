<?php
session_start();

// Simulasi cek login
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit;
// }

// Simulasi data user yang login
$nama_user = "Neisya Nur Qoyimah";
$username = "neisyanq";
$plat_nomor = "L 1234 NQ";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengemudi - DrowsyDrive</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --primary-light: #eff6ff;
            --sidebar-active: #2563eb;
            --bg-body: #f4f7fa;
            --bg-white: #ffffff;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --text-light: #94a3b8;
            --danger-color: #ef4444;
            --danger-light: #fee2e2;
            --success-color: #10b981;
            --success-light: #dcfce7;
            --warning-color: #f59e0b;
            --warning-light: #fef3c7;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.02), 0 4px 12px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 10px 20px rgba(0, 0, 0, 0.05);
            --sidebar-width: 270px;
            --border-radius-lg: 16px;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.02em;
        }

        /* SCROLLBAR CUSTOM */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* SIDEBAR */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        #sidebar-wrapper {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--bg-white);
            border-right: 1px solid #e2e8f0;
            transition: margin 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg"><path fill="%23eff6ff" fill-opacity="0.5" d="M0,256L48,229.3C96,203,192,149,288,154.7C384,160,480,224,576,218.7C672,213,768,139,864,128C960,117,1056,171,1152,197.3C1248,224,1344,224,1392,224L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-position: bottom;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .sidebar-heading {
            padding: 1.8rem 1.5rem 0.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-icon-logo {
            width: 42px;
            height: 42px;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.4rem;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .list-group-flush {
            padding: 10px 15px;
            flex-grow: 1;
            overflow-y: auto;
            z-index: 2;
        }

        .list-group-item {
            border: none;
            padding: 12px 18px;
            margin-bottom: 6px;
            border-radius: 12px;
            color: var(--text-gray);
            font-weight: 500;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.3s ease;
            background: transparent;
            cursor: pointer;
        }

        .list-group-item i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            color: #94a3b8;
            transition: 0.3s;
        }

        .list-group-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .list-group-item:hover i {
            color: var(--primary-color);
        }

        .list-group-item.active {
            background: var(--sidebar-active);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            font-weight: 600;
        }

        .list-group-item.active i {
            color: white;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #cbd5e1;
            text-transform: uppercase;
            margin: 20px 0 8px 18px;
        }

        .sidebar-footer {
            padding: 20px 15px;
            z-index: 2;
        }

        .btn-logout {
            color: var(--danger-color);
            background: var(--bg-white);
            border: 1px solid var(--danger-light);
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        }

        .btn-logout:hover {
            background: var(--danger-light);
            border-color: var(--danger-light);
            transform: translateY(-2px);
        }

        /* MAIN CONTENT & NAVBAR */
        #page-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            background-color: transparent;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background: var(--bg-body);
            padding: 24px 32px 10px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .greeting-title {
            font-size: 1.5rem;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .date-widget {
            background: var(--bg-white);
            border: 1px solid #e2e8f0;
            padding: 10px 18px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-btn {
            position: relative;
            background: var(--bg-white);
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-gray);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            background: var(--primary-light);
            color: var(--primary-color);
            transform: scale(1.05);
        }

        .notif-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--danger-color);
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .profile-circle {
            width: 46px;
            height: 46px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            border: 2px solid white;
        }

        /* MAIN CONTAINER & CARDS */
        .main-container {
            padding: 20px 32px 32px;
            flex-grow: 1;
        }

        .dash-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-lg);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: var(--shadow-sm);
            padding: 22px;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dash-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .card-blue-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
        }

        .card-blue-gradient::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 400 150" xmlns="http://www.w3.org/2000/svg"><path d="M0,100 C150,150 250,50 400,100 L400,150 L0,150 Z" fill="rgba(255,255,255,0.15)"/><path d="M0,120 C150,170 250,70 400,120 L400,150 L0,150 Z" fill="rgba(255,255,255,0.1)"/><path stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-dasharray="10,10" d="M0,110 C150,160 250,60 400,110" fill="none"/></svg>');
            background-size: cover;
            background-position: bottom;
            background-repeat: no-repeat;
            opacity: 0.9;
            z-index: 1;
            pointer-events: none;
        }

        .card-blue-content {
            position: relative;
            z-index: 2;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            transition: transform 0.3s;
        }

        .dash-card:hover .stat-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        .cv-camera-feed {
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            background: #000;
            height: 100%;
            min-height: 280px;
        }

        .cv-camera-feed img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-badge-custom {
            font-size: 0.8rem;
            padding: 6px 14px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* ================= TAMBAHAN CSS UNTUK FITUR LIVE MONITOR (BARU) ================= */
        .live-monitor-cam {
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            background: #000;
            height: 350px;
            width: 100%;
        }

        .live-monitor-cam img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Toggle Switch CSS */
        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
            margin-top: 0;
            background-color: #cbd5e1;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary-color);
        }

        /* Range Slider CSS */
        .custom-range {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 5px;
            background: #e2e8f0;
            outline: none;
            opacity: 0.9;
            transition: opacity .2s;
        }

        .custom-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.4);
        }

        .metric-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            background: #f8fafc;
        }

        .attention-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 6px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            border-top-color: var(--success-color);
            border-right-color: var(--success-color);
            transform: rotate(-45deg);
        }

        .attention-circle span {
            transform: rotate(45deg);
        }

        /* RESPONSIVE */
        @media (max-width: 1199px) {
            .dash-card {
                padding: 16px;
            }
        }

        @media (max-width: 992px) {
            #sidebar-wrapper {
                margin-left: calc(var(--sidebar-width) * -1);
            }

            #page-content-wrapper {
                margin-left: 0;
                width: 100%;
            }

            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
                box-shadow: 5px 0 25px rgba(0, 0, 0, 0.1);
            }

            .navbar-custom,
            .main-container {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <!-- ================= SIDEBAR ================= -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading pb-1">
                <div class="sidebar-icon-logo"><i class="fa-solid fa-compact-disc"></i></div>
                <div class="ms-3">
                    <h5 class="m-0 font-heading fw-bold" style="color: #1e3a8a;">DrowsyDrive</h5>
                </div>
            </div>
            <div class="px-4 mb-3">
                <small class="text-gray fw-medium" style="font-size: 0.7rem; line-height: 1.3; display: block;">Sistem
                    Deteksi Kantuk Saat<br>Berkendara</small>
            </div>

            <div class="list-group list-group-flush mt-2">
                <!-- ID ditambahkan di sini untuk JS toggle -->
                <a id="nav-dashboard-btn" class="list-group-item active"><i class="fa-solid fa-house"></i>
                    <span>Dashboard</span></a>
                <a id="nav-live-btn" class="list-group-item"><i class="fa-solid fa-camera"></i> <span>Live
                        Monitor</span></a>
                <a href="#" class="list-group-item"><i class="fa-solid fa-file-shield"></i> <span>Log
                        Pelanggaran</span></a>

                <div class="nav-section-title">DATA & LOG</div>
                <a href="#" class="list-group-item"><i class="fa-solid fa-clock-rotate-left"></i> <span>Riwayat
                        Perjalanan</span></a>
                <a href="#" class="list-group-item"><i class="fa-solid fa-triangle-exclamation"></i> <span>Riwayat
                        Deteksi</span></a>

                <div class="nav-section-title">PENGATURAN</div>
                <a href="#" class="list-group-item"><i class="fa-solid fa-user-gear"></i> <span>Profil Saya</span></a>
                <a href="#" class="list-group-item"><i class="fa-solid fa-circle-question"></i> <span>Bantuan</span></a>
            </div>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center mb-4 px-2 opacity-75">
                    <i class="fa-solid fa-car-side fs-4 text-primary"></i>
                    <div class="ms-3">
                        <small class="d-block fw-bold text-primary font-heading"
                            style="font-size:0.8rem;">DrowsyDrive</small>
                        <small class="text-primary" style="font-size: 0.65rem;">Tetap Fokus, Sampai Tujuan</small>
                    </div>
                </div>
                <a href="login.php" class="btn-logout text-decoration-none shadow-sm">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar Sistem
                </a>
            </div>
        </div>

        <!-- ================= MAIN CONTENT ================= -->
        <div id="page-content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-custom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none shadow-sm" id="menu-toggle"
                        style="border-radius: 10px; border: 1px solid #e2e8f0; width: 44px; height: 44px;">
                        <i class="fa-solid fa-bars text-primary"></i>
                    </button>
                    <div>
                        <h4 class="m-0 font-heading fw-bold greeting-title" id="top-title">Selamat Datang,
                            <?php echo explode(' ', trim($nama_user))[0]; ?>! 👋
                        </h4>
                        <small class="text-gray" id="top-subtitle" style="font-size: 0.85rem;">Jaga fokus, jaga
                            keselamatan. Sistem akan memantau kondisi kantuk Anda saat berkendara.</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 d-none d-md-flex">
                    <div class="date-widget shadow-sm">
                        <i class="fa-regular fa-calendar text-primary fs-5 bg-primary bg-opacity-10 p-2 rounded-2"></i>
                        <div style="line-height: 1.2;">
                            <div id="realtime-clock" class="fw-bold text-dark font-heading" style="font-size: 0.85rem;">
                                --:--:-- WIB</div>
                            <div id="realtime-date" class="text-muted" style="font-size: 0.7rem;">Memuat tanggal...
                            </div>
                        </div>
                    </div>

                    <button class="notification-btn shadow-sm">
                        <i class="fa-solid fa-bell fs-5"></i>
                        <span class="notif-badge">2</span>
                    </button>

                    <div class="d-flex align-items-center gap-2 cursor-pointer ms-2 bg-white px-2 py-1 rounded-pill border border-secondary border-opacity-10 shadow-sm"
                        style="transition: 0.3s;" onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background='#fff'">
                        <div class="profile-circle shadow-sm">
                            <?php
                            $initials = '';
                            $name_parts = explode(' ', trim($nama_user));
                            if (isset($name_parts[0]))
                                $initials .= substr($name_parts[0], 0, 1);
                            if (isset($name_parts[1]))
                                $initials .= substr($name_parts[1], 0, 1);
                            echo strtoupper($initials);
                            ?>
                        </div>
                        <div class="text-start d-none d-xl-block lh-sm pe-2 ps-1">
                            <div class="fw-bold text-dark font-heading" style="font-size: 0.85rem;">
                                <?php echo $nama_user; ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">Pengguna</div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-muted pe-2 d-none d-xl-block"
                            style="font-size: 0.8rem;"></i>
                    </div>
                </div>
            </nav>

            <!-- Container Fluid -->
            <div class="main-container">

                <!-- ================= START VIEW: DASHBOARD (ORIGINAL) ================= -->
                <div id="view-dashboard">
                    <!-- ROW 1: Statistik Cepat -->
                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-lg-4">
                            <div
                                class="dash-card card-blue-gradient d-flex flex-column justify-content-center h-100 shadow-sm">
                                <div class="card-blue-content d-flex align-items-center justify-content-between mb-4">
                                    <div class="stat-icon"
                                        style="background: rgba(255,255,255,0.2); backdrop-filter: blur(5px);"><i
                                            class="fa-solid fa-car text-white"></i></div>
                                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold"
                                        style="font-size: 0.75rem; box-shadow: var(--shadow-sm);">GPS Aktif</span>
                                </div>
                                <div class="card-blue-content">
                                    <small class="text-white text-opacity-75 fw-medium d-block mb-1"
                                        style="font-size: 0.85rem;">Status Perjalanan</small>
                                    <h4 class="m-0 font-heading fw-bold text-white"><span
                                            style="color: #4ade80; text-shadow: 0 0 10px #4ade80;">●</span> Sedang
                                        Berkendara</h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-9 col-lg-8">
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 h-100">
                                <div class="col">
                                    <div
                                        class="dash-card d-flex flex-column justify-content-center align-items-start p-3 h-100">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i
                                                    class="fa-solid fa-gauge-high"></i></div>
                                            <div>
                                                <small class="text-gray fw-medium" style="font-size: 0.75rem;">Kecepatan
                                                    Saat Ini</small>
                                                <h5 class="m-0 font-heading fw-bold text-dark">62 <span
                                                        class="fw-normal text-muted"
                                                        style="font-size: 0.85rem;">km/jam</span></h5>
                                            </div>
                                        </div>
                                        <span class="badge text-success mt-1 rounded-pill px-2 py-1"
                                            style="background: var(--success-light); font-size: 0.7rem; border: 1px solid #bbf7d0;"><i
                                                class="fa-solid fa-check-circle me-1"></i> Dalam batas normal</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <div
                                        class="dash-card d-flex flex-column justify-content-center align-items-start p-3 h-100">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <div class="stat-icon bg-info bg-opacity-10 text-info"><i
                                                    class="fa-solid fa-route"></i></div>
                                            <div>
                                                <small class="text-gray fw-medium" style="font-size: 0.75rem;">Jarak
                                                    Tempuh</small>
                                                <h5 class="m-0 font-heading fw-bold text-dark">48.7 <span
                                                        class="fw-normal text-muted"
                                                        style="font-size: 0.85rem;">km</span></h5>
                                            </div>
                                        </div>
                                        <small class="text-gray mt-1 fw-medium"
                                            style="font-size: 0.75rem; padding-left: 62px;">Perjalanan
                                            berlangsung</small>
                                    </div>
                                </div>
                                <div class="col">
                                    <div
                                        class="dash-card d-flex flex-column justify-content-center align-items-start p-3 h-100">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <div class="stat-icon text-warning"
                                                style="background: var(--warning-light);"><i
                                                    class="fa-regular fa-clock"></i></div>
                                            <div>
                                                <small class="text-gray fw-medium" style="font-size: 0.75rem;">Durasi
                                                    Berkendara</small>
                                                <h5 class="m-0 font-heading fw-bold text-dark">1j 23m</h5>
                                            </div>
                                        </div>
                                        <small class="text-gray mt-1 fw-medium"
                                            style="font-size: 0.75rem; padding-left: 62px;">Sejak 08:42</small>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="dash-card d-flex flex-column justify-content-center align-items-start p-3 h-100 border-danger border-opacity-25"
                                        style="background: #fffcfc;">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <div class="stat-icon text-danger" style="background: var(--danger-light);">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            </div>
                                            <div>
                                                <small class="text-gray fw-medium" style="font-size: 0.75rem;">Deteksi
                                                    Kantuk</small>
                                                <h5 class="m-0 font-heading fw-bold text-dark">0 <span
                                                        class="fw-normal text-muted"
                                                        style="font-size: 0.85rem;">kali</span></h5>
                                            </div>
                                        </div>
                                        <small class="text-gray mt-1 fw-medium"
                                            style="font-size: 0.75rem; padding-left: 62px;">Tidak ada
                                            pelanggaran</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 2: Live Camera & Map -->
                    <div class="row g-4 mb-4">
                        <div class="col-xl-8 col-lg-7">
                            <div class="dash-card h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;"><i
                                                class="fa-solid fa-video"></i></div>
                                        <div>
                                            <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.1rem;">
                                                Live Monitoring Pengemudi</h5>
                                            <small class="text-gray">Pantau kondisi wajah dan mata secara real-time saat
                                                berkendara.</small>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill px-3 py-2 d-flex align-items-center gap-2"
                                        style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; box-shadow: 0 2px 5px rgba(16,185,129,0.1);">
                                        <div
                                            style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: blinkPulse 1.5s infinite;">
                                        </div> Kamera Aktif
                                    </span>
                                </div>
                                <div class="row g-4 flex-grow-1">
                                    <div class="col-md-7 d-flex flex-column">
                                        <div
                                            class="cv-camera-feed w-100 shadow-sm border border-secondary border-opacity-25">
                                            <img src="https://images.unsplash.com/photo-1542282088-72c9c27ed0cd?auto=format&fit=crop&q=80&w=800"
                                                alt="Kamera Simulasi">
                                            <div
                                                style="position: absolute; top: 15%; left: 30%; width: 40%; height: 60%; border: 2px solid #3b82f6; border-radius: 12px; box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);">
                                                <div
                                                    style="position: absolute; top: 40%; left: 25%; width: 5px; height: 5px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981;">
                                                </div>
                                                <div
                                                    style="position: absolute; top: 40%; right: 25%; width: 5px; height: 5px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981;">
                                                </div>
                                                <div
                                                    style="position: absolute; bottom: 25%; left: 45%; width: 10%; height: 4px; background: #10b981; border-radius: 2px;">
                                                </div>
                                            </div>
                                            <span
                                                class="badge bg-dark bg-opacity-75 text-white position-absolute m-3 px-3 py-2"
                                                style="top: 0; left: 0; border-radius: 20px; backdrop-filter: blur(4px);">
                                                <span style="color: #ef4444; font-size:12px;">●</span> Live
                                            </span>
                                        </div>
                                        <div class="d-flex gap-4 mt-3 pt-2">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="fw-bold text-gray">EAR (Mata)</small>
                                                    <span class="fw-bold text-dark font-heading"
                                                        id="ear-val">0.32</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">Normal</span>
                                                    <div class="progress flex-grow-1"
                                                        style="height: 6px; border-radius: 10px;">
                                                        <div class="progress-bar bg-primary" id="ear-bar"
                                                            style="width: 80%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="width: 1px; background: #e2e8f0;"></div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="fw-bold text-gray">MAR (Mulut)</small>
                                                    <span class="fw-bold text-dark font-heading"
                                                        id="mar-val">0.18</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">Normal</span>
                                                    <div class="progress flex-grow-1"
                                                        style="height: 6px; border-radius: 10px;">
                                                        <div class="progress-bar bg-info" id="mar-bar"
                                                            style="width: 30%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="bg-light p-3 rounded-4 mb-3 d-flex align-items-center gap-3 border border-secondary border-opacity-10 shadow-sm"
                                                style="transition: 0.3s;"
                                                onmouseover="this.style.transform='translateX(5px)'"
                                                onmouseout="this.style.transform='translateX(0)'">
                                                <div class="stat-icon bg-primary bg-opacity-10 text-primary"
                                                    style="width: 48px; height: 48px; border-radius: 50%;"><i
                                                        class="fa-regular fa-user"></i></div>
                                                <div>
                                                    <small class="text-gray fw-bold d-block mb-1">Status
                                                        Pengemudi</small>
                                                    <span
                                                        class="badge bg-success text-white px-3 py-1 rounded-pill shadow-sm status-badge-custom">Fokus</span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <span
                                                    class="badge bg-white border border-success text-success px-4 py-2 rounded-pill shadow-sm mb-3 status-badge-custom d-inline-flex align-items-center"><i
                                                        class="fa-solid fa-check-circle me-2 fs-6"></i> Mata
                                                    Terbuka</span>
                                            </div>
                                            <div class="row g-2 mb-4">
                                                <div class="col-4">
                                                    <div class="border rounded-4 p-2 text-center bg-white shadow-sm hover-up"
                                                        style="transition: 0.3s;"
                                                        onmouseover="this.style.transform='translateY(-3px)'"
                                                        onmouseout="this.style.transform='translateY(0)'">
                                                        <small class="text-gray fw-bold d-block mb-1"
                                                            style="font-size: 0.7rem;">Yaw</small>
                                                        <strong
                                                            class="text-dark d-block mb-1 font-heading fs-5">2°</strong>
                                                        <small class="text-muted" style="font-size: 0.6rem;">(-20° ~
                                                            +20°)</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border rounded-4 p-2 text-center bg-white shadow-sm hover-up"
                                                        style="transition: 0.3s;"
                                                        onmouseover="this.style.transform='translateY(-3px)'"
                                                        onmouseout="this.style.transform='translateY(0)'">
                                                        <small class="text-gray fw-bold d-block mb-1"
                                                            style="font-size: 0.7rem;">Pitch</small>
                                                        <strong
                                                            class="text-dark d-block mb-1 font-heading fs-5">-1°</strong>
                                                        <small class="text-muted" style="font-size: 0.6rem;">(-15° ~
                                                            +15°)</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border rounded-4 p-2 text-center bg-white shadow-sm hover-up"
                                                        style="transition: 0.3s;"
                                                        onmouseover="this.style.transform='translateY(-3px)'"
                                                        onmouseout="this.style.transform='translateY(0)'">
                                                        <small class="text-gray fw-bold d-block mb-1"
                                                            style="font-size: 0.7rem;">Roll</small>
                                                        <strong
                                                            class="text-dark d-block mb-1 font-heading fs-5">1°</strong>
                                                        <small class="text-muted" style="font-size: 0.6rem;">(-10° ~
                                                            +10°)</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-success bg-opacity-10 p-3 rounded-4 border border-success border-opacity-25 d-flex gap-3 align-items-start shadow-sm mt-auto"
                                            style="transition: 0.3s;"
                                            onmouseover="this.style.transform='translateX(5px)'"
                                            onmouseout="this.style.transform='translateX(0)'">
                                            <div class="text-success mt-1 bg-white rounded-circle p-1 shadow-sm"><i
                                                    class="fa-solid fa-shield-check fs-5"></i></div>
                                            <div>
                                                <strong class="text-success d-block mb-1 font-heading">Deteksi
                                                    Kantuk</strong>
                                                <strong class="text-dark d-block mb-1" style="font-size: 0.95rem;">Tidak
                                                    terdeteksi</strong>
                                                <small class="text-muted" style="font-size: 0.75rem;">Kondisi wajah dan
                                                    mata dalam batas normal.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-5">
                            <div class="dash-card d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-info bg-opacity-10 text-info"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;"><i
                                                class="fa-solid fa-location-dot"></i></div>
                                        <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.1rem;">Lokasi
                                            & Rute Perjalanan</h5>
                                    </div>
                                    <button
                                        class="btn btn-sm text-primary fw-bold bg-primary bg-opacity-10 rounded-pill px-3 py-2 shadow-sm"
                                        style="font-size: 0.75rem; transition: 0.3s;"
                                        onmouseover="this.classList.add('bg-primary', 'text-white'); this.classList.remove('text-primary', 'bg-opacity-10')"
                                        onmouseout="this.classList.remove('bg-primary', 'text-white'); this.classList.add('text-primary', 'bg-opacity-10')">
                                        <i class="fa-solid fa-location-crosshairs me-1"></i> Live Location
                                    </button>
                                </div>
                                <div class="flex-grow-1 position-relative rounded-4 overflow-hidden border border-secondary border-opacity-25 shadow-sm"
                                    style="min-height: 320px;">
                                    <div id="dashboard-map"
                                        style="width: 100%; height: 100%; position: absolute; z-index: 1;"></div>
                                    <div class="position-absolute bg-white px-3 py-2 shadow-sm rounded-4 d-flex align-items-center gap-2"
                                        style="top: 15px; left: 15px; z-index: 400;">
                                        <div
                                            class="text-primary bg-primary bg-opacity-10 p-2 rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-location-arrow"></i>
                                        </div>
                                        <div>
                                            <small class="d-block text-gray fw-bold"
                                                style="font-size: 0.65rem;">Kecepatan</small>
                                            <span class="fw-bold text-dark font-heading" style="font-size: 0.9rem;">62
                                                <span class="fw-normal text-muted"
                                                    style="font-size: 0.7rem;">km/jam</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 3: Log Pelanggaran & Statistik -->
                    <div class="row g-4 pb-3">
                        <div class="col-xl-8 col-lg-7">
                            <div class="dash-card h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;"><i
                                                class="fa-solid fa-file-lines"></i></div>
                                        <div>
                                            <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.1rem;">
                                                Log Pelanggaran Terdeteksi</h5>
                                            <small class="text-gray">Riwayat deteksi kantuk dan pelanggaran selama
                                                perjalanan.</small>
                                        </div>
                                    </div>
                                    <button
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold shadow-sm"
                                        style="font-size: 0.8rem; transition: 0.3s;"
                                        onmouseover="this.classList.add('bg-primary', 'text-white')"
                                        onmouseout="this.classList.remove('bg-primary', 'text-white')">
                                        Lihat Semua <i class="fa-solid fa-chevron-down ms-1"></i>
                                    </button>
                                </div>
                                <div class="table-responsive flex-grow-1">
                                    <table class="table table-hover align-middle border-bottom border-light mb-0"
                                        style="font-size: 0.85rem;">
                                        <thead>
                                            <tr>
                                                <th class="text-muted fw-bold py-3 border-0 text-uppercase"
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Waktu</th>
                                                <th class="text-muted fw-bold py-3 border-0 text-uppercase"
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Jenis Deteksi</th>
                                                <th class="text-muted fw-bold py-3 border-0 text-uppercase"
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Nilai (EAR/MAR)
                                                </th>
                                                <th class="text-muted fw-bold py-3 border-0 text-uppercase"
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Status</th>
                                                <th class="text-muted fw-bold py-3 border-0 text-end text-uppercase"
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Tindakan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                            <tr style="transition: 0.2s; cursor: pointer;">
                                                <td class="text-muted py-3 fw-medium">09:12:34</td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center gap-2"><i
                                                            class="fa-solid fa-eye text-primary bg-primary bg-opacity-10 p-2 rounded-circle"></i><span
                                                            class="text-dark fw-bold">Mata Mengantuk</span></div>
                                                </td>
                                                <td class="py-3"><span class="text-primary fw-bold">EAR: 0.21</span>
                                                    <span class="text-muted mx-1">|</span> <span
                                                        class="text-info fw-bold">MAR: 0.16</span>
                                                </td>
                                                <td class="py-3"><span
                                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill"><i
                                                            class="fa-solid fa-triangle-exclamation me-1"></i>
                                                        Peringatan</span></td>
                                                <td class="text-end py-3"><button
                                                        class="btn btn-sm text-primary fw-bold px-3 py-1 bg-primary bg-opacity-10"
                                                        style="border-radius: 8px; transition: 0.3s;"
                                                        onmouseover="this.classList.add('bg-primary', 'text-white')"
                                                        onmouseout="this.classList.remove('bg-primary', 'text-white')"><i
                                                            class="fa-solid fa-eye me-1"></i> Lihat <i
                                                            class="fa-solid fa-chevron-right ms-1 text-muted"></i></button>
                                                </td>
                                            </tr>
                                            <tr style="transition: 0.2s; cursor: pointer;">
                                                <td class="text-muted py-3 fw-medium">09:05:18</td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center gap-2"><i
                                                            class="fa-solid fa-face-tired text-danger bg-danger bg-opacity-10 p-2 rounded-circle"></i><span
                                                            class="text-dark fw-bold">Menguap</span></div>
                                                </td>
                                                <td class="py-3"><span class="text-primary fw-bold">EAR: 0.27</span>
                                                    <span class="text-muted mx-1">|</span> <span
                                                        class="text-info fw-bold">MAR: 0.22</span>
                                                </td>
                                                <td class="py-3"><span
                                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill"><i
                                                            class="fa-solid fa-triangle-exclamation me-1"></i>
                                                        Peringatan</span></td>
                                                <td class="text-end py-3"><button
                                                        class="btn btn-sm text-primary fw-bold px-3 py-1 bg-primary bg-opacity-10"
                                                        style="border-radius: 8px; transition: 0.3s;"
                                                        onmouseover="this.classList.add('bg-primary', 'text-white')"
                                                        onmouseout="this.classList.remove('bg-primary', 'text-white')"><i
                                                            class="fa-solid fa-eye me-1"></i> Lihat <i
                                                            class="fa-solid fa-chevron-right ms-1 text-muted"></i></button>
                                                </td>
                                            </tr>
                                            <tr style="transition: 0.2s; cursor: pointer;">
                                                <td class="text-muted py-3 fw-medium">08:57:03</td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center gap-2"><i
                                                            class="fa-solid fa-eye text-success bg-success bg-opacity-10 p-2 rounded-circle"></i><span
                                                            class="text-dark fw-bold">Mata Terbuka</span></div>
                                                </td>
                                                <td class="py-3"><span class="text-primary fw-bold">EAR: 0.31</span>
                                                    <span class="text-muted mx-1">|</span> <span
                                                        class="text-info fw-bold">MAR: 0.18</span>
                                                </td>
                                                <td class="py-3"><span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i
                                                            class="fa-solid fa-check-circle me-1"></i> Normal</span>
                                                </td>
                                                <td class="text-end py-3"><button
                                                        class="btn btn-sm text-primary fw-bold px-3 py-1 bg-primary bg-opacity-10"
                                                        style="border-radius: 8px; transition: 0.3s;"
                                                        onmouseover="this.classList.add('bg-primary', 'text-white')"
                                                        onmouseout="this.classList.remove('bg-primary', 'text-white')"><i
                                                            class="fa-solid fa-eye me-1"></i> Lihat <i
                                                            class="fa-solid fa-chevron-right ms-1 text-muted"></i></button>
                                                </td>
                                            </tr>
                                            <tr style="transition: 0.2s; cursor: pointer;">
                                                <td class="text-muted py-3 fw-medium">08:42:17</td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center gap-2"><i
                                                            class="fa-solid fa-user-check text-success bg-success bg-opacity-10 p-2 rounded-circle"></i><span
                                                            class="text-dark fw-bold">Posisi Kepala Normal</span></div>
                                                </td>
                                                <td class="py-3 text-muted"><span class="fw-bold text-dark">Yaw:
                                                        1°</span> | Pitch: 0° | Roll: 1°</td>
                                                <td class="py-3"><span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i
                                                            class="fa-solid fa-check-circle me-1"></i> Normal</span>
                                                </td>
                                                <td class="text-end py-3"><button
                                                        class="btn btn-sm text-primary fw-bold px-3 py-1 bg-primary bg-opacity-10"
                                                        style="border-radius: 8px; transition: 0.3s;"
                                                        onmouseover="this.classList.add('bg-primary', 'text-white')"
                                                        onmouseout="this.classList.remove('bg-primary', 'text-white')"><i
                                                            class="fa-solid fa-eye me-1"></i> Lihat <i
                                                            class="fa-solid fa-chevron-right ms-1 text-muted"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-5">
                            <div class="dash-card h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-info bg-opacity-10 text-info"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;"><i
                                                class="fa-solid fa-chart-line"></i></div>
                                        <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.1rem;">
                                            Statistik Perjalanan</h5>
                                    </div>
                                    <select class="form-select form-select-sm bg-light border-0 fw-bold shadow-sm"
                                        style="width: auto; border-radius: 8px; cursor: pointer;">
                                        <option>Hari Ini</option>
                                        <option>Minggu Ini</option>
                                    </select>
                                </div>
                                <small class="text-primary fw-bold mb-2">Jumlah Deteksi Kantuk</small>
                                <div class="flex-grow-1 position-relative" style="min-height: 180px;">
                                    <canvas id="chartKantuk"></canvas>
                                </div>
                                <div class="mt-3 bg-primary bg-opacity-10 p-3 rounded-4 border border-primary border-opacity-25 d-flex gap-3 align-items-start shadow-sm"
                                    style="transition: 0.3s;" onmouseover="this.style.transform='translateY(-3px)'"
                                    onmouseout="this.style.transform='translateY(0)'">
                                    <div class="text-primary mt-1 bg-white rounded-circle p-1 shadow-sm"><i
                                            class="fa-solid fa-circle-info"></i></div>
                                    <div>
                                        <strong class="text-primary d-block mb-1 font-heading"
                                            style="font-size: 0.9rem;">Tetap terjaga di perjalanan</strong>
                                        <small class="text-primary text-opacity-75" style="font-size: 0.75rem;">Jangan
                                            memaksakan diri. Istirahat jika merasa lelah.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ================= END VIEW: DASHBOARD ================= -->


                <!-- ================= START VIEW: LIVE MONITOR (BARU DITAMBAHKAN) ================= -->
                <div id="view-live-monitor" style="display: none;">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary"
                                style="width: 46px; height: 46px;"><i class="fa-solid fa-video"></i></div>
                            <div>
                                <h4 class="m-0 font-heading fw-bold text-dark">Live Monitor</h4>
                                <small class="text-gray">Pantau kondisi wajah dan deteksi kantuk secara
                                    real-time.</small>
                            </div>
                        </div>
                        <div>
                            <span
                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 fw-bold shadow-sm"><i
                                    class="fa-solid fa-location-dot me-1"></i> GPS Aktif</span>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- KOLOM KIRI (CAMERA & PENGATURAN) -->
                        <div class="col-xl-7 col-lg-12">

                            <!-- Box Kamera -->
                            <div class="dash-card p-3 mb-4 border border-secondary border-opacity-10">
                                <div class="live-monitor-cam shadow-sm border border-secondary border-opacity-25">
                                    <img src="https://images.unsplash.com/photo-1542282088-72c9c27ed0cd?auto=format&fit=crop&q=80&w=800"
                                        alt="Kamera Simulasi">

                                    <!-- Badge Top Left -->
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span
                                            class="badge bg-danger rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
                                            <div
                                                style="width: 8px; height: 8px; background: #fff; border-radius: 50%; animation: blinkPulse 1.5s infinite;">
                                            </div>
                                            LIVE
                                        </span>
                                    </div>

                                    <!-- Badge Top Right -->
                                    <div class="position-absolute top-0 end-0 m-3 d-flex gap-2">
                                        <span
                                            class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-2 text-white border border-light border-opacity-25"
                                            style="backdrop-filter: blur(4px);">
                                            <i class="fa-regular fa-clock me-1"></i> 09:42:15
                                        </span>
                                        <span
                                            class="badge bg-success bg-opacity-75 rounded-pill px-3 py-2 text-white border border-success border-opacity-25"
                                            style="backdrop-filter: blur(4px);">
                                            <span style="color: #4ade80;">●</span> FPS Aktif
                                        </span>
                                    </div>

                                    <!-- Bounding Box Bawah Tengah (Wajah) -->
                                    <div
                                        style="position: absolute; top: 15%; left: 30%; width: 40%; height: 60%; border: 3px solid #3b82f6; border-radius: 8px; box-shadow: 0 0 15px rgba(59, 130, 246, 0.5); display: flex; flex-direction: column; justify-content: flex-end; align-items: center;">
                                        <span
                                            class="badge bg-primary text-white rounded-pill px-3 py-1 mb-n3 shadow-sm fw-bold"
                                            style="transform: translateY(50%);">Mata Terbuka</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Box Detail Status -->
                            <div class="dash-card mb-4 border border-secondary border-opacity-10">
                                <div class="row g-3 text-center mb-4 border-bottom pb-3">
                                    <div class="col-3 border-end">
                                        <small class="text-muted d-block mb-1 font-heading"
                                            style="font-size:0.75rem;"><i class="fa-regular fa-eye text-primary"></i>
                                            Status Deteksi</small>
                                        <strong class="text-success fw-bold"><span style="color: #10b981;">●</span>
                                            Normal</strong>
                                    </div>
                                    <div class="col-3 border-end">
                                        <small class="text-muted d-block mb-1 font-heading"
                                            style="font-size:0.75rem;"><i class="fa-solid fa-eye text-primary"></i>
                                            Kondisi Mata</small>
                                        <strong class="text-dark fw-bold">Terbuka</strong>
                                    </div>
                                    <div class="col-3 border-end">
                                        <small class="text-muted d-block mb-1 font-heading"
                                            style="font-size:0.75rem;"><i class="fa-solid fa-user text-primary"></i>
                                            Posisi Kepala</small>
                                        <strong class="text-dark fw-bold">Normal</strong>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted d-block mb-1 font-heading"
                                            style="font-size:0.75rem;"><i class="fa-solid fa-moon text-primary"></i>
                                            Kantuk</small>
                                        <strong class="text-gray fw-bold">Tidak Terdeteksi</strong>
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="fw-bold text-dark">EAR (Mata)</small>
                                            <span class="fw-bold text-dark">0.32</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar bg-primary" style="width: 80%"></div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="fw-bold text-dark">MAR (Mulut)</small>
                                            <span class="fw-bold text-dark">0.18</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar bg-primary" style="width: 40%"></div>
                                        </div>
                                    </div>
                                    <div class="col-4 d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="fw-bold text-dark d-block">Head Pose <span
                                                    class="text-muted fw-normal" style="font-size: 0.7rem;">(Yaw / Pitch
                                                    / Roll)</span></small>
                                            <span class="fw-bold text-dark">-2° / 1° / 0°</span>
                                        </div>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">Normal</span>
                                    </div>
                                </div>

                                <div
                                    class="bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 p-3 d-flex align-items-start gap-3">
                                    <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                                    <small class="text-primary fw-medium" style="font-size: 0.8rem;">Sistem akan
                                        memberikan peringatan jika terdeteksi tanda-tanda kantuk seperti mata tertutup,
                                        menguap, atau posisi kepala tidak normal.</small>
                                </div>
                            </div>

                            <!-- Box Pengaturan Sistem -->
                            <div class="dash-card border border-secondary border-opacity-10">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-gear text-primary fs-5"></i>
                                        <h6 class="m-0 font-heading fw-bold">Pengaturan Sistem</h6>
                                    </div>
                                    <a href="#" class="text-primary fw-bold text-decoration-none"
                                        style="font-size: 0.8rem;">Kelola Pengaturan <i
                                            class="fa-solid fa-arrow-right"></i></a>
                                </div>
                                <div class="row g-4">
                                    <div class="col-md-6 border-end">
                                        <small class="text-dark fw-bold d-block mb-3"><i
                                                class="fa-solid fa-shield-halved text-primary me-2"></i> Deteksi &
                                            Peringatan</small>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">Sensitivitas Deteksi</small>
                                                <small class="text-dark fw-bold">70%</small>
                                            </div>
                                            <input type="range" class="custom-range mt-1" min="0" max="100" value="70">
                                        </div>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">Durasi Peringatan</small>
                                                <small class="text-dark fw-bold">60 detik</small>
                                            </div>
                                            <input type="range" class="custom-range mt-1" min="0" max="120" value="60">
                                        </div>
                                        <div>
                                            <small class="text-muted d-block mb-1">Kualitas Kamera</small>
                                            <select class="form-select form-select-sm text-dark fw-medium shadow-sm">
                                                <option selected>Tinggi (HD)</option>
                                                <option>Sedang</option>
                                                <option>Rendah</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <small class="text-dark fw-bold d-block mb-3"><i
                                                class="fa-solid fa-bolt text-primary me-2"></i> Kontrol
                                            Perangkat</small>

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted">Kamera Depan</small>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" checked>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted">Notifikasi Suara</small>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" checked>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <small class="text-muted">Screenshot Otomatis</small>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" checked>
                                            </div>
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm fw-bold py-2"><i
                                                class="fa-solid fa-floppy-disk me-2"></i> Simpan Pengaturan</button>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Akhir Kolom Kiri -->
                        <!-- KOLOM KANAN (RINGKASAN, METRIK, LOG & MAP) -->
                        <div class="col-xl-5 col-lg-12 d-flex flex-column gap-4">

                            <!-- Ringkasan Perjalanan -->
                            <div class="dash-card border border-secondary border-opacity-10 p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3"><i
                                                class="fa-solid fa-map-location-dot"></i></div>
                                        <h6 class="m-0 font-heading fw-bold">Ringkasan Perjalanan</h6>
                                    </div>
                                    <a href="#" class="text-primary fw-bold text-decoration-none"
                                        style="font-size: 0.75rem;">Lihat Semua <i
                                            class="fa-solid fa-arrow-right ms-1"></i></a>
                                </div>

                                <div class="row g-3">
                                    <div class="col-4 border-end">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="fa-solid fa-gauge-high text-primary bg-primary bg-opacity-10 p-1 rounded-circle"
                                                    style="font-size:0.65rem;"></i>
                                                <small class="text-muted fw-bold"
                                                    style="font-size: 0.7rem;">Kecepatan</small>
                                            </div>
                                            <strong class="text-dark font-heading">62 <span class="fw-normal text-muted"
                                                    style="font-size:0.75rem;">km/jam</span></strong>
                                            <small class="text-success mt-1" style="font-size: 0.65rem;">Dalam batas
                                                normal</small>
                                        </div>
                                    </div>
                                    <div class="col-4 border-end px-3">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="fa-solid fa-location-dot text-primary bg-primary bg-opacity-10 p-1 rounded-circle"
                                                    style="font-size:0.65rem;"></i>
                                                <small class="text-muted fw-bold" style="font-size: 0.7rem;">Lokasi Saat
                                                    Ini</small>
                                            </div>
                                            <strong class="text-dark font-heading text-truncate"
                                                style="font-size: 0.85rem;">Jl. Raya Surabaya - Malang</strong>
                                            <small class="text-muted mt-1" style="font-size: 0.65rem;">Kab.
                                                Pasuruan</small>
                                        </div>
                                    </div>
                                    <div class="col-4 ps-3">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="fa-regular fa-clock text-primary bg-primary bg-opacity-10 p-1 rounded-circle"
                                                    style="font-size:0.65rem;"></i>
                                                <small class="text-muted fw-bold" style="font-size: 0.7rem;">Durasi
                                                    Berkendara</small>
                                            </div>
                                            <strong class="text-dark font-heading">1j 23m</strong>
                                            <small class="text-muted mt-1" style="font-size: 0.65rem;">Sejak
                                                08:42</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Metrik Sistem -->
                            <div class="dash-card border border-secondary border-opacity-10 p-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3"><i
                                            class="fa-solid fa-sliders"></i></div>
                                    <h6 class="m-0 font-heading fw-bold">Metrik Sistem</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-3">
                                        <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.65rem;">EAR
                                            (Eye Aspect Ratio)</small>
                                        <strong class="text-dark d-block font-heading mb-1 fs-5">0.34</strong>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"
                                            style="font-size: 0.6rem;">Normal</span>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.65rem;">MAR
                                            (Mouth Aspect Ratio)</small>
                                        <strong class="text-dark d-block font-heading mb-1 fs-5">0.12</strong>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"
                                            style="font-size: 0.6rem;">Normal</span>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.65rem;">Yaw
                                            (Kepala)</small>
                                        <strong class="text-dark d-block font-heading mb-1 fs-5">-2°</strong>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"
                                            style="font-size: 0.6rem;">Normal</span>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.65rem;">Pitch
                                            (Kepala)</small>
                                        <strong class="text-dark d-block font-heading mb-1 fs-5">1°</strong>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"
                                            style="font-size: 0.6rem;">Normal</span>
                                    </div>

                                    <div
                                        class="col-12 mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted fw-bold d-block mb-1"
                                                style="font-size: 0.65rem;">Roll (Kepala)</small>
                                            <strong class="text-dark d-block font-heading mb-1 fs-5">0°</strong>
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"
                                                style="font-size: 0.6rem;">Normal</span>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted fw-bold d-block mb-1"
                                                style="font-size: 0.75rem;">Attention Score</small>
                                            <strong class="text-dark font-heading fs-4 d-block mb-2">98%</strong>
                                            <div class="progress shadow-sm"
                                                style="height: 6px; width: 120px; border-radius: 10px;">
                                                <div class="progress-bar bg-success" style="width: 98%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Log Pelanggaran Mini -->
                            <div class="dash-card border border-secondary border-opacity-10 p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3"><i
                                                class="fa-solid fa-file-invoice"></i></div>
                                        <h6 class="m-0 font-heading fw-bold">Log Pelanggaran</h6>
                                    </div>
                                    <a href="#" class="text-primary fw-bold text-decoration-none"
                                        style="font-size: 0.75rem;">Lihat Semua <i
                                            class="fa-solid fa-arrow-right ms-1"></i></a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-borderless table-hover align-middle mb-0"
                                        style="font-size: 0.75rem;">
                                        <thead>
                                            <tr class="border-bottom text-muted">
                                                <th class="py-2 text-uppercase fw-bold" style="font-size: 0.65rem;">
                                                    Waktu</th>
                                                <th class="py-2 text-uppercase fw-bold" style="font-size: 0.65rem;">
                                                    Jenis Pelanggaran</th>
                                                <th class="py-2 text-uppercase fw-bold" style="font-size: 0.65rem;">
                                                    Status</th>
                                                <th class="py-2 text-uppercase fw-bold text-end"
                                                    style="font-size: 0.65rem;">Atensi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="cursor: pointer;" class="border-bottom border-light">
                                                <td class="text-muted fw-bold">09:10:42</td>
                                                <td class="text-dark fw-bold">Menguap</td>
                                                <td><span
                                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 rounded-pill"><i
                                                            class="fa-solid fa-circle text-warning me-1"
                                                            style="font-size:0.4rem;"></i> Ringan</span></td>
                                                <td class="text-end fw-bold text-muted">85% <i
                                                        class="fa-solid fa-chevron-right ms-2"
                                                        style="font-size: 0.6rem;"></i></td>
                                            </tr>
                                            <tr style="cursor: pointer;" class="border-bottom border-light">
                                                <td class="text-muted fw-bold">09:08:15</td>
                                                <td class="text-dark fw-bold">Mata Mengantuk</td>
                                                <td><span
                                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill"><i
                                                            class="fa-solid fa-circle text-danger me-1"
                                                            style="font-size:0.4rem;"></i> Sedang</span></td>
                                                <td class="text-end fw-bold text-muted">72% <i
                                                        class="fa-solid fa-chevron-right ms-2"
                                                        style="font-size: 0.6rem;"></i></td>
                                            </tr>
                                            <tr style="cursor: pointer;">
                                                <td class="text-muted fw-bold">08:52:10</td>
                                                <td class="text-dark fw-bold">Posisi Kepala Tidak Normal</td>
                                                <td><span class="badge bg-danger text-white px-2 py-1 rounded-pill"><i
                                                            class="fa-solid fa-circle text-white me-1"
                                                            style="font-size:0.4rem;"></i> Berat</span></td>
                                                <td class="text-end fw-bold text-muted">45% <i
                                                        class="fa-solid fa-chevron-right ms-2"
                                                        style="font-size: 0.6rem;"></i></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Lokasi Perjalanan (Mini Map) -->
                            <div class="dash-card border border-secondary border-opacity-10 p-0 flex-grow-1 overflow-hidden"
                                style="min-height: 250px; position: relative;">
                                <div
                                    class="position-absolute top-0 start-0 w-100 p-3 d-flex justify-content-between align-items-center z-3">
                                    <div
                                        class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill shadow-sm">
                                        <i class="fa-solid fa-location-arrow text-primary"></i>
                                        <h6 class="m-0 font-heading fw-bold" style="font-size: 0.8rem;">Lokasi
                                            Perjalanan</h6>
                                    </div>
                                    <span
                                        class="badge bg-success bg-opacity-75 text-white rounded-pill px-3 py-2 border border-success border-opacity-25 shadow-sm"
                                        style="backdrop-filter: blur(4px);">
                                        <span style="color: #4ade80;">●</span> Live
                                    </span>
                                </div>
                                <div id="live-map" style="width: 100%; height: 100%; position: absolute; z-index: 1;">
                                </div>
                            </div>

                        </div> <!-- Akhir Kolom Kanan -->
                    </div> <!-- Akhir Row Live Monitor -->

                </div>
                <!-- ================= END VIEW: LIVE MONITOR ================= -->

            </div> <!-- End Container Fluid -->
        </div> <!-- End Page Content Wrapper -->
    </div> <!-- End Wrapper -->

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ================= SCRIPT INTERAKTIVITAS DASHBOARD & LIVE MONITOR ================= -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // --- 1. Sidebar Toggle Mobile ---
            const menuToggle = document.getElementById('menu-toggle');
            const wrapper = document.getElementById('wrapper');
            menuToggle.addEventListener('click', function (e) {
                e.preventDefault();
                wrapper.classList.toggle('toggled');
            });

            // --- 2. Navigasi Antar Tab (Dashboard vs Live Monitor) ---
            const btnDashboard = document.getElementById('nav-dashboard-btn');
            const btnLive = document.getElementById('nav-live-btn');

            const viewDashboard = document.getElementById('view-dashboard');
            const viewLive = document.getElementById('view-live-monitor');

            btnDashboard.addEventListener('click', function (e) {
                e.preventDefault();
                // Ubah Tampilan
                viewDashboard.style.display = 'block';
                viewLive.style.display = 'none';

                // Ubah State Tombol
                btnDashboard.classList.add('active');
                btnLive.classList.remove('active');
            });

            btnLive.addEventListener('click', function (e) {
                e.preventDefault();
                // Ubah Tampilan
                viewDashboard.style.display = 'none';
                viewLive.style.display = 'block';

                // Ubah State Tombol
                btnLive.classList.add('active');
                btnDashboard.classList.remove('active');

                // PENTING: Leaflet butuh invalidateSize saat kontainernya berubah dari display:none menjadi block
                setTimeout(() => {
                    if (typeof liveMap !== 'undefined') liveMap.invalidateSize();
                }, 100);
            });

            // --- 3. Real-Time Clock ---
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                document.getElementById('realtime-clock').textContent = timeString;

                const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
                const dateString = now.toLocaleDateString('id-ID', options);
                document.getElementById('realtime-date').textContent = dateString;
            }
            setInterval(updateClock, 1000);
            updateClock();

            // --- 4. Simulasi EAR & MAR Dynamic Smoothly ---
            setInterval(() => {
                let newEar = (Math.random() * (0.35 - 0.28) + 0.28).toFixed(2);
                let earWidth = (newEar / 0.40) * 100;
                let newMar = (Math.random() * (0.18 - 0.10) + 0.10).toFixed(2);
                let marWidth = (newMar / 0.30) * 100;

                // Update di Dashboard
                if (document.getElementById('ear-val')) {
                    document.getElementById('ear-val').textContent = newEar;
                    document.getElementById('ear-bar').style.width = earWidth + '%';
                    document.getElementById('mar-val').textContent = newMar;
                    document.getElementById('mar-bar').style.width = marWidth + '%';
                }
            }, 2500);

            // --- 5. Konfigurasi Map Dashboard (Map 1) ---
            var dashboardMap = L.map('dashboard-map', {
                zoomControl: true, attributionControl: false, zoomSnap: 0.1
            }).setView([-7.953, 112.613], 13.5);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(dashboardMap);

            var driverIcon = L.divIcon({
                className: 'custom-gps-marker',
                html: `<div class="ping"></div><div class="dot"></div>`,
                iconSize: [34, 34], iconAnchor: [17, 17]
            });
            L.marker([-7.953, 112.613], { icon: driverIcon }).addTo(dashboardMap);
            var latlngs = [[-7.935, 112.580], [-7.940, 112.595], [-7.948, 112.605], [-7.953, 112.613]];
            L.polyline(latlngs, { color: '#3b82f6', weight: 5, opacity: 0.9 }).addTo(dashboardMap);

            // --- 6. Konfigurasi Map Live Monitor (Map 2) ---
            var liveMap = L.map('live-map', {
                zoomControl: true, attributionControl: false, zoomSnap: 0.1
            }).setView([-7.953, 112.613], 13.5);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(liveMap);
            L.marker([-7.953, 112.613], { icon: driverIcon }).addTo(liveMap);
            L.polyline(latlngs, { color: '#3b82f6', weight: 5, opacity: 0.9 }).addTo(liveMap);

            // Fix map size on resize
            window.addEventListener('resize', () => {
                dashboardMap.invalidateSize();
                liveMap.invalidateSize();
            });
            setTimeout(() => { dashboardMap.invalidateSize(); }, 500);

            // --- 7. Chart.js (Grafik Jumlah Deteksi Kantuk) ---
            const ctx = document.getElementById('chartKantuk').getContext('2d');
            let gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00'],
                    datasets: [{
                        label: 'Deteksi',
                        data: [0, 1, 3, 1, 4, 6],
                        borderColor: '#3b82f6',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8 } },
                    scales: {
                        y: {
                            beginAtZero: true, max: 8,
                            ticks: { stepSize: 2, color: '#94a3b8', font: { size: 11, family: 'Inter' } },
                            border: { display: false, dash: [4, 4] },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { color: '#94a3b8', font: { size: 11, family: 'Inter' } },
                            grid: { display: false },
                            border: { display: false }
                        }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        });
    </script>
</body>

</html>