<?php
session_start();

// Simulasi cek login (Hapus komentar jika sistem login sudah berjalan)
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
    <title>Dashboard Pengemudi - AWAS 2.0</title>

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
        /* ==========================================
           1. CORE VARIABLES & TYPOGRAPHY
           ========================================== */
        :root {
            --primary-color: #4338ca;
            --primary-dark: #312e81;
            --primary-light: #e0e7ff;
            --secondary-color: #0ea5e9;
            --accent-color: #f43f5e;

            --gradient-main: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
            --gradient-accent: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
            --gradient-danger: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);

            --bg-body: #f8fafc;
            --bg-white: #ffffff;

            --text-dark: #0f172a;
            --text-gray: #475569;
            --text-light: #94a3b8;

            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);

            --sidebar-width: 280px;
            --border-radius-xl: 24px;
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

        /* ==========================================
           2. SIDEBAR STYLING (MODERNIZED)
           ========================================== */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        #sidebar-wrapper {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--bg-white);
            border-right: 1px solid rgba(226, 232, 240, 0.8);
            transition: margin 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }

        .sidebar-heading {
            padding: 2rem 1.5rem;
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-icon-logo {
            width: 40px;
            height: 40px;
            background: var(--gradient-main);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.1rem;
            box-shadow: 0 8px 16px rgba(67, 56, 202, 0.25);
        }

        .list-group-flush {
            padding: 10px 15px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .list-group-flush::-webkit-scrollbar {
            width: 4px;
        }

        .list-group-flush::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .list-group-item {
            border: none;
            padding: 14px 18px;
            margin-bottom: 8px;
            border-radius: 14px;
            color: var(--text-gray);
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.3s ease;
            background: transparent;
            position: relative;
            overflow: hidden;
        }

        .list-group-item i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            color: var(--text-light);
            transition: all 0.3s ease;
            z-index: 2;
        }

        .list-group-item span {
            z-index: 2;
        }

        .list-group-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
            transform: translateX(4px);
        }

        .list-group-item:hover i {
            color: var(--primary-color);
        }

        .list-group-item.active {
            background: var(--gradient-main);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 56, 202, 0.3);
        }

        .list-group-item.active i {
            color: white;
        }

        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #94a3b8;
            text-transform: uppercase;
            margin: 20px 0 10px 18px;
        }

        .sidebar-footer {
            padding: 24px;
            background: linear-gradient(to top, rgba(255, 255, 255, 1) 80%, rgba(255, 255, 255, 0));
        }

        .btn-logout {
            background: #fff1f2;
            color: var(--accent-color);
            border: 1px solid #ffe4e6;
            width: 100%;
            padding: 12px;
            border-radius: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: var(--gradient-danger);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);
        }

        /* ==========================================
           3. MAIN CONTENT & NAVBAR
           ========================================== */
        #page-content-wrapper {
            min-width: 100vw;
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            background-color: var(--bg-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            padding: 16px 32px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .greeting-title {
            font-size: 1.35rem;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .nav-profile-img {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            transition: 0.3s;
        }

        .nav-profile-img:hover {
            border-color: var(--primary-color);
        }

        .notification-btn {
            position: relative;
            background: var(--bg-white);
            width: 44px;
            height: 44px;
            border-radius: 12px;
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
            border-color: var(--primary-light);
        }

        .notif-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--gradient-danger);
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            width: 20px;
            height: 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(244, 63, 94, 0.3);
        }

        .clock-widget {
            background: var(--bg-white);
            padding: 8px 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        /* ==========================================
           4. DASHBOARD CARDS & WIDGETS
           ========================================== */
        .main-container {
            padding: 32px;
            flex-grow: 1;
        }

        .dash-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-xl);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: var(--shadow-sm);
            padding: 24px;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .dash-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            position: relative;
            z-index: 2;
        }

        .stat-content {
            z-index: 2;
        }

        /* Status Colors Modernized */
        .bg-indigo-light {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: var(--primary-color);
        }

        .bg-green-light {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #16a34a;
        }

        .bg-red-light {
            background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);
            color: var(--accent-color);
        }

        .bg-orange-light {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        /* ==========================================
           6. RESPONSIVE MOBILE
           ========================================== */
        @media (max-width: 992px) {
            #sidebar-wrapper {
                margin-left: calc(var(--sidebar-width) * -1);
            }

            #page-content-wrapper {
                min-width: 100vw;
                margin-left: 0;
                width: 100%;
            }

            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
            }

            #wrapper.toggled #page-content-wrapper {
                margin-left: var(--sidebar-width);
                position: absolute;
            }

            .main-container {
                padding: 16px;
            }

            .navbar-custom {
                padding: 12px 16px;
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">

        <!-- ================= SIDEBAR ================= -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <div class="sidebar-icon-logo">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <span>AWAS <span style="color: var(--secondary-color);">2.0</span></span>
            </div>

            <div class="list-group list-group-flush mt-2">
                <div class="nav-section-title">Menu Utama</div>
                <a href="#" class="list-group-item active">
                    <i class="fa-solid fa-house"></i> <span>Dashboard</span>
                </a>
                <a href="#" class="list-group-item">
                    <i class="fa-solid fa-camera"></i> <span>Live Monitor</span>
                </a>
                <a href="#" class="list-group-item">
                    <i class="fa-solid fa-map-location-dot"></i> <span>Pelacakan GPS</span>
                </a>

                <div class="nav-section-title mt-4">Data & Log</div>
                <a href="#" class="list-group-item">
                    <i class="fa-solid fa-clock-rotate-left"></i> <span>Riwayat Perjalanan</span>
                </a>
                <a href="#" class="list-group-item">
                    <i class="fa-solid fa-triangle-exclamation"></i> <span>Log Peringatan</span>
                </a>

                <div class="nav-section-title mt-4">Preferensi</div>
                <a href="#" class="list-group-item">
                    <i class="fa-solid fa-user-gear"></i> <span>Profil Saya</span>
                </a>
                <a href="#" class="list-group-item">
                    <i class="fa-solid fa-address-book"></i> <span>Kontak Darurat</span>
                </a>
            </div>

            <div class="sidebar-footer">
                <a href="login.php" class="btn-logout text-decoration-none">
                    <i class="fa-solid fa-power-off"></i> Keluar Sistem
                </a>
            </div>
        </div>

        <!-- ================= MAIN CONTENT ================= -->
        <div id="page-content-wrapper">

            <!-- Navbar Atas -->
            <nav class="navbar navbar-expand-lg navbar-custom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none" id="menu-toggle"
                        style="border-radius: 12px; border: 1px solid #e2e8f0; width: 44px; height: 44px;">
                        <i class="fa-solid fa-bars text-dark"></i>
                    </button>
                    <div>
                        <h5 class="m-0 font-heading fw-bold greeting-title">Selamat Datang,
                            <?php echo explode(' ', trim($nama_user))[0]; ?>! 👋</h5>
                        <small class="text-gray" style="font-size: 0.85rem;">Sistem aktif memantau perjalanan
                            Anda.</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <div class="clock-widget d-none d-md-flex">
                        <div class="fw-bold text-dark" style="font-size: 0.95rem; font-family: monospace;"
                            id="realtime-clock">00:00:00 WIB</div>
                        <small class="text-muted fw-medium" style="font-size: 0.75rem;" id="realtime-date">Senin, 1 Jan
                            2026</small>
                    </div>

                    <button class="notification-btn">
                        <i class="fa-regular fa-bell"></i>
                        <span class="notif-badge">2</span>
                    </button>

                    <div class="d-flex align-items-center gap-3 cursor-pointer">
                        <div class="d-none d-md-block text-end">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">
                                <?php echo $nama_user; ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">@<?php echo $username; ?></div>
                        </div>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nama_user); ?>&background=4338ca&color=fff&bold=true"
                            alt="Profile" class="nav-profile-img shadow-sm">
                    </div>
                </div>
            </nav>

            <!-- Container Fluid Dashboard -->
            <div class="main-container">

                <!-- ROW 1: Statistik Cepat (Asimetris / Lebih Dinamis) -->
                <div class="row g-4 mb-4">
                    <!-- Status Sistem: Dibuat sedikit berbeda penekanannya -->
                    <div class="col-xl-3 col-md-6">
                        <div class="dash-card d-flex flex-column justify-content-center"
                            style="background: var(--gradient-main); border: none;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="stat-icon-box" style="background: rgba(255,255,255,0.2); color: white;">
                                    <i class="fa-solid fa-shield-check"></i>
                                </div>
                                <span class="badge bg-white text-primary rounded-pill px-2 py-1"
                                    style="font-size: 0.7rem;"><i class="fa-solid fa-wifi"></i> Online</span>
                            </div>
                            <small class="text-white text-opacity-75 fw-medium" style="font-size: 0.85rem;">Status
                                Sistem</small>
                            <h4 class="m-0 font-heading fw-bold text-white">Aktif & Aman</h4>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="dash-card d-flex align-items-center gap-3">
                            <div class="stat-icon-box bg-indigo-light">
                                <i class="fa-solid fa-car"></i>
                            </div>
                            <div class="stat-content">
                                <small class="text-gray fw-bold text-uppercase"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Kendaraan</small>
                                <h4 class="m-0 font-heading fw-bold text-dark mt-1"><?php echo $plat_nomor; ?></h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="dash-card d-flex align-items-center gap-3">
                            <div class="stat-icon-box bg-orange-light">
                                <i class="fa-solid fa-stopwatch"></i>
                            </div>
                            <div class="stat-content">
                                <small class="text-gray fw-bold text-uppercase"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Durasi Berkendara</small>
                                <h4 class="m-0 font-heading fw-bold text-dark mt-1">01j 15m</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="dash-card d-flex align-items-center gap-3">
                            <div class="stat-icon-box bg-red-light">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                            <div class="stat-content">
                                <small class="text-gray fw-bold text-uppercase"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Peringatan Hari Ini</small>
                                <h4 class="m-0 font-heading fw-bold text-dark mt-1">0 <span
                                        style="font-size: 0.9rem; font-weight: 500; color: #64748b;">Kali</span></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Grid Kamera dan Peta dilanjutkan di Part 2 -->
                 <!-- ROW 2: Live Camera & Live Map (Asimetris 7:5 agar tidak monoton) -->
                <div class="row g-4 mb-4">
                    
                    <!-- KIRI: Live Computer Vision Feed (7 Kolom) -->
                    <div class="col-lg-7">
                        <div class="dash-card d-flex flex-column h-100 p-0">
                            <!-- Header Card -->
                            <div class="p-4 pb-0 d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.15rem;">Live Monitoring Kamera</h5>
                                    <small class="text-gray">Pemindaian wajah & mata real-time</small>
                                </div>
                                <span class="badge rounded-pill px-3 py-2 d-flex align-items-center gap-2" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                                    <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: blinkPulse 1.5s infinite;"></div>
                                    Kamera Aktif
                                </span>
                            </div>

                            <!-- Feed Kamera Mockup -->
                            <div class="px-4 mb-4">
                                <div class="cv-camera-feed shadow-inner" style="height: 280px; border-radius: 20px;">
                                    <i class="fa-solid fa-user-tie cv-face"></i>
                                    <div class="cv-bounding-box">
                                        <div class="cv-scan-line"></div>
                                        <div class="cv-corner cv-c-tl"></div>
                                        <div class="cv-corner cv-c-tr"></div>
                                        <div class="cv-corner cv-c-bl"></div>
                                        <div class="cv-corner cv-c-br"></div>
                                    </div>
                                    <!-- Indikator Kinerja Model -->
                                    <div class="position-absolute" style="bottom: 15px; left: 20px; font-size: 0.75rem; font-family: monospace; background: rgba(0,0,0,0.5); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                                        <span style="color: #cbd5e1;">FPS:</span> <span class="text-success fw-bold">30.0</span> <span style="color: #cbd5e1; margin: 0 5px;">|</span>
                                        <span style="color: #cbd5e1;">LAT:</span> <span class="text-warning fw-bold">12ms</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Real-time Metrics (EAR & MAR) -->
                            <div class="row g-0 border-top mt-auto" style="background: #f8fafc;">
                                <div class="col-6 p-4 border-end">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-gray fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">EAR (Mata)</small>
                                        <span class="badge bg-white text-dark border shadow-sm" style="font-size: 0.8rem;" id="ear-value">0.32</span>
                                    </div>
                                    <div class="progress-bar-custom" style="height: 6px;">
                                        <div class="progress-fill" id="ear-bar" style="width: 85%; background: var(--gradient-main);"></div>
                                    </div>
                                </div>
                                <div class="col-6 p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-gray fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">MAR (Mulut)</small>
                                        <span class="badge bg-white text-dark border shadow-sm" style="font-size: 0.8rem;" id="mar-value">0.15</span>
                                    </div>
                                    <div class="progress-bar-custom" style="height: 6px;">
                                        <div class="progress-fill" id="mar-bar" style="width: 30%; background: var(--gradient-accent);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: Live Tracking Map (5 Kolom) -->
                    <div class="col-lg-5">
                        <div class="dash-card d-flex flex-column h-100 p-0">
                            <!-- Header Card -->
                            <div class="p-4 pb-3 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.15rem;">Pelacakan GPS Aktif</h5>
                                <button class="btn btn-sm text-primary fw-bold" style="background: var(--primary-light); border-radius: 10px; font-size: 0.75rem;">
                                    <i class="fa-solid fa-expand"></i> Penuh
                                </button>
                            </div>

                            <!-- Peta Wrapper -->
                            <div class="flex-grow-1 position-relative p-4 pt-0">
                                <div style="position: relative; height: 100%; min-height: 350px; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);">
                                    <div id="dashboard-map" style="width: 100%; height: 100%; z-index: 1;"></div>
                                    
                                    <!-- Overlay Status Peta -->
                                    <div class="position-absolute bg-white px-3 py-2 shadow-sm d-flex align-items-center gap-3" 
                                         style="top: 15px; left: 15px; z-index: 10; border: 1px solid #f1f5f9; border-radius: 12px;">
                                        <div style="width: 35px; height: 35px; background: var(--primary-light); color: var(--primary-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                            <i class="fa-solid fa-location-arrow"></i>
                                        </div>
                                        <div>
                                            <small class="d-block text-gray fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Kecepatan</small>
                                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">65 <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 500;">km/h</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ROW 3: Riwayat Peringatan Terkini (Full Width) -->
                <div class="row">
                    <div class="col-12">
                        <div class="dash-card">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
                                <div>
                                    <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.15rem;">Log Peringatan Terkini</h5>
                                    <small class="text-gray">Riwayat deteksi kondisi kantuk selama perjalanan ini.</small>
                                </div>
                                <button class="btn btn-sm btn-light text-primary fw-bold px-4 py-2" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                                    Lihat Semua <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #f1f5f9;">
                                            <th class="text-muted fw-bold py-3 border-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">WAKTU</th>
                                            <th class="text-muted fw-bold py-3 border-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">JENIS DETEKSI</th>
                                            <th class="text-muted fw-bold py-3 border-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">NILAI (EAR/MAR)</th>
                                            <th class="text-muted fw-bold py-3 border-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">STATUS</th>
                                            <th class="text-muted fw-bold py-3 border-0 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px;">TINDAKAN</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <!-- Baris Kosong (Jika tidak ada peringatan) -->
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div style="width: 60px; height: 60px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 15px;">
                                                        <i class="fa-regular fa-face-smile-beam"></i>
                                                    </div>
                                                    <h6 class="fw-bold text-dark mb-1 font-heading">Perjalanan Aman Terkendali</h6>
                                                    <small class="text-gray">Belum ada indikasi kantuk atau peringatan yang terdeteksi sejauh ini.</small>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Contoh Baris Data (Hapus komentar di bawah untuk melihat tampilan jika ada data) -->
                                        <!--
                                        <tr style="border-bottom: 1px solid #f8fafc;">
                                            <td class="fw-bold text-dark py-3">11:45:20 WIB</td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div style="width: 36px; height: 36px; background: #ffe4e6; color: #e11d48; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fa-solid fa-eye-low-vision"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark">Mata Tertutup (Micro-sleep)</span>
                                                </div>
                                            </td>
                                            <td class="py-3"><span class="badge bg-white border text-danger fw-bold shadow-sm px-2 py-1">EAR: 0.18</span></td>
                                            <td class="py-3"><span class="badge" style="background: #fecdd3; color: #be123c; border-radius: 8px; padding: 6px 12px;">Bahaya</span></td>
                                            <td class="text-end py-3">
                                                <button class="btn btn-sm btn-light border text-gray fw-bold px-3" style="border-radius: 8px;">Detail</button>
                                            </td>
                                        </tr>
                                        -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- End Container Fluid -->
        </div> <!-- End Page Content Wrapper -->
    </div> <!-- End Wrapper -->

    <!-- Animasi CSS Tambahan Khusus Dashboard -->
    <style>
        @keyframes blinkPulse {
            0% { opacity: 1; transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { opacity: 0.7; transform: scale(1.1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { opacity: 1; transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        
        .shadow-inner {
            box-shadow: inset 0 0 20px rgba(15, 23, 42, 0.8);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* Marker GPS Custom */
        .gps-marker-dashboard {
            position: relative;
            width: 28px;
            height: 28px;
        }
        .gps-marker-dashboard .ping {
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 50%;
            animation: pingDashboard 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        .gps-marker-dashboard .dot {
            width: 28px;
            height: 28px;
            background-color: var(--primary-color);
            border: 4px solid white;
            border-radius: 50%;
            position: absolute;
            box-shadow: 0 4px 10px rgba(67, 56, 202, 0.4);
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gps-marker-dashboard .dot::after {
            content: '';
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
        }
        @keyframes pingDashboard {
            75%, 100% { transform: scale(2.5); opacity: 0; }
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- SCRIPT INTERAKTIVITAS DASHBOARD -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            
            // 1. Toggle Sidebar (Untuk Mode Mobile/Tablet)
            const menuToggle = document.getElementById('menu-toggle');
            const wrapper = document.getElementById('wrapper');
            
            menuToggle.addEventListener('click', function (e) {
                e.preventDefault();
                wrapper.classList.toggle('toggled');
            });

            // Tutup sidebar jika layar mobile dan mengklik area luar sidebar
            document.addEventListener('click', function (e) {
                if (window.innerWidth <= 992) {
                    const sidebar = document.getElementById('sidebar-wrapper');
                    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && wrapper.classList.contains('toggled')) {
                        wrapper.classList.remove('toggled');
                    }
                }
            });

            // 2. Waktu Real-Time
            function updateClock() {
                const now = new Date();
                
                // Format Jam
                const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                document.getElementById('realtime-clock').textContent = timeString;

                // Format Tanggal
                const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
                const dateString = now.toLocaleDateString('id-ID', options);
                document.getElementById('realtime-date').textContent = dateString;
            }
            setInterval(updateClock, 1000);
            updateClock();

            // 3. Simulasi Pergerakan Progress Bar (EAR & MAR) agar terlihat dinamis
            setInterval(() => {
                // Generate random EAR between 0.28 and 0.35
                let newEar = (Math.random() * (0.35 - 0.28) + 0.28).toFixed(2);
                let earWidth = (newEar / 0.40) * 100; // Asumsi 0.40 max normal
                
                // Generate random MAR between 0.10 and 0.18
                let newMar = (Math.random() * (0.18 - 0.10) + 0.10).toFixed(2);
                let marWidth = (newMar / 0.30) * 100; // Asumsi 0.30 max normal

                document.getElementById('ear-value').textContent = newEar;
                document.getElementById('ear-bar').style.width = earWidth + '%';

                document.getElementById('mar-value').textContent = newMar;
                document.getElementById('mar-bar').style.width = marWidth + '%';
            }, 2000);

            // 4. Inisialisasi Peta Leaflet (Posisi GPS)
            var map = L.map('dashboard-map', {
                zoomControl: false,
                attributionControl: false // Menyembunyikan teks atribusi agar lebih bersih di dashboard
            }).setView([-7.1311, 112.7277], 15);

            // Menggunakan tileset OpenStreetMap standard
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            // Kontrol Zoom di pojok kanan bawah agar tidak tertutup overlay status
            L.control.zoom({ position: 'bottomright' }).addTo(map);

            // Icon Custom Marker untuk Dashboard
            var dashboardIcon = L.divIcon({
                className: 'custom-gps-marker',
                html: `
                    <div class="gps-marker-dashboard">
                        <div class="ping"></div>
                        <div class="dot"></div>
                    </div>
                `,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            // Tambahkan Marker utama
            L.marker([-7.1311, 112.7277], { icon: dashboardIcon }).addTo(map);

            // Perbaiki rendering map jika di-load di dalam container flex/grid
            setTimeout(() => {
                map.invalidateSize();
            }, 500);
            
            // Re-render map on window resize
            window.addEventListener('resize', function() {
                map.invalidateSize();
            });
        });
    </script>
</body>

</html>