<?php
session_start();

// ==========================================
// KONEKSI DATABASE
// ==========================================
$host = "localhost";
$user = "root";       // Sesuaikan dengan username MySQL kamu
$pass = "";           // Sesuaikan dengan password database kamu
$db = "db_drowsiness"; // Pastikan nama DB sesuai

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ==========================================
// AJAX HANDLER UNTUK REALTIME LOKASI & STATS
// ==========================================
if (isset($_GET['ajax_realtime']) && $_GET['ajax_realtime'] == '1') {
    header('Content-Type: application/json');
    $user_id = $_SESSION['user_id'] ?? 1;

    $q_trip = "SELECT id, start_time FROM trips WHERE driver_id = '$user_id' ORDER BY id DESC LIMIT 1";
    $r_trip = $conn->query($q_trip);

    if ($r_trip && $r_trip->num_rows > 0) {
        $trip = $r_trip->fetch_assoc();
        $trip_id = $trip['id'];

        // Kalkulasi Durasi
        $start_time = strtotime($trip['start_time']);
        $now = time();
        $durasi_detik = max(0, $now - $start_time);
        $jam = floor($durasi_detik / 3600);
        $menit = floor(($durasi_detik / 60) % 60);
        $durasi_str = ($jam > 0 ? $jam . 'j ' : '') . $menit . 'm';
        $sejak_str = date('H:i', $start_time);

        // Ambil Rute GPS, Jarak, & Kecepatan
        $q_gps = "SELECT latitude, longitude, speed FROM gps_tracking WHERE trip_id = '$trip_id' ORDER BY timestamp ASC";
        $r_gps = $conn->query($q_gps);
        $route = [];
        $total_distance = 0;
        $current_speed = 0;

        // Fungsi Haversine untuk kalkulasi jarak GPS yang presisi
        function haversineDistance($latFrom, $lonFrom, $latTo, $lonTo, $earthRadius = 6371)
        {
            $latDelta = deg2rad($latTo - $latFrom);
            $lonDelta = deg2rad($lonTo - $lonFrom);
            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos(deg2rad($latFrom)) * cos(deg2rad($latTo)) * pow(sin($lonDelta / 2), 2)));
            return $angle * $earthRadius;
        }

        $prev_lat = null;
        $prev_lon = null;

        if ($r_gps && $r_gps->num_rows > 0) {
            while ($row = $r_gps->fetch_assoc()) {
                $lat = (float) $row['latitude'];
                $lon = (float) $row['longitude'];
                $route[] = [$lat, $lon];
                $current_speed = $row['speed'];

                if ($prev_lat !== null && $prev_lon !== null) {
                    $total_distance += haversineDistance($prev_lat, $prev_lon, $lat, $lon);
                }
                $prev_lat = $lat;
                $prev_lon = $lon;
            }
        }

        echo json_encode([
            'status' => 'success',
            'route' => $route,
            'current_speed' => $current_speed,
            'distance_km' => number_format($total_distance, 1),
            'duration_str' => $durasi_str,
            'since_str' => $sejak_str
        ]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

// ==========================================
// SIMULASI LOGIN & AMBIL DATA
// ==========================================
// Simulasi user yang login (ID 1)
$_SESSION['user_id'] = 1;
$user_id = $_SESSION['user_id'];

// 1. Ambil Data Profil User
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = $conn->query($query_user);
$data_user = $result_user->fetch_assoc();

$nama_user = $data_user['nama_lengkap'] ?? "Pengguna";
$username = $data_user['username'] ?? "user";
$email_user = $data_user['email'] ?? "-";
$nohp_user = $data_user['no_hp'] ?? "-";

// 2. Ambil Data Kendaraan Aktif
$query_vehicle = "SELECT * FROM vehicles WHERE user_id = '$user_id' AND status = 'active' LIMIT 1";
$result_vehicle = $conn->query($query_vehicle);
$data_vehicle = $result_vehicle->fetch_assoc();

$plat_nomor = $data_vehicle['plat_nomor'] ?? "Belum Ada Plat";
$nama_kendaraan = $data_vehicle['nama_kendaraan'] ?? "Belum Ada Kendaraan";
$vehicle_id = $data_vehicle['id'] ?? 0;

// 3. Ambil Perjalanan (Trip) Terakhir yang Aktif
$query_trip = "SELECT id, start_time FROM trips WHERE driver_id = '$user_id' ORDER BY id DESC LIMIT 1";
$result_trip = $conn->query($query_trip);
$active_trip_id = 0;
if ($result_trip && $result_trip->num_rows > 0) {
    $trip_data = $result_trip->fetch_assoc();
    $active_trip_id = $trip_data['id'];
}

// 4. Ambil Kecepatan Terbaru (GPS)
$current_speed = 0;
if ($active_trip_id > 0) {
    $q_gps = "SELECT speed FROM gps_tracking WHERE trip_id = '$active_trip_id' ORDER BY timestamp DESC LIMIT 1";
    $r_gps = $conn->query($q_gps);
    if ($r_gps && $r_gps->num_rows > 0) {
        $current_speed = $r_gps->fetch_assoc()['speed'];
    }
}

// 5. Total Pelanggaran / Deteksi Kantuk pada Trip Aktif
$total_deteksi = 0;
if ($active_trip_id > 0) {
    $q_deteksi = "SELECT COUNT(id) as total FROM drowsiness_events WHERE trip_id = '$active_trip_id'";
    $r_deteksi = $conn->query($q_deteksi);
    if ($r_deteksi && $r_deteksi->num_rows > 0) {
        $total_deteksi = $r_deteksi->fetch_assoc()['total'];
    }
}

// 6. Ambil Log Pelanggaran Terbaru (Untuk Tabel Dashboard)
$logs = [];
if ($active_trip_id > 0) {
    $q_logs = "SELECT * FROM drowsiness_events WHERE trip_id = '$active_trip_id' ORDER BY timestamp DESC LIMIT 5";
    $r_logs = $conn->query($q_logs);
    if ($r_logs) {
        while ($row = $r_logs->fetch_assoc()) {
            $logs[] = $row;
        }
    }
}

// ==========================================
// STATUS KAMERA (LOGIKA MAIN.PY)
// ==========================================
$is_camera_active = false;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengemudi - DrowsyDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

        /* KEYFRAMES TAMBAHAN UNTUK LIVE KAMERA */
        @keyframes blinkPulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.2;
            }

            100% {
                opacity: 1;
            }
        }

        .transition-hover {
            transition: all 0.3s ease;
        }

        .transition-hover:hover {
            transform: translateX(4px);
            border-color: #cbd5e1 !important;
            box-shadow: var(--shadow-sm);
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
            border-radius: 12px !important;
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

        /* MAIN CONTAINER & CARDS */
        .main-container {
            padding: 20px 32px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
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
            background-color: var(--primary-color) !important;
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

        /* LOG PELANGGARAN */
        .log-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .thumbnail-bukti {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        .thumbnail-container {
            position: relative;
            display: inline-block;
        }

        .play-icon-overlay {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.55rem;
        }

        .table-log th {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-gray);
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
            padding-bottom: 12px;
        }

        .table-log td {
            vertical-align: middle;
            padding: 16px 8px;
            font-size: 0.85rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .btn-detail {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            color: var(--primary-color);
            border: 1px solid rgba(37, 99, 235, 0.2);
            background: transparent;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-detail:hover {
            background: var(--primary-light);
            border-color: var(--primary-color);
        }

        /* PROFIL SAYA */
        .profile-avatar-large {
            width: 85px;
            height: 85px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 700;
            position: relative;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
        }

        .profile-avatar-large .camera-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: white;
            color: var(--primary-color);
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            box-shadow: var(--shadow-sm);
            border: 2px solid white;
            cursor: pointer;
        }

        .profile-info-row {
            display: flex;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .profile-info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .profile-info-icon {
            width: 32px;
            color: #94a3b8;
            font-size: 1rem;
            text-align: left;
        }

        .profile-info-label {
            width: 140px;
            color: #64748b;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .profile-info-value {
            flex-grow: 1;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .activity-summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .activity-summary-card:hover {
            border-color: #cbd5e1;
            box-shadow: var(--shadow-sm);
            transform: translateY(-2px);
        }

        .banner-profil {
            background-image: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&q=80&w=1000');
            background-size: cover;
            background-position: center;
            min-height: 140px;
            position: relative;
            border-radius: 16px;
        }

        .banner-profil::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-radius: 16px;
        }

        .banner-content {
            position: relative;
            z-index: 1;
            padding: 24px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* BANTUAN */
        .accordion-button:not(.collapsed) {
            background-color: var(--primary-light);
            color: var(--primary-color);
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(37, 99, 235, 0.2);
        }

        .accordion-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px !important;
            margin-bottom: 12px;
            overflow: hidden;
            background-color: transparent;
        }

        .accordion-button {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
            padding: 16px 20px;
            background-color: transparent;
        }

        .help-card {
            transition: 0.3s ease;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            height: 100%;
            background-color: transparent;
            display: flex;
            flex-direction: column;
        }

        .help-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
            background-color: #f8fafc;
        }

        .help-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 14px;
        }

        .floating-chat-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            cursor: pointer;
            z-index: 1050;
            transition: 0.3s ease;
        }

        .floating-chat-btn:hover {
            transform: scale(1.08) translateY(-3px);
            background-color: #1d4ed8;
        }

        /* CSS TAMBAHAN UNTUK FITUR RIWAYAT BARU */
        .vehicle-card {
            min-width: 220px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 12px;
            transition: 0.3s;
        }

        .vehicle-card:hover {
            border-color: #cbd5e1;
        }

        .vehicle-card.active {
            border: 2px solid var(--primary-color) !important;
            background: var(--primary-light);
        }

        .vehicle-card-img {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
        }

        .table-riwayat th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-riwayat td {
            vertical-align: middle;
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .badge-tingkat {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .pagination-custom .page-link {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 2px;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div id="wrapper">
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
                <a id="nav-dashboard-btn" class="list-group-item active"><i class="fa-solid fa-house"></i>
                    <span>Dashboard</span></a>
                <a id="nav-live-btn" class="list-group-item"><i class="fa-solid fa-video"></i>
                    <span>Live Monitor</span></a>
                <a id="nav-log-btn" href="#" class="list-group-item"><i class="fa-solid fa-file-shield"></i> <span>Log
                        Pelanggaran</span></a>
                <div class="nav-section-title">DATA & LOG</div>
                <a id="nav-riwayat-btn" href="#" class="list-group-item"><i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Riwayat</span></a>

                <div class="nav-section-title">PENGATURAN</div>
                <a id="nav-profil-btn" href="#" class="list-group-item"><i class="fa-solid fa-user-gear"></i>
                    <span>Profil Saya</span></a>
                <a id="nav-bantuan-btn" href="#" class="list-group-item"><i class="fa-solid fa-circle-question"></i>
                    <span>Bantuan</span></a>
            </div>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center mb-4 px-2 opacity-75">
                    <i class="fa-solid fa-car-side fs-4 text-primary"></i>
                    <div class="ms-3">
                        <small class="d-block fw-bold text-primary font-heading"
                            style="font-size:0.8rem;">DrowsyDrive</small>
                        <small class="text-primary" style="font-size: 0.65rem;">Tetap Fokus,
                            Sampai Tujuan</small>
                    </div>
                </div>
                <a href="login.php" class="btn-logout text-decoration-none shadow-sm">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar Sistem
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-custom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none shadow-sm" id="menu-toggle"
                        style="border-radius: 10px; border: 1px solid #e2e8f0; width: 44px; height: 44px;">
                        <i class="fa-solid fa-bars text-primary"></i>
                    </button>
                    <div>
                        <h4 class="m-0 font-heading fw-bold greeting-title" id="top-title">
                            Selamat Datang,
                            <?php echo htmlspecialchars(explode(' ', trim($nama_user))[0]); ?>! 👋
                        </h4>
                        <small class="text-gray" id="top-subtitle" style="font-size: 0.85rem;">Jika ada pertanyaan atau
                            kendala, kamu bisa menemukan solusinya di sini.</small>
                    </div>
                </div>

                <!-- Bagian Kanan Navbar Sesuai Instruksi (Referensi Gambar) -->
                <div class="d-flex align-items-center gap-3 d-none d-md-flex">
                    <!-- Date Widget -->
                    <div class="date-widget d-flex align-items-center gap-2"
                        style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 20px;">
                        <i class="fa-regular fa-calendar text-primary" style="font-size: 1rem;"></i>
                        <span id="realtime-clock-date" class="text-dark fw-medium" style="font-size: 0.85rem;">Memuat
                            waktu...</span>
                    </div>

                    <!-- Notification -->
                    <button class="notification-btn"
                        style="width: 40px; height: 40px; border-radius: 50%; background: #f8fafc; border: 1px solid #e2e8f0; position: relative; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-regular fa-bell text-dark" style="font-size: 1.1rem;"></i>
                        <span class="notif-badge"
                            style="position: absolute; top: -2px; right: -2px; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 800; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white;">3</span>
                    </button>

                    <!-- Profile Widget -->
                    <div class="d-flex align-items-center gap-2 bg-white px-2 py-1 rounded-pill border"
                        style="border-color: #e2e8f0;">
                        <div class="profile-circle"
                            style="width: 36px; height: 36px; background: #2563eb; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;">
                            <?php echo strtoupper(substr(trim($nama_user), 0, 1)); ?>
                        </div>
                        <div class="text-start d-none d-xl-block pe-3 ps-1 lh-sm">
                            <div class="fw-bold text-dark font-heading" style="font-size: 0.85rem;">
                                <?php echo htmlspecialchars($nama_user); ?>
                            </div>
                            <div class="text-primary" style="font-size: 0.75rem;">Siswa</div>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="main-container">

                <div id="view-dashboard" class="flex-grow-1 flex-column w-100" style="display: flex;">
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
                                    <h4 class="m-0 font-heading fw-bold text-white">
                                        <span style="color: #4ade80; text-shadow: 0 0 10px #4ade80;">●</span> Sedang
                                        Berkendara
                                    </h4>
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
                                                <h5 class="m-0 font-heading fw-bold text-dark">
                                                    <span
                                                        id="val-speed-dash"><?php echo htmlspecialchars($current_speed); ?></span>
                                                    <span class="fw-normal text-muted"
                                                        style="font-size: 0.85rem;">km/jam</span>
                                                </h5>
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
                                                <h5 class="m-0 font-heading fw-bold text-dark"><span
                                                        id="val-jarak">0.0</span> <span class="fw-normal text-muted"
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
                                                <h5 class="m-0 font-heading fw-bold text-dark" id="val-durasi">0j 0m
                                                </h5>
                                            </div>
                                        </div>
                                        <small class="text-gray mt-1 fw-medium"
                                            style="font-size: 0.75rem; padding-left: 62px;" id="val-sejak">Sejak
                                            --:--</small>
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
                                                <h5 class="m-0 font-heading fw-bold text-dark">
                                                    <?php echo htmlspecialchars($total_deteksi); ?> <span
                                                        class="fw-normal text-muted"
                                                        style="font-size: 0.85rem;">kali</span>
                                                </h5>
                                            </div>
                                        </div>
                                        <small class="text-gray mt-1 fw-medium"
                                            style="font-size: 0.75rem; padding-left: 62px;">Berdasarkan riwayat trip
                                            ini</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4 flex-grow-1">
                        <div class="col-xl-8 col-lg-7">
                            <div class="dash-card h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;"><i
                                                class="fa-solid fa-video"></i></div>
                                        <div>
                                            <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.1rem;">
                                                Live Monitoring
                                                Pengemudi</h5>
                                            <small class="text-gray">Pantau
                                                kondisi wajah dan mata secara real-time saat
                                                berkendara.</small>
                                        </div>
                                    </div>
                                    <?php if ($is_camera_active): ?>
                                        <span class="badge rounded-pill px-3 py-2 d-flex align-items-center gap-2"
                                            style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; box-shadow: 0 2px 5px rgba(16,185,129,0.1);">
                                            <div
                                                style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: blinkPulse 1.5s infinite;">
                                            </div> Kamera Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill px-3 py-2 d-flex align-items-center gap-2"
                                            style="background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; box-shadow: 0 2px 5px rgba(239,68,68,0.1);">
                                            <div style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%;">
                                            </div> Kamera Mati
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="row g-4 flex-grow-1">
                                    <div class="col-md-7 d-flex flex-column">

                                        <div class="cv-camera-feed w-100 shadow-sm border border-secondary border-opacity-25 flex-grow-1 <?php echo !$is_camera_active ? 'd-flex justify-content-center align-items-center bg-dark' : ''; ?>"
                                            style="position: relative;">
                                            <?php if ($is_camera_active): ?>
                                                <img src="http://localhost:5000/video_feed" alt="Kamera Live"
                                                    style="position: absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; z-index: 2;">
                                                <div
                                                    style="position: absolute; top: 15%; left: 30%; width: 40%; height: 60%; border: 2px solid #3b82f6; border-radius: 12px; box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); z-index: 3;">
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
                                                    style="top: 0; left: 0; border-radius: 20px; backdrop-filter: blur(4px); z-index: 3;">
                                                    <span style="color: #ef4444; font-size:12px;">●</span> Live
                                                </span>
                                            <?php else: ?>
                                                <div class="text-center p-4 z-1">
                                                    <div class="bg-secondary bg-opacity-25 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                                        style="width: 80px; height: 80px;">
                                                        <i class="fa-solid fa-video-slash text-secondary"
                                                            style="font-size: 2rem;"></i>
                                                    </div>
                                                    <h5 class="text-white fw-bold font-heading">Kamera Tidak Aktif</h5>
                                                    <p class="text-white-50 mb-0" style="font-size: 0.85rem;">Silakan
                                                        jalankan <code class="text-warning">main.py</code> terlebih dahulu.
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-flex gap-4 mt-3 pt-2">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="fw-bold text-gray">EAR (Mata)</small>
                                                    <span class="fw-bold text-dark font-heading"
                                                        id="ear-val">0.00</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">Normal</span>
                                                    <div class="progress flex-grow-1"
                                                        style="height: 6px; border-radius: 10px;">
                                                        <div class="progress-bar bg-primary" id="ear-bar"
                                                            style="width: 0%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="width: 1px; background: #e2e8f0;"></div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="fw-bold text-gray">MAR (Mulut)</small>
                                                    <span class="fw-bold text-dark font-heading"
                                                        id="mar-val">0.00</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">Normal</span>
                                                    <div class="progress flex-grow-1"
                                                        style="height: 6px; border-radius: 10px;">
                                                        <div class="progress-bar bg-info" id="mar-bar"
                                                            style="width: 0%"></div>
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
                                                    mata dalam batas
                                                    normal.</small>
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
                                            <span class="fw-bold text-dark font-heading"
                                                style="font-size: 0.9rem;"><span
                                                    id="val-speed-map-dash"><?php echo htmlspecialchars($current_speed); ?></span>
                                                <span class="fw-normal text-muted"
                                                    style="font-size: 0.7rem;">km/jam</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-0">
                        <div class="col-xl-8 col-lg-7">
                            <div class="dash-card h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;"><i
                                                class="fa-solid fa-file-lines"></i></div>
                                        <div>
                                            <h5 class="fw-bold text-dark m-0 font-heading" style="font-size: 1.1rem;">
                                                Log Pelanggaran
                                                Terdeteksi</h5>
                                            <small class="text-gray">Riwayat
                                                deteksi kantuk dan pelanggaran selama
                                                perjalanan.</small>
                                        </div>
                                    </div>
                                    <button
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold shadow-sm"
                                        style="font-size: 0.8rem; transition: 0.3s;"
                                        onmouseover="this.classList.add('bg-primary', 'text-white')"
                                        onmouseout="this.classList.remove('bg-primary', 'text-white')"
                                        onclick="switchView('log')">
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
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Nilai (DSI)
                                                </th>
                                                <th class="text-muted fw-bold py-3 border-0 text-uppercase"
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Status</th>
                                                <th class="text-muted fw-bold py-3 border-0 text-end text-uppercase"
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Tindakan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                            <?php
                                            // Mapping tipe event ke visual UI
                                            function getEventUI($type)
                                            {
                                                $map = [
                                                    'YAWNING' => ['icon' => 'fa-face-tired', 'color' => 'warning', 'label' => 'Menguap'],
                                                    'MILD_FATIGUE' => ['icon' => 'fa-user', 'color' => 'info', 'label' => 'Kelelahan Ringan'],
                                                    'DROWSY' => ['icon' => 'fa-eye', 'color' => 'danger', 'label' => 'Mengantuk'],
                                                    'SEVERE_DROWSINESS' => ['icon' => 'fa-eye-slash', 'color' => 'danger', 'label' => 'Kantuk Berat'],
                                                    'MICROSLEEP' => ['icon' => 'fa-bed', 'color' => 'danger', 'label' => 'Microsleep']
                                                ];
                                                return $map[$type] ?? ['icon' => 'fa-triangle-exclamation', 'color' => 'secondary', 'label' => $type];
                                            }

                                            // Query 5 Log Terakhir untuk Dashboard
                                            $q_dash_log = "SELECT de.* FROM drowsiness_events de 
                                                   JOIN trips t ON de.trip_id = t.id 
                                                   WHERE t.driver_id = '$user_id' 
                                                   ORDER BY de.timestamp DESC LIMIT 5";
                                            $r_dash_log = $conn->query($q_dash_log);

                                            if ($r_dash_log && $r_dash_log->num_rows > 0):
                                                while ($log = $r_dash_log->fetch_assoc()):
                                                    $ui = getEventUI($log['event_type']);
                                                    $time = date('H:i:s', strtotime($log['timestamp']));
                                                    ?>
                                                    <tr style="transition: 0.2s; cursor: pointer;">
                                                        <td class="text-muted py-3 fw-medium">
                                                            <?php echo $time; ?>
                                                        </td>
                                                        <td class="py-3">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i
                                                                    class="fa-solid <?php echo $ui['icon']; ?> text-<?php echo $ui['color']; ?> bg-<?php echo $ui['color']; ?> bg-opacity-10 p-2 rounded-circle"></i>
                                                                <span class="text-dark fw-bold">
                                                                    <?php echo $ui['label']; ?>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="py-3">
                                                            <span class="text-primary fw-bold">Score:
                                                                <?php echo $log['attention_score']; ?>
                                                            </span>
                                                            <span class="text-muted mx-1">|</span>
                                                            <span class="text-info fw-bold">DSI:
                                                                <?php echo htmlspecialchars($log['dsi_status']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="py-3">
                                                            <span
                                                                class="badge bg-<?php echo $ui['color']; ?> bg-opacity-10 text-<?php echo $ui['color']; ?> border border-<?php echo $ui['color']; ?> border-opacity-25 px-3 py-2 rounded-pill">
                                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Peringatan
                                                            </span>
                                                        </td>
                                                        <td class="text-end py-3">
                                                            <button
                                                                class="btn btn-sm text-primary fw-bold px-3 py-1 bg-primary bg-opacity-10"
                                                                style="border-radius: 8px; transition: 0.3s;"
                                                                onmouseover="this.classList.add('bg-primary', 'text-white')"
                                                                onmouseout="this.classList.remove('bg-primary', 'text-white')">
                                                                <i class="fa-solid fa-eye me-1"></i> Lihat <i
                                                                    class="fa-solid fa-chevron-right ms-1 text-muted"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                endwhile;
                                            else:
                                                ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted fw-medium">
                                                        <i class="fa-solid fa-shield-check fs-4 mb-2 text-success"></i><br>
                                                        Belum ada riwayat pelanggaran hari ini.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
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

                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAMBAHAN FITUR 2: LIVE MONITOR ================= -->
                <div id="view-live-monitor" class="flex-grow-1 flex-column w-100" style="display: none;">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                        </div>
                    </div>

                    <div class="row g-4 flex-grow-1 mb-4">
                        <!-- LEFT COLUMN: Camera & Stats -->
                        <div class="col-xl-8 col-lg-12 d-flex flex-column gap-3">
                            <!-- Camera Row -->
                            <div class="row g-3" style="min-height: 400px;">
                                <!-- Camera Area (dilebarkan penuh setelah panel statistik gelap dihapus) -->
                                <div class="col-12 d-flex flex-column position-relative">
                                    <div class="cv-camera-feed w-100 h-100 flex-grow-1 position-relative shadow-sm"
                                        style="border-radius: 16px; overflow: hidden; background: #000;">
                                        <!-- Offline Placeholder -->
                                        <div id="cam-offline-live"
                                            class="text-center text-white p-3 z-1 position-absolute w-100 h-100 flex-column justify-content-center align-items-center"
                                            style="<?php echo $is_camera_active ? 'display:none;' : 'display:flex;'; ?>">
                                            <i class="fa-solid fa-video-slash mb-3 text-secondary"
                                                style="font-size: 3rem;"></i>
                                            <h5 class="fw-bold font-heading m-0">Kamera Mati</h5>
                                            <small class="text-secondary mt-1">Sistem deteksi belum berjalan. Nyalakan
                                                script main.py</small>
                                        </div>
                                        <!-- Feed Video Stream -->
                                        <img id="cam-feed-live" src="http://localhost:5000/video_feed"
                                            alt="Kamera Simulasi"
                                            style="position: absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; display:<?php echo $is_camera_active ? 'block' : 'none'; ?>; z-index: 2;"
                                            onload="document.getElementById('cam-offline-live').style.display='none'; this.style.display='block';"
                                            onerror="this.style.display='none'; document.getElementById('cam-offline-live').style.display='flex';">

                                        <!-- Top Badges -->
                                        <div class="position-absolute top-0 start-0 m-3 z-3 d-flex justify-content-between w-100"
                                            style="padding-right: 2.5rem;">
                                            <span
                                                class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-2 text-white border border-light border-opacity-25 d-flex align-items-center gap-2 shadow-sm"
                                                style="backdrop-filter: blur(4px);">
                                                <div
                                                    style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%; animation: blinkPulse 1.5s infinite;">
                                                </div> LIVE
                                            </span>
                                            <span
                                                class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-2 text-white border border-light border-opacity-25 d-flex align-items-center gap-2 shadow-sm"
                                                style="backdrop-filter: blur(4px);">
                                                <span id="live-fps">FPS 28</span>
                                            </span>
                                        </div>

                                        <!-- Simulasi Bounding Box -->
                                        <div
                                            style="position: absolute; top: 15%; left: 30%; width: 40%; height: 60%; border: 2px solid #22c55e; box-shadow: 0 0 10px rgba(34, 197, 94, 0.4); display: <?php echo $is_camera_active ? 'block' : 'none'; ?>; z-index: 3;">
                                            <div
                                                style="position: absolute; top: 35%; left: 25%; width: 4px; height: 4px; background: #22c55e; border-radius: 50%;">
                                            </div>
                                            <div
                                                style="position: absolute; top: 35%; right: 25%; width: 4px; height: 4px; background: #22c55e; border-radius: 50%;">
                                            </div>
                                            <div
                                                style="position: absolute; bottom: 30%; left: 40%; width: 20%; height: 2px; background: #22c55e;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ringkasan Kondisi -->
                            <div class="dash-card p-4 flex-grow-1 shadow-sm mt-1" style="border-radius: 16px;">
                                <div class="d-flex align-items-center gap-2 mb-4">
                                    <i class="fa-solid fa-heart-pulse text-primary fs-5"></i>
                                    <h6 class="m-0 font-heading fw-bold text-dark">Ringkasan Kondisi</h6>
                                </div>
                                <div class="row row-cols-2 row-cols-md-5 g-3">
                                    <!-- EAR -->
                                    <div class="col">
                                        <div class="border border-secondary border-opacity-10 rounded-4 p-3 d-flex flex-column justify-content-between h-100 bg-white hover-up"
                                            style="transition:0.3s;"
                                            onmouseover="this.style.transform='translateY(-3px)'"
                                            onmouseout="this.style.transform='translateY(0)'">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <i class="fa-regular fa-eye text-primary"></i>
                                                <small class="text-gray fw-bold" style="font-size: 0.75rem;">EAR
                                                    (Mata)</small>
                                            </div>
                                            <h3 class="font-heading fw-bold text-dark mb-3" id="sum-ear-val">0.00</h3>
                                            <div><span
                                                    class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold"
                                                    style="font-size: 0.7rem;" id="sum-ear-badge"><i
                                                        class="fa-solid fa-circle-check me-1"></i> Normal</span></div>
                                        </div>
                                    </div>
                                    <!-- MAR -->
                                    <div class="col">
                                        <div class="border border-secondary border-opacity-10 rounded-4 p-3 d-flex flex-column justify-content-between h-100 bg-white hover-up"
                                            style="transition:0.3s;"
                                            onmouseover="this.style.transform='translateY(-3px)'"
                                            onmouseout="this.style.transform='translateY(0)'">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <i class="fa-solid fa-face-smile text-primary"></i>
                                                <small class="text-gray fw-bold" style="font-size: 0.75rem;">MAR
                                                    (Mulut)</small>
                                            </div>
                                            <h3 class="font-heading fw-bold text-dark mb-3" id="sum-mar-val">0.00</h3>
                                            <div><span
                                                    class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold"
                                                    style="font-size: 0.7rem;" id="sum-mar-badge"><i
                                                        class="fa-solid fa-circle-check me-1"></i> Normal</span></div>
                                        </div>
                                    </div>
                                    <!-- Attention -->
                                    <div class="col">
                                        <div class="border border-secondary border-opacity-10 rounded-4 p-3 d-flex flex-column justify-content-between h-100 bg-white hover-up"
                                            style="transition:0.3s;"
                                            onmouseover="this.style.transform='translateY(-3px)'"
                                            onmouseout="this.style.transform='translateY(0)'">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <i class="fa-solid fa-eye text-primary"></i>
                                                <small class="text-gray fw-bold" style="font-size: 0.75rem;">Attention
                                                    Score</small>
                                            </div>
                                            <h3 class="font-heading fw-bold text-dark mb-3" id="sum-attn-val">0%</h3>
                                            <div><span
                                                    class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold"
                                                    style="font-size: 0.7rem;" id="sum-attn-badge"><i
                                                        class="fa-solid fa-circle-check me-1"></i> Aman</span></div>
                                        </div>
                                    </div>
                                    <!-- DSI -->
                                    <div class="col">
                                        <div class="border border-secondary border-opacity-10 rounded-4 p-3 d-flex flex-column justify-content-between h-100 bg-white hover-up"
                                            style="transition:0.3s;"
                                            onmouseover="this.style.transform='translateY(-3px)'"
                                            onmouseout="this.style.transform='translateY(0)'">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <i class="fa-solid fa-shield-halved text-primary"></i>
                                                <small class="text-gray fw-bold" style="font-size: 0.75rem;">DSI</small>
                                            </div>
                                            <h3 class="font-heading fw-bold text-dark mb-3" id="sum-dsi-val">Safe</h3>
                                            <div><span
                                                    class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold"
                                                    style="font-size: 0.7rem;" id="sum-dsi-badge"><i
                                                        class="fa-solid fa-circle-check me-1"></i> Aman</span></div>
                                        </div>
                                    </div>
                                    <!-- Yawning -->
                                    <div class="col">
                                        <div class="border border-secondary border-opacity-10 rounded-4 p-3 d-flex flex-column justify-content-between h-100 bg-white hover-up"
                                            style="transition:0.3s;"
                                            onmouseover="this.style.transform='translateY(-3px)'"
                                            onmouseout="this.style.transform='translateY(0)'">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <i class="fa-solid fa-face-tired text-primary"></i>
                                                <small class="text-gray fw-bold"
                                                    style="font-size: 0.75rem;">Yawning</small>
                                            </div>
                                            <h3 class="font-heading fw-bold text-dark mb-3" id="sum-yawn-val">No</h3>
                                            <div><span
                                                    class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold"
                                                    style="font-size: 0.7rem;" id="sum-yawn-badge">Tidak
                                                    Terdeteksi</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Vehicle, Map, Info -->
                        <div class="col-xl-4 col-lg-12 d-flex flex-column gap-3">
                            <!-- Vehicle Info Card -->
                            <div class="dash-card border border-secondary border-opacity-10 p-4 shadow-sm"
                                style="border-radius: 16px;">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-car-side text-primary"></i>
                                        <h6 class="m-0 font-heading fw-bold text-dark">Informasi Kendaraan</h6>
                                    </div>
                                    <a href="#" class="text-primary fw-bold text-decoration-none"
                                        style="font-size: 0.75rem;">Lihat Detail</a>
                                </div>
                                <div class="row g-3 align-items-center">
                                    <div class="col-5">
                                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center p-2"
                                            style="height: 100px;">
                                            <img src="assets/img/<?php echo $data_vehicle['foto_kendaraan'] ?? 'default_car.png'; ?>"
                                                alt="Kendaraan"
                                                style="max-width: 100%; max-height: 100%; object-fit: contain; mix-blend-mode: multiply;"
                                                onerror="this.src='https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=300'">
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex flex-column gap-2">
                                        <div>
                                            <h6 class="font-heading fw-bold text-dark m-0 d-inline">
                                                <?php echo htmlspecialchars($nama_kendaraan); ?>
                                            </h6>
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill ms-1"
                                                style="font-size: 0.6rem;"><i class="fa-solid fa-car-side me-1"></i>
                                                Mobil</span>
                                        </div>
                                        <div>
                                            <small class="text-gray d-block" style="font-size: 0.65rem;">Nomor
                                                Plat</small>
                                            <strong class="text-dark"
                                                style="font-size: 0.8rem;"><?php echo htmlspecialchars($plat_nomor); ?></strong>
                                        </div>
                                        <div>
                                            <small class="text-gray d-block" style="font-size: 0.65rem;">Tipe
                                                Kendaraan</small>
                                            <strong class="text-dark"
                                                style="font-size: 0.8rem;"><?php echo htmlspecialchars($data_vehicle['jenis_kendaraan'] ?? 'Toyota Avanza 1.5 G (M/T)'); ?></strong>
                                        </div>
                                        <div>
                                            <small class="text-gray d-block" style="font-size: 0.65rem;">Warna</small>
                                            <strong class="text-dark"
                                                style="font-size: 0.8rem;"><?php echo htmlspecialchars($data_vehicle['warna'] ?? 'Putih'); ?></strong>
                                        </div>
                                        <div>
                                            <small class="text-gray d-block" style="font-size: 0.65rem;">Tahun</small>
                                            <strong class="text-dark" style="font-size: 0.8rem;">2020</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location & Map Card -->
                            <div class="dash-card border border-secondary border-opacity-10 p-4 shadow-sm"
                                style="border-radius: 16px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center"
                                            style="width: 28px; height: 28px;">
                                            <i class="fa-solid fa-location-dot" style="font-size: 0.75rem;"></i>
                                        </div>
                                        <h6 class="m-0 font-heading fw-bold text-dark">Lokasi & Rute</h6>
                                    </div>
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1"
                                        style="font-size: 0.65rem;">
                                        <i class="fa-solid fa-location-dot me-1"></i> GPS Aktif
                                    </span>
                                </div>
                                <div class="position-relative w-100 rounded-4 overflow-hidden border border-secondary border-opacity-10 shadow-sm"
                                    style="height: 180px;">
                                    <div id="live-map"
                                        style="width: 100%; height: 100%; position: absolute; top:0; left:0; z-index: 1;">
                                    </div>
                                    <!-- Floating over map -->
                                    <div class="position-absolute bg-white px-3 py-2 shadow-sm rounded-4 d-flex align-items-center gap-2"
                                        style="top: 10px; left: 10px; z-index: 400;">
                                        <div class="text-primary bg-primary bg-opacity-10 p-2 rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 28px; height: 28px;">
                                            <i class="fa-solid fa-gauge-high" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <div>
                                            <small class="d-block text-gray fw-bold"
                                                style="font-size: 0.6rem;">Kecepatan</small>
                                            <strong class="text-dark font-heading" style="font-size: 0.85rem;"><span
                                                    id="val-speed-live-map"><?php echo htmlspecialchars($current_speed); ?></span>
                                                <small class="text-muted fw-normal">km/jam</small></strong>
                                        </div>
                                    </div>
                                    <button
                                        class="btn btn-sm btn-white text-primary border shadow-sm position-absolute fw-bold"
                                        style="top: 10px; right: 10px; z-index: 400; border-radius: 20px; font-size: 0.7rem; padding: 4px 12px; background: white;">Lihat
                                        Lokasi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAMBAHAN FITUR 3: LOG PELANGGARAN ================= -->
                <!-- ================= TAMBAHAN FITUR 3: LOG PELANGGARAN ================= -->
                <div id="view-log-pelanggaran" class="flex-grow-1 flex-column w-100" style="display: none;">

                    <?php
                    // Hitung Statistik Pelanggaran Keseluruhan User Ini
                    $q_stats = "SELECT event_type, COUNT(de.id) as total_event 
                        FROM drowsiness_events de 
                        JOIN trips t ON de.trip_id = t.id 
                        WHERE t.driver_id = '$user_id' 
                        GROUP BY event_type";
                    $r_stats = $conn->query($q_stats);

                    $stat_total = 0;
                    $stat_yawning = 0;
                    $stat_drowsy = 0; // Gabungan Drowsy, Severe, Microsleep
                    $stat_fatigue = 0;

                    if ($r_stats) {
                        while ($row_stat = $r_stats->fetch_assoc()) {
                            $stat_total += $row_stat['total_event'];
                            if ($row_stat['event_type'] == 'YAWNING') {
                                $stat_yawning += $row_stat['total_event'];
                            } elseif ($row_stat['event_type'] == 'MILD_FATIGUE') {
                                $stat_fatigue += $row_stat['total_event'];
                            } else {
                                $stat_drowsy += $row_stat['total_event'];
                            }
                        }
                    }
                    ?>

                    <!-- Header -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"
                            style="width: 48px; height: 48px; font-size: 1.4rem;">
                            <i class="fa-solid fa-file-invoice"></i>
                        </div>
                        <div>
                            <h4 class="m-0 font-heading fw-bold text-dark">Log Pelanggaran</h4>
                            <small class="text-gray">Berikut adalah riwayat pelanggaran yang terdeteksi oleh sistem
                                selama perjalanan Anda.</small>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div
                                class="dash-card p-3 border border-secondary border-opacity-10 d-flex gap-3 align-items-center h-100">
                                <div class="log-card-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <small class="text-dark fw-bold d-block mb-1" style="font-size: 0.8rem;">Total
                                        Pelanggaran</small>
                                    <div class="d-flex align-items-end gap-2">
                                        <h3 class="m-0 font-heading fw-bold">
                                            <?php echo $stat_total; ?>
                                        </h3>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.65rem;">Riwayat Keseluruhan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div
                                class="dash-card p-3 border border-secondary border-opacity-10 d-flex gap-3 align-items-center h-100">
                                <div class="log-card-icon text-purple"
                                    style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                                    <i class="fa-solid fa-face-tired"></i>
                                </div>
                                <div>
                                    <small class="text-dark fw-bold d-block mb-1" style="font-size: 0.8rem;">Menguap
                                        Berlebih</small>
                                    <div class="d-flex align-items-end gap-2">
                                        <h3 class="m-0 font-heading fw-bold">
                                            <?php echo $stat_yawning; ?>
                                        </h3>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.65rem;">Riwayat Keseluruhan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="dash-card p-3 border border-warning border-opacity-25 d-flex gap-3 align-items-center h-100"
                                style="background: #fffdfa;">
                                <div class="log-card-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <small class="text-dark fw-bold d-block mb-1" style="font-size: 0.8rem;">Kelelahan
                                        Ringan</small>
                                    <div class="d-flex align-items-end gap-2">
                                        <h3 class="m-0 font-heading fw-bold">
                                            <?php echo $stat_fatigue; ?>
                                        </h3>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.65rem;">Riwayat Keseluruhan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="dash-card p-3 border border-danger border-opacity-10 d-flex gap-3 align-items-center h-100"
                                style="background: #fffcfc;">
                                <div class="log-card-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </div>
                                <div>
                                    <small class="text-dark fw-bold d-block mb-1" style="font-size: 0.8rem;">Mata
                                        Tertutup (Kantuk)</small>
                                    <div class="d-flex align-items-end gap-2">
                                        <h3 class="m-0 font-heading fw-bold text-dark">
                                            <?php echo $stat_drowsy; ?>
                                        </h3>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.65rem;">Riwayat Keseluruhan</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Log -->
                    <div class="row g-4 flex-grow-1 pb-4">
                        <!-- Table Section -->
                        <div class="col-xl-8 col-lg-8 d-flex flex-column gap-4">
                            <div class="dash-card border border-secondary border-opacity-10 p-4 flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="m-0 font-heading fw-bold text-dark">Daftar Log Pelanggaran</h5>
                                    <button
                                        class="btn btn-sm btn-light border d-flex align-items-center gap-2 fw-medium text-dark shadow-sm px-3 py-2"
                                        style="border-radius: 8px;">
                                        <i class="fa-solid fa-arrow-down-short-wide text-gray"></i> Terbaru <i
                                            class="fa-solid fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-borderless table-log mb-4">
                                        <thead>
                                            <tr>
                                                <th>WAKTU</th>
                                                <th>JENIS PELANGGARAN</th>
                                                <th>TINGKAT</th>
                                                <th>KENDARAAN</th>
                                                <th class="text-center">AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Ambil semua log pelanggaran dengan JOIN ke tabel kendaraan
                                            $q_all_logs = "SELECT de.*, v.nama_kendaraan, v.plat_nomor 
                                                   FROM drowsiness_events de 
                                                   JOIN trips t ON de.trip_id = t.id 
                                                   JOIN vehicles v ON t.vehicle_id = v.id 
                                                   WHERE t.driver_id = '$user_id' 
                                                   ORDER BY de.timestamp DESC LIMIT 10";
                                            $r_all_logs = $conn->query($q_all_logs);

                                            if ($r_all_logs && $r_all_logs->num_rows > 0):
                                                while ($log_item = $r_all_logs->fetch_assoc()):
                                                    $ui = getEventUI($log_item['event_type']);
                                                    $waktu = date('H:i:s', strtotime($log_item['timestamp']));
                                                    $tgl = date('d M Y', strtotime($log_item['timestamp']));

                                                    // Menentukan tingkat berdasarkan attention score atau jenis
                                                    $tingkat = 'Sedang';
                                                    $badge_color = 'warning';
                                                    if (in_array($log_item['event_type'], ['SEVERE_DROWSINESS', 'MICROSLEEP'])) {
                                                        $tingkat = 'Berat';
                                                        $badge_color = 'danger';
                                                    }
                                                    ?>
                                                    <tr style="transition: 0.2s; cursor: pointer;">
                                                        <td>
                                                            <strong class="text-dark d-block">
                                                                <?php echo $waktu; ?>
                                                            </strong>
                                                            <small class="text-gray" style="font-size: 0.7rem;">
                                                                <?php echo $tgl; ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="text-<?php echo $ui['color']; ?> bg-<?php echo $ui['color']; ?> bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center"
                                                                    style="width: 28px; height: 28px;">
                                                                    <i class="fa-solid <?php echo $ui['icon']; ?>"
                                                                        style="font-size: 0.75rem;"></i>
                                                                </div>
                                                                <div>
                                                                    <strong class="text-dark d-block"
                                                                        style="font-size: 0.85rem;">
                                                                        <?php echo $ui['label']; ?>
                                                                    </strong>
                                                                    <small class="text-gray" style="font-size: 0.7rem;">Skor
                                                                        Perhatian:
                                                                        <?php echo $log_item['attention_score']; ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge bg-<?php echo $badge_color; ?> bg-opacity-10 text-<?php echo $badge_color; ?> border border-<?php echo $badge_color; ?> border-opacity-25 rounded-pill px-2 py-1">
                                                                <i class="fa-solid fa-triangle-exclamation me-1"
                                                                    style="font-size: 0.5rem;"></i>
                                                                <?php echo $tingkat; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <strong class="text-dark d-block" style="font-size: 0.8rem;">
                                                                <?php echo htmlspecialchars($log_item['nama_kendaraan']); ?>
                                                            </strong>
                                                            <small class="text-gray" style="font-size: 0.7rem;">
                                                                <?php echo htmlspecialchars($log_item['plat_nomor']); ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-center">
                                                            <button class="btn-detail"><i class="fa-solid fa-eye"></i> Lihat
                                                                Detail <i
                                                                    class="fa-solid fa-chevron-right ms-1 text-muted"></i></button>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                endwhile;
                                            else:
                                                ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">
                                                        Tidak ada data log pelanggaran.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Placeholder -->
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                    <small class="text-gray fw-medium">Menampilkan halaman 1</small>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm pagination-custom m-0">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" tabindex="-1"><i
                                                        class="fa-solid fa-chevron-left"
                                                        style="font-size: 0.7rem;"></i></a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item">
                                                <a class="page-link" href="#"><i class="fa-solid fa-chevron-right"
                                                        style="font-size: 0.7rem;"></i></a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Filter & Tips -->
                        <div class="col-xl-4 col-lg-4 d-flex flex-column gap-4">
                            <!-- Filter Card -->
                            <div class="dash-card border border-secondary border-opacity-10 p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center gap-2 text-dark">
                                        <i class="fa-solid fa-filter text-primary"></i>
                                        <h6 class="m-0 font-heading fw-bold">Filter Data</h6>
                                    </div>
                                    <a href="#" class="text-primary text-decoration-none fw-bold"
                                        style="font-size: 0.75rem;">Reset</a>
                                </div>

                                <form>
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold"
                                            style="font-size: 0.8rem;">Tanggal</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white text-gray border-end-0"><i
                                                    class="fa-regular fa-calendar"></i></span>
                                            <input type="text"
                                                class="form-control border-start-0 ps-0 text-dark fw-medium"
                                                value="<?php echo date('d M Y'); ?>   —   <?php echo date('d M Y'); ?>"
                                                readonly style="font-size: 0.8rem; background: #fff;">
                                            <span class="input-group-text bg-white text-gray"><i
                                                    class="fa-solid fa-chevron-down"
                                                    style="font-size: 0.7rem;"></i></span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold" style="font-size: 0.8rem;">Jenis
                                            Pelanggaran</label>
                                        <select class="form-select form-select-sm fw-medium text-dark">
                                            <option selected>Semua Jenis</option>
                                            <option>Menguap</option>
                                            <option>Mata Tertutup</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold"
                                            style="font-size: 0.8rem;">Tingkat</label>
                                        <select class="form-select form-select-sm fw-medium text-dark">
                                            <option selected>Semua Tingkat</option>
                                            <option>Ringan</option>
                                            <option>Sedang</option>
                                            <option>Berat</option>
                                        </select>
                                    </div>
                                    <button type="button"
                                        class="btn btn-primary w-100 rounded-3 shadow-sm fw-bold py-2 d-flex justify-content-center align-items-center gap-2">
                                        <i class="fa-solid fa-magnifying-glass"></i> Terapkan Filter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAMBAHAN FITUR 4: RIWAYAT ================= -->
                <div id="view-riwayat" class="flex-grow-1 flex-column w-100" style="display: none;">

                    <!-- Header -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"
                            style="width: 50px; height: 50px; font-size: 1.4rem;">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <h4 class="m-0 font-heading fw-bold text-dark">Riwayat Deteksi</h4>
                            <small class="text-gray">Berisi seluruh riwayat deteksi kantuk yang terdeteksi pada
                                kendaraan yang Anda gunakan.</small>
                        </div>
                    </div>

                    <!-- Panel Kendaraan -->
                    <div class="dash-card h-auto border border-secondary border-opacity-10 p-4 mb-3 d-flex flex-column gap-3"
                        style="height: auto !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                            <button
                                class="btn btn-sm btn-white border fw-bold text-primary d-flex align-items-center gap-2 px-3 py-2 shadow-sm"
                                style="border-radius: 8px;">
                                <i class="fa-solid fa-car-side"></i> Semua Kendaraan <i
                                    class="fa-solid fa-chevron-down ms-2" style="font-size: 0.7rem;"></i>
                            </button>
                            <button
                                class="btn btn-sm text-primary fw-bold bg-primary bg-opacity-10 px-3 py-2 transition-hover"
                                style="border-radius: 8px;">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Kendaraan
                            </button>
                        </div>

                        <!-- Horizontal Scroll Cards (Dari Database) -->
                        <div class="d-flex gap-3 overflow-auto pb-2 w-100" style="scrollbar-width: thin;">
                            <?php
                            // Ambil Data Semua Kendaraan User Ini
                            $q_my_vehicles = "SELECT * FROM vehicles WHERE user_id = '$user_id'";
                            $r_my_vehicles = $conn->query($q_my_vehicles);
                            if ($r_my_vehicles && $r_my_vehicles->num_rows > 0):
                                $is_first = true;
                                while ($veh = $r_my_vehicles->fetch_assoc()):
                                    $bg_class = $is_first ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary border-opacity-25 bg-white shadow-sm';
                                    ?>
                                    <div class="border <?php echo $bg_class; ?> rounded-3 p-3 d-flex align-items-center gap-3 position-relative transition-hover"
                                        style="min-width: 240px; cursor:pointer;">
                                        <?php if ($is_first): ?>
                                            <i class="fa-solid fa-circle-check text-primary position-absolute"
                                                style="top: -6px; right: -6px; font-size: 1.2rem; background:#fff; border-radius:50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></i>
                                        <?php endif; ?>
                                        <img src="assets/img/<?php echo $veh['foto_kendaraan']; ?>"
                                            alt="<?php echo htmlspecialchars($veh['nama_kendaraan']); ?>"
                                            onerror="this.src='https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=150'"
                                            style="width: 65px; height: 45px; object-fit: cover; border-radius: 6px; <?php echo $is_first ? 'mix-blend-mode: multiply;' : ''; ?>">
                                        <div>
                                            <strong class="d-block text-dark font-heading" style="font-size: 0.85rem;">
                                                <?php echo htmlspecialchars($veh['nama_kendaraan']); ?>
                                            </strong>
                                            <small class="text-<?php echo $is_first ? 'primary' : 'gray'; ?> fw-bold"
                                                style="font-size: 0.75rem;">
                                                <?php echo htmlspecialchars($veh['plat_nomor']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php
                                    $is_first = false;
                                endwhile;
                            else:
                                ?>
                                <small class="text-muted p-2">Belum ada kendaraan terdaftar.</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <div class="input-group input-group-sm bg-white border rounded-pill shadow-sm overflow-hidden"
                            style="width: 260px; padding: 4px 8px;">
                            <span class="input-group-text bg-white border-0 text-gray"><i
                                    class="fa-regular fa-calendar"></i></span>
                            <input type="text" class="form-control border-0 ps-0 text-dark fw-medium"
                                value="<?php echo date('d M Y'); ?>   -   <?php echo date('d M Y'); ?>" readonly
                                style="font-size: 0.75rem; background: #fff; box-shadow:none;">
                            <span class="input-group-text bg-white border-0 text-gray"><i
                                    class="fa-solid fa-chevron-down" style="font-size:0.7rem;"></i></span>
                        </div>

                        <div class="input-group input-group-sm bg-white border rounded-pill shadow-sm overflow-hidden ms-auto"
                            style="width: 280px; padding: 4px 8px;">
                            <span class="input-group-text bg-white border-0 text-gray"><i
                                    class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control border-0 ps-1 text-dark"
                                placeholder="Cari nama kendaraan, jenis deteksi..."
                                style="font-size: 0.75rem; background: #fff; box-shadow:none;">
                        </div>
                    </div>

                    <!-- Main Table Data -->
                    <div
                        class="dash-card border border-secondary border-opacity-10 p-0 flex-grow-1 d-flex flex-column bg-white overflow-hidden mb-4">
                        <div class="p-4 border-bottom bg-white">
                            <h5 class="m-0 font-heading fw-bold text-dark">Daftar Riwayat Deteksi</h5>
                        </div>

                        <div class="table-responsive flex-grow-1">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr class="text-gray"
                                        style="background: #f8fafc; font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <th class="py-3 px-4 border-0 fw-bold">NO</th>
                                        <th class="py-3 px-2 border-0 fw-bold">WAKTU <i
                                                class="fa-solid fa-caret-down ms-1"></i></th>
                                        <th class="py-3 px-2 border-0 fw-bold">KENDARAAN</th>
                                        <th class="py-3 px-2 border-0 fw-bold">JENIS KENDARAAN</th>
                                        <th class="py-3 px-2 border-0 fw-bold">JENIS DETEKSI</th>
                                        <th class="py-3 px-2 border-0 fw-bold">TINGKAT</th>
                                        <th class="py-3 px-2 border-0 fw-bold">STATUS</th>
                                        <th class="py-3 px-4 border-0 fw-bold text-center">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Ambil Riwayat Keseluruhan dengan JOIN
                                    $q_riwayat = "SELECT de.*, v.nama_kendaraan, v.plat_nomor, v.jenis_kendaraan 
                                          FROM drowsiness_events de 
                                          JOIN trips t ON de.trip_id = t.id 
                                          JOIN vehicles v ON t.vehicle_id = v.id 
                                          WHERE t.driver_id = '$user_id' 
                                          ORDER BY de.timestamp DESC LIMIT 15";
                                    $r_riwayat = $conn->query($q_riwayat);
                                    $no = 1;

                                    if ($r_riwayat && $r_riwayat->num_rows > 0):
                                        while ($riw = $r_riwayat->fetch_assoc()):
                                            $ui = getEventUI($riw['event_type']);
                                            $jam = date('H:i:s', strtotime($riw['timestamp']));
                                            $tgl = date('d M Y', strtotime($riw['timestamp']));

                                            // Tentukan Icon Kendaraan
                                            $veh_icon = ($riw['jenis_kendaraan'] == 'Motor') ? 'fa-motorcycle' : 'fa-car-side';

                                            $tingkat = 'Sedang';
                                            $badge_color = 'warning';
                                            if (in_array($riw['event_type'], ['SEVERE_DROWSINESS', 'MICROSLEEP'])) {
                                                $tingkat = 'Berat';
                                                $badge_color = 'danger';
                                            }
                                            ?>
                                            <tr class="border-bottom border-light">
                                                <td class="px-4 text-gray fw-medium">
                                                    <?php echo $no++; ?>
                                                </td>
                                                <td class="px-2">
                                                    <span class="text-primary fw-medium d-block">
                                                        <?php echo $tgl; ?>
                                                    </span>
                                                    <span class="text-gray" style="font-size: 0.75rem;">
                                                        <?php echo $jam; ?>
                                                    </span>
                                                </td>
                                                <td class="px-2">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-light rounded p-2 text-secondary">
                                                            <i class="fa-solid <?php echo $veh_icon; ?>"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block" style="font-size: 0.8rem;">
                                                                <?php echo htmlspecialchars($riw['nama_kendaraan']); ?>
                                                            </strong>
                                                            <small class="text-primary fw-bold" style="font-size: 0.7rem;">
                                                                <?php echo htmlspecialchars($riw['plat_nomor']); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-2">
                                                    <div class="d-flex align-items-center gap-2 text-primary fw-medium">
                                                        <?php echo htmlspecialchars($riw['jenis_kendaraan']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-2 py-3">
                                                    <div class="d-flex align-items-start gap-2">
                                                        <div class="bg-<?php echo $ui['color']; ?> bg-opacity-10 text-<?php echo $ui['color']; ?> rounded-circle mt-1 d-flex align-items-center justify-content-center"
                                                            style="width: 24px; height: 24px; flex-shrink:0;">
                                                            <i class="fa-solid <?php echo $ui['icon']; ?>"
                                                                style="font-size: 0.65rem;"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block">
                                                                <?php echo $ui['label']; ?>
                                                            </strong>
                                                            <small class="text-gray" style="font-size: 0.7rem;">DSI:
                                                                <?php echo htmlspecialchars($riw['dsi_status']); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-2">
                                                    <span
                                                        class="badge bg-<?php echo $badge_color; ?> bg-opacity-10 text-<?php echo $badge_color; ?> border border-<?php echo $badge_color; ?> border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"
                                                            style="font-size:0.6rem;"></i>
                                                        <?php echo $tingkat; ?>
                                                    </span>
                                                </td>
                                                <td class="px-2">
                                                    <span
                                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                                        <i class="fa-solid fa-location-crosshairs me-1"
                                                            style="font-size:0.6rem;"></i> Terdeteksi
                                                    </span>
                                                </td>
                                                <td class="px-4 text-center">
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold"
                                                        style="font-size: 0.75rem;">
                                                        <i class="fa-regular fa-eye me-1"></i> Lihat Detail <i
                                                            class="fa-solid fa-chevron-right ms-1"
                                                            style="font-size:0.6rem;"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat deteksi.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-white mt-auto">
                            <small class="text-gray fw-medium">Menampilkan halaman 1</small>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm pagination-custom m-0 gap-1">
                                    <li class="page-item disabled">
                                        <a class="page-link border-0 bg-light text-muted rounded-circle d-flex align-items-center justify-content-center"
                                            href="#" tabindex="-1" style="width: 32px; height: 32px;"><i
                                                class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i></a>
                                    </li>
                                    <li class="page-item active">
                                        class="page-link border-0 rounded-circle d-flex align-items-center
                                        justify-content-center fw-bold"
                                        href="#"
                                        style="width: 32px; height: 32px; background: #2563eb; color:white;">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link border-0 bg-light text-dark rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                            href="#" style="width: 32px; height: 32px;"><i
                                                class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- ================= TAMBAHAN FITUR 5: PROFIL SAYA ================= -->
                <div id="view-profil" class="flex-grow-1 flex-column w-100" style="display: none;">
                    <div class="row g-4 flex-grow-1 mb-4">
                        <!-- KOLOM KIRI -->
                        <div class="col-xl-6 col-lg-6 d-flex flex-column gap-4">
                            <div class="dash-card border border-secondary border-opacity-10 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <h6 class="m-0 font-heading fw-bold text-dark">Informasi Profil</h6>
                                    <button class="btn btn-sm fw-bold px-3 py-1 text-primary bg-primary bg-opacity-10"
                                        style="border-radius: 8px; font-size: 0.75rem; border: 1px solid rgba(37,99,235,0.1);"><i
                                            class="fa-solid fa-pen me-1"></i> Ubah Profil</button>
                                </div>
                                <div class="d-flex align-items-center gap-4 mb-3">
                                    <div class="profile-avatar-large shadow-sm border border-white border-2">
                                        <?php
                                        $initials = '';
                                        $name_parts = explode(' ', trim($nama_user));
                                        if (isset($name_parts[0]))
                                            $initials .= substr($name_parts[0], 0, 1);
                                        if (isset($name_parts[1]))
                                            $initials .= substr($name_parts[1], 0, 1);
                                        echo strtoupper(htmlspecialchars($initials));
                                        ?>
                                        <div class="camera-badge"><i class="fa-solid fa-camera"></i></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="m-0 font-heading fw-bold text-dark mb-1"
                                            style="letter-spacing: -0.5px;">
                                            <?php echo htmlspecialchars($nama_user); ?>
                                        </h4>
                                        <small class="text-gray fw-medium d-block mb-3">Pengguna
                                            (
                                            <?php echo ucfirst(htmlspecialchars($data_user['role'] ?? 'Driver')); ?>)
                                        </small>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2"><i
                                                    class="fa-regular fa-envelope text-primary opacity-75"
                                                    style="width: 20px;"></i><small class="text-dark fw-medium"
                                                    style="font-size: 0.85rem;">
                                                    <?php echo htmlspecialchars($email_user); ?>
                                                </small>
                                            </div>
                                            <div class="d-flex align-items-center gap-2"><i
                                                    class="fa-solid fa-phone text-primary opacity-75"
                                                    style="width: 20px;"></i><small class="text-dark fw-medium"
                                                    style="font-size: 0.85rem;">
                                                    <?php echo htmlspecialchars($nohp_user); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dash-card border border-secondary border-opacity-10 p-4 flex-grow-1">
                                <h6 class="m-0 font-heading fw-bold text-dark mb-4">Detail Akun</h6>
                                <div class="d-flex flex-column">
                                    <div class="profile-info-row">
                                        <div class="profile-info-icon"><i class="fa-solid fa-at"></i></div>
                                        <div class="profile-info-label">Username</div>
                                        <div class="profile-info-value">
                                            <?php echo htmlspecialchars($username); ?>
                                        </div>
                                        <div class="profile-info-action"><i class="fa-solid fa-pen"></i></div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-icon"><i class="fa-solid fa-lock"></i></div>
                                        <div class="profile-info-label">Password</div>
                                        <div class="profile-info-value" style="letter-spacing: 2px;">••••••••</div>
                                        <div class="profile-info-action d-flex gap-3"><i
                                                class="fa-regular fa-eye-slash"></i><i class="fa-solid fa-pen"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="col-xl-6 col-lg-6 d-flex flex-column gap-4">
                            <div class="dash-card border border-secondary border-opacity-10 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <h6 class="m-0 font-heading fw-bold text-dark">Data Kendaraan Terpilih</h6>
                                    <button class="btn btn-sm fw-bold px-3 py-1 text-primary bg-primary bg-opacity-10"
                                        style="border-radius: 8px; font-size: 0.75rem; border: 1px solid rgba(37,99,235,0.1);"><i
                                            class="fa-solid fa-pen me-1"></i> Ubah Kendaraan</button>
                                </div>
                                <div class="d-flex gap-4 align-items-center flex-wrap">
                                    <div
                                        style="width: 180px; height: 120px; background: #f1f5f9; border-radius: 12px; padding: 10px; display: flex; align-items: center; justify-content: center;">
                                        <img src="assets/img/<?php echo $data_vehicle['foto_kendaraan'] ?? 'default_car.png'; ?>"
                                            onerror="this.src='https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=300'"
                                            alt="Kendaraan"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; mix-blend-mode: multiply;">
                                    </div>
                                    <div class="flex-grow-1 d-flex flex-column gap-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="fa-solid fa-car text-primary opacity-75 mt-1"></i>
                                            <div>
                                                <small class="text-gray d-block mb-1"
                                                    style="font-size: 0.7rem;">Kendaraan</small>
                                                <strong class="text-dark font-heading d-block"
                                                    style="font-size: 0.95rem;">
                                                    <?php echo htmlspecialchars($nama_kendaraan); ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="fa-solid fa-id-card text-primary opacity-75 mt-1"></i>
                                            <div>
                                                <small class="text-gray d-block mb-1" style="font-size: 0.7rem;">Nomor
                                                    Plat</small>
                                                <strong class="text-dark font-heading d-block"
                                                    style="font-size: 0.95rem;">
                                                    <?php echo htmlspecialchars($plat_nomor); ?>
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="banner-profil mt-auto overflow-hidden border border-secondary border-opacity-10 shadow-sm flex-grow-1">
                                <div class="banner-content">
                                    <i class="fa-solid fa-quote-left text-primary opacity-50 fs-4 mb-2"></i>
                                    <h5 class="text-dark font-heading fw-bold mb-1" style="letter-spacing: -0.3px;">
                                        Tetap fokus di jalan,</h5>
                                    <h5 class="text-dark font-heading fw-bold mb-3" style="letter-spacing: -0.3px;">
                                        karena <span class="text-primary">keluarga</span> menunggu di rumah.</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAMBAHAN FITUR 6: BANTUAN ================= -->
                <div id="view-bantuan" class="flex-grow-1 flex-column w-100" style="display: none;">
                    <div class="row g-4 flex-grow-1 mb-4">
                        <div class="col-xl-7 col-lg-12 d-flex flex-column gap-4">
                            <div class="dash-card border border-secondary border-opacity-10 p-4 h-100">
                                <h5 class="m-0 font-heading fw-bold text-dark mb-4">Pertanyaan yang Sering Diajukan</h5>
                                <div class="accordion" id="accordionFAQ">
                                    <div class="accordion-item shadow-sm">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button collapsed d-flex align-items-center gap-3"
                                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                aria-expanded="false" aria-controls="collapseOne">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 28px; height: 28px; flex-shrink: 0;"><strong
                                                        style="font-size: 0.8rem;">Q</strong></div> Bagaimana cara
                                                memulai deteksi kantuk?
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse collapse"
                                            aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
                                            <div class="accordion-body text-gray"
                                                style="font-size: 0.85rem; line-height: 1.6; padding-left: 60px;">Sistem
                                                akan otomatis memulai deteksi saat Anda menekan tab 'Live Monitor' dan
                                                mengizinkan akses kamera, serta skrip main.py sudah berjalan.</div>
                                        </div>
                                    </div>
                                    <div class="accordion-item shadow-sm">
                                        <h2 class="accordion-header" id="headingTwo">
                                            <button class="accordion-button collapsed d-flex align-items-center gap-3"
                                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                aria-expanded="false" aria-controls="collapseTwo">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 28px; height: 28px; flex-shrink: 0;"><strong
                                                        style="font-size: 0.8rem;">Q</strong></div> Mengapa kamera
                                                bertuliskan 'Kamera Tidak Aktif'?
                                            </button>
                                        </h2>
                                        <div id="collapseTwo" class="accordion-collapse collapse"
                                            aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
                                            <div class="accordion-body text-gray"
                                                style="font-size: 0.85rem; line-height: 1.6; padding-left: 60px;">Itu
                                                berarti server Python (Flask) yang bertugas menangkap frame kamera dan
                                                memproses AI belum aktif. Pastikan Anda telah menjalankan perintah
                                                <code>python main.py</code> di terminal.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5 col-lg-12 d-flex flex-column gap-4">
                            <div class="dash-card border border-secondary border-opacity-10 p-4 h-100 flex-grow-1">
                                <h6 class="m-0 font-heading fw-bold text-dark mb-2">Hubungi Admin Sistem</h6>
                                <p class="text-gray mb-4" style="font-size: 0.8rem; line-height: 1.5;">Kirim laporan
                                    kendala teknis atau pertanyaan melalui form berikut.</p>
                                <form>
                                    <div class="input-group mb-3 shadow-sm border rounded" style="overflow: hidden;">
                                        <span class="input-group-text bg-white border-0 text-gray pe-1"><i
                                                class="fa-regular fa-user"></i></span>
                                        <input type="text" class="form-control border-0 ps-2 text-dark fw-medium"
                                            placeholder="Nama Anda" value="<?php echo htmlspecialchars($nama_user); ?>"
                                            readonly style="box-shadow: none; font-size: 0.85rem; padding: 10px;">
                                    </div>
                                    <div class="input-group mb-4 shadow-sm border rounded" style="overflow: hidden;">
                                        <span
                                            class="input-group-text bg-white border-0 text-gray pe-1 align-items-start pt-3"><i
                                                class="fa-solid fa-pen-clip"></i></span>
                                        <textarea class="form-control border-0 ps-2 text-dark pt-3 fw-medium"
                                            placeholder="Jelaskan kendala Anda..." rows="4"
                                            style="box-shadow: none; font-size: 0.85rem; resize: none;"></textarea>
                                    </div>
                                    <button type="button"
                                        class="btn btn-primary w-100 rounded-3 shadow-sm fw-bold py-2 d-flex justify-content-center align-items-center gap-2">
                                        <i class="fa-regular fa-paper-plane"></i> Kirim Laporan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- Floating Chat Button -->
    <div class="floating-chat-btn">
        <i class="fa-regular fa-comment-dots"></i>
    </div>
    <!-- ================= MODAL PENGATURAN SISTEM ================= -->
    <div class="modal fade" id="modalPengaturanSistem" tabindex="-1" aria-labelledby="modalPengaturanSistemLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-gear fs-5"></i>
                        </div>
                        <h5 class="modal-title font-heading fw-bold text-dark m-0" id="modalPengaturanSistemLabel">
                            Pengaturan Sistem</h5>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 mt-2">
                    <div class="row g-4">
                        <div class="col-md-6 border-end pe-4">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-solid fa-shield-halved text-primary opacity-75"></i>
                                <strong class="text-dark" style="font-size: 0.85rem;">Deteksi & Peringatan</strong>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-gray fw-medium">Sensitivitas Deteksi EAR</small>
                                    <small class="text-dark fw-bold">0.25 Threshold</small>
                                </div>
                                <input type="range" class="form-range custom-range" min="0.20" max="0.30" step="0.01"
                                    value="0.25">
                            </div>
                        </div>
                        <div class="col-md-6 ps-4 d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-solid fa-video text-primary opacity-75"></i>
                                <strong class="text-dark" style="font-size: 0.85rem;">Kontrol Perangkat</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <small class="text-gray fw-medium">Integrasi Kamera AI</small>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input bg-primary" type="checkbox" checked
                                        style="border:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal"
                        style="border-radius: 8px;">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold px-4 d-flex align-items-center gap-2"
                        style="border-radius: 8px;" data-bs-dismiss="modal">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- PHP CHART LOGIC -->
    <?php
    $chart_labels = [];
    $chart_data = [];
    if ($active_trip_id > 0) {
        $q_chart = "SELECT DATE_FORMAT(timestamp, '%H:00') as jam, COUNT(id) as total 
                FROM drowsiness_events 
                WHERE trip_id = '$active_trip_id' 
                GROUP BY HOUR(timestamp) 
                ORDER BY timestamp ASC LIMIT 6";
        $r_chart = $conn->query($q_chart);
        if ($r_chart && $r_chart->num_rows > 0) {
            while ($c = $r_chart->fetch_assoc()) {
                $chart_labels[] = $c['jam'];
                $chart_data[] = $c['total'];
            }
        }
    }
    // Jika tidak ada data, gunakan format default statis untuk display UI
    if (empty($chart_labels)) {
        $chart_labels = [date('H:00', strtotime('-3 hours')), date('H:00', strtotime('-2 hours')), date('H:00', strtotime('-1 hours')), date('H:00')];
        $chart_data = [0, 0, 0, 0];
    }
    ?>
    <!-- SCRIPT JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Export fungsi switchView agar dapat diakses oleh tombol "Lihat Semua"
        function switchView(target) {
            const views = {
                dashboard: document.getElementById('view-dashboard'),
                live: document.getElementById('view-live-monitor'),
                log: document.getElementById('view-log-pelanggaran'),
                riwayat: document.getElementById('view-riwayat'),
                profil: document.getElementById('view-profil'),
                bantuan: document.getElementById('view-bantuan')
            };

            const btns = {
                dashboard: document.getElementById('nav-dashboard-btn'),
                live: document.getElementById('nav-live-btn'),
                log: document.getElementById('nav-log-btn'),
                riwayat: document.getElementById('nav-riwayat-btn'),
                profil: document.getElementById('nav-profil-btn'),
                bantuan: document.getElementById('nav-bantuan-btn')
            };

            Object.values(views).forEach(v => { if (v) v.style.display = 'none'; });
            Object.values(btns).forEach(b => { if (b) b.classList.remove('active'); });

            if (views[target]) views[target].style.display = 'flex';
            if (btns[target]) btns[target].classList.add('active');

            // Trigger map resize if moving to map views
            if (target === 'dashboard' && typeof dashboardMap !== 'undefined') {
                setTimeout(() => dashboardMap.invalidateSize(), 100);
            }
            if (target === 'live' && typeof liveMap !== 'undefined') {
                setTimeout(() => liveMap.invalidateSize(), 100);
            }
        }

        document.addEventListener("DOMContentLoaded", function () {

            // --- 1. Sidebar Toggle Mobile ---
            const menuToggle = document.getElementById('menu-toggle');
            const wrapper = document.getElementById('wrapper');
            if (menuToggle) {
                menuToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    wrapper.classList.toggle('toggled');
                });
            }

            // --- 2. Inisialisasi Tombol Navigasi ---
            const btnKeys = ['dashboard', 'live', 'log', 'riwayat', 'profil', 'bantuan'];
            btnKeys.forEach(key => {
                let btn = document.getElementById('nav-' + key + '-btn');
                if (btn) {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        switchView(key);
                    });
                }
            });

            // Set default view on load
            switchView('live');

            // --- 3. Real-Time Clock ---
            function updateClock() {
                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' });

                let rtClockDate = document.getElementById('realtime-clock-date');
                if (rtClockDate) rtClockDate.textContent = dateStr + ' • ' + timeStr;
            }
            setInterval(updateClock, 1000);
            updateClock();

            // --- 4. Konfigurasi Maps (Leaflet) ---
            var driverIcon = L.divIcon({
                className: 'custom-gps-marker',
                html: `<div class="ping" style="width:100%;height:100%;background:#3b82f6;border-radius:50%;opacity:0.4;animation:blinkPulse 1.5s infinite;"></div><div class="dot" style="position:absolute;top:25%;left:25%;width:50%;height:50%;background:#1d4ed8;border-radius:50%;border:2px solid white;"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            var latlngs = [[-7.935, 112.580], [-7.940, 112.595], [-7.948, 112.605], [-7.953, 112.613]];

            window.liveMap = L.map('live-map', { zoomControl: true, attributionControl: false, zoomSnap: 0.1 }).setView([-7.953, 112.613], 13.5);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(window.liveMap);
            window.liveMarker = L.marker([-7.953, 112.613], { icon: driverIcon }).addTo(window.liveMap);
            window.livePolyline = L.polyline(latlngs, { color: '#3b82f6', weight: 5, opacity: 0.9 }).addTo(window.liveMap);

            window.addEventListener('resize', () => {
                if (window.liveMap) window.liveMap.invalidateSize();
            });
            setTimeout(() => { if (window.liveMap) window.liveMap.invalidateSize(); }, 500);

            // --- 4b. Peta Dashboard (Kartu "Lokasi & Rute Perjalanan") ---
            window.dashboardMap = L.map('dashboard-map', { zoomControl: true, attributionControl: false, zoomSnap: 0.1 }).setView([-7.953, 112.613], 13.5);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(window.dashboardMap);
            window.dashboardMarker = L.marker([-7.953, 112.613], { icon: driverIcon }).addTo(window.dashboardMap);
            window.dashboardPolyline = L.polyline(latlngs, { color: '#3b82f6', weight: 5, opacity: 0.9 }).addTo(window.dashboardMap);

            window.addEventListener('resize', () => {
                if (window.dashboardMap) window.dashboardMap.invalidateSize();
            });
            setTimeout(() => { if (window.dashboardMap) window.dashboardMap.invalidateSize(); }, 500);

            // --- 5. LOGIKA AJAX REALTIME (LOKASI, KECEPATAN, DURASI, JARAK) ---
            function fetchRealtimeData() {
                fetch('?ajax_realtime=1')
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (document.getElementById('val-speed-live-map')) document.getElementById('val-speed-live-map').innerText = data.current_speed;

                            if (data.route && data.route.length > 0) {
                                let latestCoord = data.route[data.route.length - 1];

                                if (window.liveMarker) window.liveMarker.setLatLng(latestCoord);
                                if (window.livePolyline) window.livePolyline.setLatLngs(data.route);
                                if (window.liveMap) window.liveMap.panTo(latestCoord);
                            }
                        }
                    })
                    .catch(err => console.error("Gagal mengambil data realtime:", err));
            }
            setInterval(fetchRealtimeData, 3000);
            fetchRealtimeData();

            // --- 7. LOGIKA AJAX AI REALTIME DARI FLASK UNTUK UI KAMERA/DETEKSI ---
            function fetchAIData() {
                fetch('http://localhost:5000/data')
                    .then(response => response.json())
                    .then(data => {
                        // FPS
                        if (document.getElementById('live-fps')) document.getElementById('live-fps').innerText = "FPS " + (data.fps || "28");

                        // Update EAR
                        let earVal = parseFloat(data.ear).toFixed(2);
                        if (document.getElementById('live-ear-val')) document.getElementById('live-ear-val').innerText = earVal;
                        if (document.getElementById('live-ear-val-right')) document.getElementById('live-ear-val-right').innerText = earVal;

                        // Update MAR
                        let marVal = parseFloat(data.mar).toFixed(2);
                        if (document.getElementById('live-mar-val')) document.getElementById('live-mar-val').innerText = marVal;
                        if (document.getElementById('live-mar-val-right')) document.getElementById('live-mar-val-right').innerText = marVal;

                        // Update Attention Score
                        if (document.getElementById('live-attention-val')) document.getElementById('live-attention-val').innerText = data.attention + "%";
                        if (document.getElementById('live-attention-val-right')) document.getElementById('live-attention-val-right').innerText = data.attention + "%";

                        if (document.getElementById('live-attention-bar-right')) {
                            document.getElementById('live-attention-bar-right').style.width = data.attention + "%";
                            let barColorRight = data.attention > 70 ? 'bg-success' : (data.attention > 40 ? 'bg-warning' : 'bg-danger');
                            document.getElementById('live-attention-bar-right').className = 'progress-bar ' + barColorRight;
                        }

                        // Right Status (Yawning)
                        if (document.getElementById('live-yawning-status-right')) {
                            let yawnRight = document.getElementById('live-yawning-status-right');
                            let yawnIcon = yawnRight.parentElement.parentElement.querySelector('.fa-circle-check, .fa-triangle-exclamation');
                            if (data.status === 'YAWNING' || data.mar > 0.6) {
                                yawnRight.innerText = "Yes";
                                yawnRight.className = "text-warning font-heading";
                                if (yawnIcon) yawnIcon.className = "fa-solid fa-triangle-exclamation text-warning fs-5";
                            } else {
                                yawnRight.innerText = "No";
                                yawnRight.className = "text-white font-heading";
                                if (yawnIcon) yawnIcon.className = "fa-solid fa-circle-check text-success fs-5";
                            }
                        }

                        // Driver Status & DSI logic
                        if (document.getElementById('live-driver-status-right')) {
                            let driverRight = document.getElementById('live-driver-status-right');
                            let dsiRight = document.getElementById('live-dsi-status-right');

                            let driverIcon = driverRight.parentElement.parentElement.querySelector('.fa-circle-check, .fa-triangle-exclamation');
                            let dsiIcon = dsiRight.parentElement.parentElement.querySelector('.fa-circle-check, .fa-triangle-exclamation');

                            if (data.status === 'NORMAL') {
                                driverRight.innerText = "Normal";
                                driverRight.className = "text-success font-heading fs-6";
                                if (driverIcon) driverIcon.className = "fa-solid fa-circle-check text-success fs-6";

                                dsiRight.innerText = "Safe";
                                dsiRight.className = "text-white font-heading fs-6";
                                if (dsiIcon) dsiIcon.className = "fa-solid fa-circle-check text-success fs-6";
                            } else {
                                driverRight.innerText = "Warning";
                                driverRight.className = "text-danger font-heading fs-6";
                                if (driverIcon) driverIcon.className = "fa-solid fa-triangle-exclamation text-danger fs-6";

                                dsiRight.innerText = data.status;
                                dsiRight.className = "text-danger font-heading fs-6";
                                if (dsiIcon) dsiIcon.className = "fa-solid fa-triangle-exclamation text-danger fs-6";
                            }
                        }
                    })
                    .catch(error => {
                        // JIKA FLASK MATI/OFFLINE, BUAT SEMUA JADI 0
                        if (document.getElementById('live-ear-val')) document.getElementById('live-ear-val').innerText = "0.00";
                        if (document.getElementById('live-mar-val')) document.getElementById('live-mar-val').innerText = "0.00";
                        if (document.getElementById('live-ear-val-right')) document.getElementById('live-ear-val-right').innerText = "0.00";
                        if (document.getElementById('live-mar-val-right')) document.getElementById('live-mar-val-right').innerText = "0.00";

                        // Reset Attention Score menjadi 0% karena sistem mati
                        if (document.getElementById('live-attention-val')) document.getElementById('live-attention-val').innerText = "0%";
                        if (document.getElementById('live-attention-val-right')) document.getElementById('live-attention-val-right').innerText = "0%";
                        if (document.getElementById('live-attention-bar-right')) document.getElementById('live-attention-bar-right').style.width = "0%";
                    });
            }

            setInterval(fetchAIData, 1000);
            fetchAIData();
        });
    </script>
</body>

</html>