<?php
session_start();

// Cek apakah user sudah login, jika belum kembalikan ke index.php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AWAS 2.0 Safety System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-main: #0f172a;
            --bg-panel: #1e293b;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-blue: #3b82f6;
            --safe-green: #10b981;
            --caution-yellow: #f59e0b;
            --warning-orange: #f97316;
            --critical-red: #ef4444;
            --sidebar-width: 260px;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            font-family: 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
        }

        /* -------------------------
           SIDEBAR STYLING
           ------------------------- */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--bg-panel);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 24px;
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            letter-spacing: 1px;
        }

        .sidebar-menu {
            padding: 20px 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .menu-title {
            padding: 10px 24px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        .nav-link {
            color: var(--text-muted);
            padding: 12px 24px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 10px;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--text-main);
            background-color: rgba(59, 130, 246, 0.1);
            border-left-color: var(--accent-blue);
        }

        /* -------------------------
           TOP NAVBAR STYLING
           ------------------------- */
        .top-navbar {
            margin-left: var(--sidebar-width);
            height: 70px;
            background-color: var(--bg-main);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--accent-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }

        /* -------------------------
           MAIN CONTENT
           ------------------------- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: calc(100vh - 70px);
        }

        /* Custom Scrollbar untuk sidebar */
        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-shield-halved text-primary me-3 fs-3"></i>
            AWAS 2.0
        </div>

        <div class="sidebar-menu">
            <div class="menu-title">Main Dashboard</div>
            <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-border-all"></i> Overview</a>

            <div class="menu-title mt-3">Intelligence Core</div>
            <a href="#" class="nav-link"><i class="fa-solid fa-video"></i> Live Monitoring</a>
            <a href="#" class="nav-link"><i class="fa-solid fa-map-location-dot"></i> Live Map (GPS)</a>
            <a href="#" class="nav-link"><i class="fa-solid fa-route"></i> My Trips</a>

            <div class="menu-title mt-3">Vehicle & Safety</div>
            <a href="#" class="nav-link"><i class="fa-solid fa-car"></i> Vehicles</a>
            <a href="#" class="nav-link"><i class="fa-solid fa-chart-line"></i> Safety Analytics</a>

            <div class="menu-title mt-3">Emergency Response</div>
            <a href="#" class="nav-link text-danger"><i class="fa-solid fa-truck-medical"></i> Emergency Center</a>
            <a href="#" class="nav-link"><i class="fa-regular fa-bell"></i> Notifications</a>

            <div class="menu-title mt-3">Settings</div>
            <a href="#" class="nav-link"><i class="fa-regular fa-id-card"></i> Driver Profile</a>
            <a href="logout.php" class="nav-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <!-- TOP NAVBAR -->
    <header class="top-navbar">
        <div class="d-flex align-items-center">
            <h5 class="m-0 fw-bold">Dashboard Overview</h5>
            <span class="badge bg-primary bg-opacity-25 text-primary ms-3 px-3 py-2 rounded-pill shadow-sm"
                style="font-size: 0.75rem;">
                <i class="fa-solid fa-circle-check me-1"></i> System Online
            </span>
        </div>

        <div class="d-flex align-items-center gap-4">
            <!-- Tombol Darurat Cepat -->
            <button class="btn btn-danger btn-sm px-3 fw-bold shadow-sm" style="border-radius: 8px;">
                <i class="fa-solid fa-phone-volume me-1"></i> SOS
            </button>

            <div class="position-relative" style="cursor: pointer;">
                <i class="fa-regular fa-bell fs-5 text-muted"></i>
                <span
                    class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </div>

            <div class="user-profile">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold" style="font-size: 0.9rem;">
                        <?php echo htmlspecialchars($nama_user); ?>
                    </div>
                    <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                        <?php echo htmlspecialchars($role_user); ?>
                    </div>
                </div>
                <div class="avatar">
                    <?php echo strtoupper(substr($nama_user, 0, 1)); ?>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT (Akan diisi di Part 3) -->
    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- ROW 1: TOP METRICS -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-25 text-primary"><i class="fa-solid fa-shield-cat"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                            Safety Score</div>
                        <h3 class="m-0 fw-bold" id="ui-safety-score">100<span
                                style="font-size: 1rem; color: var(--text-muted);">/100</span></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-25 text-success" id="icon-status"><i
                            class="fa-solid fa-face-smile"></i></div>
                    <div>
                        <div class="text-muted" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                            Fatigue Level</div>
                        <h4 class="m-0 fw-bold text-success" id="ui-fatigue-level">AWAKE</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-25 text-info"><i class="fa-solid fa-stopwatch"></i></div>
                    <div>
                        <div class="text-muted" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                            Driving Time</div>
                        <h4 class="m-0 fw-bold">01:24:31</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-color: var(--accent-blue);">
                    <div class="stat-icon bg-primary text-white"><i class="fa-solid fa-mug-hot"></i></div>
                    <div>
                        <div class="text-muted" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                            Next Rest Area</div>
                        <h5 class="m-0 fw-bold">Rest Area KM 26</h5>
                        <small class="text-primary fw-bold">Est. 15 mins (12.4 km)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2: AI CAMERA & DRIVER IDENTITY -->
        <div class="row g-4 mb-4">

            <!-- Left: Live AI Monitor -->
            <div class="col-lg-8">
                <div class="panel-card h-100 mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-video text-primary me-2"></i> Live AI Monitoring
                        </h6>
                        <span class="badge bg-danger bg-opacity-25 text-danger px-3 py-1 rounded-pill"><i
                                class="fa-solid fa-circle fa-beat me-1"></i> REC</span>
                    </div>

                    <div class="video-wrapper">
                        <img id="camera-feed" src="http://localhost:5000/video_feed" alt="Camera Feed Offline"
                            onerror="this.src='https://via.placeholder.com/800x420/000000/FFFFFF/?text=Camera+Offline+or+Paused'">
                    </div>

                    <div class="d-flex justify-content-between mt-3 px-2">
                        <span class="text-muted fw-bold" style="font-size: 0.85rem;" id="ui-fps">FPS: 30</span>
                        <span class="text-muted fw-bold" style="font-size: 0.85rem;" id="ui-ear">EAR: 0.00 | Yawning:
                            NO</span>
                    </div>
                </div>
            </div>

            <!-- Right: Identity & Prediction -->
            <div class="col-lg-4">
                <div class="panel-card h-100 mb-0">
                    <h6 class="fw-bold mb-4"><i class="fa-regular fa-id-card text-primary me-2"></i> Active Trip Detail
                    </h6>

                    <div class="text-center mb-4">
                        <div class="avatar mx-auto mb-2" style="width: 70px; height: 70px; font-size: 1.5rem;">
                            <?php echo strtoupper(substr($nama_user, 0, 1)); ?>
                        </div>
                        <h6 class="fw-bold m-0">
                            <?php echo htmlspecialchars($nama_user); ?>
                        </h6>
                        <span class="badge bg-secondary bg-opacity-25 text-light mt-1">ID: DRV-8821</span>
                    </div>

                    <ul class="driver-info-list mb-4">
                        <li><span class="text-muted">Vehicle</span> <span class="fw-bold">Toyota Avanza</span></li>
                        <li><span class="text-muted">Plate Number</span> <span
                                class="fw-bold bg-dark px-2 rounded border border-secondary">M 1234 XX</span></li>
                        <li><span class="text-muted">Route</span> <span class="fw-bold text-info">Bangkalan <i
                                    class="fa-solid fa-arrow-right mx-1"></i> Surabaya</span></li>
                        <li><span class="text-muted">Distance</span> <span class="fw-bold">72.4 km</span></li>
                    </ul>

                    <!-- Predictive AI Risk -->
                    <h6 class="fw-bold mb-2" style="font-size: 0.85rem; text-transform: uppercase;">Fatigue Risk
                        Prediction</h6>
                    <div class="progress bg-dark mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" id="risk-bar" role="progressbar" style="width: 10%;"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-success fw-bold" id="risk-text">LOW RISK</small>
                        <small class="text-muted">AI Forecast</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 3: GPS TRACKING & LOGS -->
        <div class="row g-4">

            <!-- Left: Live Map -->
            <div class="col-lg-8">
                <div class="panel-card h-100 mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-compass text-primary me-2"></i> Live Route
                            Tracking</h6>
                        <span class="fw-bold" style="font-size: 0.9rem;">Speed: <span class="text-info">64
                                km/h</span></span>
                    </div>
                    <!-- Peta Visual (Placeholder) -->
                    <div class="map-placeholder d-flex align-items-center justify-content-center">
                        <div class="bg-dark bg-opacity-75 p-3 rounded text-center border border-secondary"
                            style="backdrop-filter: blur(5px);">
                            <i class="fa-solid fa-location-dot text-danger fs-2 mb-2"></i>
                            <h6 class="text-white m-0">Live Vehicle Marker</h6>
                            <small class="text-muted">Currently in Bangkalan, Jawa Timur</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Event Center -->
            <div class="col-lg-4">
                <div class="panel-card h-100 mb-0">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i> Safety
                        Event Log</h6>

                    <div style="height: 250px; overflow-y: auto;" id="event-log-container">
                        <!-- Ini akan diisi oleh JavaScript AJAX nanti (Part PHP API) -->
                        <div class="d-flex align-items-start mb-3 border-bottom border-secondary pb-2">
                            <div class="bg-warning bg-opacity-25 text-warning p-2 rounded me-3"><i
                                    class="fa-solid fa-eye-lowered"></i></div>
                            <div>
                                <h6 class="m-0" style="font-size: 0.9rem;">Drowsiness Detected</h6>
                                <small class="text-muted">Bangkalan Area • 08:31 AM</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3 border-bottom border-secondary pb-2">
                            <div class="bg-info bg-opacity-25 text-info p-2 rounded me-3"><i
                                    class="fa-solid fa-route"></i></div>
                            <div>
                                <h6 class="m-0" style="font-size: 0.9rem;">Trip Started</h6>
                                <small class="text-muted">Trunojoyo University • 07:10 AM</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>