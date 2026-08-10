<?php
// ==========================================
// 1. BLOK API PHP (HARUS DI BARIS PALING ATAS)
// ==========================================
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_drowsiness';

// Mematikan pesan error PHP agar tidak merusak response AJAX
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$conn = new mysqli($host, $user, $pass, $db);

// Menangkap request untuk EXPORT CSV (FITUR BARU)
if (isset($_GET['action']) && $_GET['action'] == 'export_csv') {
    if (!$conn->connect_error) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Log_Pelanggaran_Skyguard_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Waktu', 'Status', 'Attention Score', 'DSI', 'Yawning'));
        $result = $conn->query("SELECT waktu, status, attention_score, dsi, yawning FROM log_pelanggaran ORDER BY id DESC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
    }
    exit;
}

// Menangkap request untuk MENGAMBIL DATA LOG
if (isset($_GET['action']) && $_GET['action'] == 'get_logs') {
    if ($conn->connect_error) {
        echo "<tr><td colspan='4' class='text-danger py-3'>Koneksi Database Gagal!</td></tr>";
        exit;
    }

    $result = $conn->query("SELECT waktu, status, attention_score, dsi FROM log_pelanggaran ORDER BY id DESC LIMIT 15");
    $html = '';

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stColor = ($row['status'] == 'NORMAL') ? 'text-success' : (($row['status'] == 'DROWSY' || $row['status'] == 'WARNING') ? 'text-warning' : 'text-danger');
            $dsiColor = ($row['dsi'] == 'SAFE') ? 'text-success' : (($row['dsi'] == 'WARNING') ? 'text-warning' : 'text-danger');
            $waktu = date('H:i:s', strtotime($row['waktu']));

            $html .= "<tr>
                <td class='text-secondary' style='font-family: \"Inter\", sans-serif; font-size: 0.85rem;'>{$waktu}</td>
                <td class='fw-bold {$stColor}' style='font-size: 0.85rem;'>{$row['status']}</td>
                <td style='font-size: 0.85rem;'>{$row['attention_score']}%</td>
                <td class='fw-bold {$dsiColor}' style='font-size: 0.85rem;'>{$row['dsi']}</td>
            </tr>";
        }
    } else {
        $html = "<tr><td colspan='4' class='text-muted py-4'>Belum ada data log pelanggaran.</td></tr>";
    }
    echo $html;
    exit; // Wajib exit agar tidak memuat HTML di bawahnya
}

// Menangkap request untuk MENYIMPAN DATA LOG
if (isset($_POST['action']) && $_POST['action'] == 'save_log') {
    if (!$conn->connect_error) {
        $status = $_POST['status'];
        $attention = $_POST['attention'];
        $dsi = $_POST['dsi'];
        $yawning = $_POST['yawning'];

        $stmt = $conn->prepare("INSERT INTO log_pelanggaran (status, attention_score, dsi, yawning) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("siss", $status, $attention, $dsi, $yawning);
            $stmt->execute();
            $stmt->close();
        }
    }
    exit; // Wajib exit
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Drowsiness Dashboard - AWAS 2.0</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bg-body: #fafbfe;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #f1f5f9;
            --primary-blue: #2563eb;
            --light-blue: #eff6ff;
            --danger-red: #ef4444;
            --success-green: #10b981;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            padding: 20px;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.3px;
        }

        /* Container & Cards */
        .ui-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            padding: 24px;
        }

        /* Section Titles */
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .section-title i {
            color: var(--primary-blue);
        }

        /* Video Area */
        .video-wrapper {
            position: relative;
            width: 100%;
            height: 420px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .video-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 2;
        }

        .cam-offline-msg {
            position: absolute;
            z-index: 1;
            text-align: center;
            color: #f8fafc;
        }

        /* Toolbar Buttons under Video */
        .btn-toolbar-custom {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 12px;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.01);
        }

        .btn-toolbar-custom:hover {
            background-color: var(--light-blue);
            color: var(--primary-blue);
            border-color: #bfdbfe;
        }

        .btn-toolbar-custom.active {
            color: var(--primary-blue);
        }

        /* Sliders */
        .setting-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .form-range {
            height: 6px;
            background: var(--border-color);
            border-radius: 10px;
            appearance: none;
        }

        .form-range::-webkit-slider-thumb {
            background: var(--primary-blue);
            width: 16px;
            height: 16px;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(37, 99, 235, 0.4);
            appearance: none;
            margin-top: -5px;
        }

        .form-range::-webkit-slider-runnable-track {
            background-color: transparent;
        }

        /* Custom Switches */
        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.2em;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .switch-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            cursor: pointer;
        }

        /* Theme Toggle Pills */
        .theme-toggle-wrapper {
            background: var(--border-color);
            border-radius: 50rem;
            padding: 4px;
            display: inline-flex;
        }

        .theme-toggle-btn {
            padding: 6px 16px;
            border-radius: 50rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border: none;
            background: transparent;
        }

        .theme-toggle-btn.active {
            background: var(--primary-blue);
            color: white;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        /* Dropdown Custom */
        .form-select-custom {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            padding: 10px 15px;
            background-color: var(--bg-body);
        }

        /* Buttons Bottom */
        .btn-export {
            background-color: var(--primary-blue);
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px;
            border: none;
            width: 100%;
        }

        .btn-export:hover {
            background-color: #1d4ed8;
            color: white;
        }

        .btn-reset {
            background-color: var(--bg-body);
            color: var(--text-muted);
            font-weight: 600;
            border-radius: 10px;
            padding: 12px;
            border: 1px solid var(--border-color);
            width: 100%;
        }

        /* Metrics System Right Side */
        .metric-box {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.01);
        }

        .metric-icon-wrapper {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background-color: var(--light-blue);
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .metric-title-small {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .metric-value-large {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary-blue);
            /* Default color for WAITING */
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .metric-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Attention Progress */
        .progress-attention {
            height: 6px;
            border-radius: 10px;
            background-color: var(--border-color);
            margin-top: 15px;
        }

        /* Table Override */
        .table-log {
            font-size: 0.85rem;
        }

        .table-log th {
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 15px;
        }

        .table-log td {
            padding: 15px 5px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .live-badge {
            background-color: #fef2f2;
            color: var(--danger-red);
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 50rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>

<body>
    <div class="container-fluid max-w-100">
        <div class="row g-4">

            <!-- ========================================== -->
            <!-- KOLOM KIRI (Live Vision & Pengaturan) -->
            <!-- ========================================== -->
            <div class="col-lg-7">

                <!-- Section: Live Vision Monitor -->
                <div class="mb-4">
                    <div class="section-title">
                        <div class="metric-icon-wrapper" style="width: 28px; height: 28px; font-size: 0.8rem;">
                            <i class="fa-solid fa-video"></i>
                        </div>
                        Live Vision Monitor
                        <span id="ui-fps" class="badge bg-secondary ms-auto d-none">Live FPS: 0 | Target: 30</span>
                        <span id="connection-status" class="d-none"></span> <!-- Disembunyikan, logika tetap jalan -->
                    </div>

                    <div class="video-wrapper mb-3" id="videoContainer">
                        <div class="cam-offline-msg" id="cam-offline">
                            <i class="fa-solid fa-video-slash fs-2 mb-3"></i>
                            <h5 class="font-heading fw-bold">Kamera Terputus</h5>
                            <p class="text-white-50 m-0" style="font-size: 0.85rem;">Pastikan modul Computer Vision
                                (app.py) berjalan.</p>
                        </div>
                        <img id="camera-feed" src="http://localhost:5000/video_feed" alt="Live Feed"
                            onerror="this.style.display='none';">
                    </div>

                    <!-- 3 Toolbar Buttons -->
                    <div class="row g-2">
                        <div class="col-4">
                            <button
                                class="btn btn-toolbar-custom w-100 active d-flex justify-content-center align-items-center gap-2">
                                <i class="fa-solid fa-video"></i> Mode Real-time
                            </button>
                        </div>
                        <div class="col-4">
                            <button
                                class="btn btn-toolbar-custom w-100 d-flex justify-content-center align-items-center gap-2">
                                <i class="fa-regular fa-image"></i> Tangkapan Layar
                            </button>
                        </div>
                        <div class="col-4">
                            <!-- Toggle Fullscreen disambungkan ke checkbox lama -->
                            <label
                                class="btn btn-toolbar-custom w-100 d-flex justify-content-center align-items-center gap-2 m-0 cursor-pointer"
                                for="fullscreenCheck">
                                <i class="fa-solid fa-expand"></i> Perluasan Layar
                            </label>
                            <input type="checkbox" id="fullscreenCheck" class="d-none">
                        </div>
                    </div>
                </div>

                <!-- Section: Pengaturan Sistem -->
                <div class="section-title mt-5">
                    <i class="fa-solid fa-gear"></i> Pengaturan Sistem
                </div>

                <div class="ui-card">
                    <div class="row g-4">
                        <!-- Pengaturan Visual -->
                        <div class="col-md-6 border-end" style="border-color: var(--border-color) !important;">
                            <h6 class="fw-bold mb-4 d-flex align-items-center gap-2"
                                style="font-size: 0.9rem; color: var(--primary-blue);">
                                <i class="fa-solid fa-sliders"></i> Pengaturan Visual
                            </h6>

                            <div class="mb-4">
                                <div class="setting-label">
                                    <span>Kecerahan</span>
                                    <span id="valBright" class="text-muted">70%</span>
                                </div>
                                <input type="range" class="form-range w-100" min="0" max="200" value="70"
                                    id="brightnessSlider">
                            </div>

                            <div class="mb-4">
                                <div class="setting-label">
                                    <span>Kontras</span>
                                    <span id="valContrast" class="text-muted">60%</span>
                                </div>
                                <input type="range" class="form-range w-100" min="0" max="200" value="60"
                                    id="contrastSlider">
                            </div>

                            <div class="mb-2">
                                <div class="setting-label">Kualitas Tampilan</div>
                                <select class="form-select form-select-custom w-100" id="fpsSelect">
                                    <option value="15">Rendah (Hemat Daya)</option>
                                    <option value="30" selected>Tinggi (HD)</option>
                                    <option value="60">Ultra (60 FPS)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kontrol & Peringatan -->
                        <div class="col-md-6 ps-md-3">
                            <h6 class="fw-bold mb-4 d-flex align-items-center gap-2"
                                style="font-size: 0.9rem; color: var(--primary-blue);">
                                <i class="fa-solid fa-bolt"></i> Kontrol & Peringatan
                            </h6>

                            <div
                                class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-3">
                                <label class="switch-label d-flex align-items-center gap-2 m-0" for="landmarksCheck">
                                    <i class="fa-regular fa-face-smile text-muted"></i> Tampilkan Analisis Wajah
                                    (Landmarks)
                                </label>
                                <input class="form-check-input m-0" type="checkbox" id="landmarksCheck" checked>
                            </div>

                            <div
                                class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-3">
                                <label class="switch-label d-flex align-items-center gap-2 m-0" for="alarmCheck">
                                    <i class="fa-solid fa-shield-halved text-muted"></i> Aktifkan Alarm Peringatan Suara
                                </label>
                                <input class="form-check-input m-0" type="checkbox" id="alarmCheck" checked>
                            </div>

                            <div
                                class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-4">
                                <label class="switch-label d-flex align-items-center gap-2 m-0" for="pauseFeedCheck">
                                    <i class="fa-solid fa-circle-exclamation text-muted"></i> Jeda Kamera Sementara
                                </label>
                                <input class="form-check-input m-0" type="checkbox" id="pauseFeedCheck">
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3"
                                style="border-top: 1px solid var(--border-color);">
                                <div class="switch-label d-flex align-items-center gap-2">
                                    <i class="fa-regular fa-sun text-muted"></i> Tema Tampilan
                                </div>
                                <div class="theme-toggle-wrapper">
                                    <button class="theme-toggle-btn active">Terang</button>
                                    <button class="theme-toggle-btn">Gelap</button>
                                </div>
                                <!-- Checkbox asli tetap ada untuk menjaga logika JS, disembunyikan -->
                                <input class="form-check-input d-none" type="checkbox" id="lightModeCheck" checked>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4 pt-3 g-3" style="border-top: 1px solid var(--border-color);">
                        <div class="col-md-6">
                            <button class="btn btn-export" id="exportCsvBtn">
                                <i class="fa-solid fa-download me-2"></i> Unduh Log Riwayat (CSV)
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-reset">
                                <i class="fa-solid fa-rotate-right me-2"></i> Reset Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ========================================== -->
            <!-- KOLOM KANAN (Metrik & Log) -->
            <!-- ========================================== -->
            <div class="col-lg-5">

                <!-- Header Metrik Sistem -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title m-0">
                        <i class="fa-solid fa-chart-line" style="color: var(--primary-blue);"></i>
                        Metrik Sistem
                    </div>
                    <a href="#" class="text-decoration-none fw-bold"
                        style="font-size: 0.85rem; color: var(--primary-blue);"></a>
                </div>

                <!-- Top Row: Status & DSI -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="metric-box">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon-wrapper">
                                    <i class="fa-solid fa-user-shield"></i>
                                </div>
                                <span class="metric-title-small">Status Pengemudi</span>
                            </div>
                            <div class="metric-value-large" id="ui-status">WAITING</div>
                            <div class="metric-desc">Menunggu deteksi</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="metric-box">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon-wrapper">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <span class="metric-title-small">Indeks Keamanan (DSI)</span>
                            </div>
                            <div class="metric-value-large" id="ui-dsi">WAITING</div>
                            <div class="metric-desc">Menunggu analisis</div>
                        </div>
                    </div>
                </div>

                <!-- Middle Row: Attention Score -->
                <!-- Kotak ini sekarang sudah fit-to-content dan tidak melar ke bawah -->
                <div class="metric-box mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="metric-icon-wrapper">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <span class="metric-title-small">Attention Score</span>
                    </div>
                    <div class="d-flex align-items-end gap-3 mt-3">
                        <div class="metric-value-large m-0" id="ui-attention-text" style="color: var(--text-main);">0%
                        </div>
                        <div class="metric-desc mb-1 pb-1">Tingkat perhatian pengemudi</div>
                    </div>
                    <div class="progress progress-attention">
                        <div class="progress-bar bg-secondary" id="ui-attention-bar" role="progressbar"
                            style="width: 0%; transition: width 0.4s ease;"></div>
                    </div>
                </div>

                <!-- Bottom Row: EAR & Yawning -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="metric-box">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon-wrapper">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <span class="metric-title-small">EAR (Eye Aspect Ratio)</span>
                            </div>
                            <div class="metric-value-large" id="ui-ear" style="color: var(--text-main);">0.00</div>
                            <div class="metric-desc">Deteksi mata</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="metric-box">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon-wrapper">
                                    <i class="fa-solid fa-face-tired"></i>
                                </div>
                                <span class="metric-title-small">Yawning</span>
                            </div>
                            <div class="metric-value-large" id="ui-yawning" style="color: var(--text-main);">-</div>
                            <div class="metric-desc">Tidak terdeteksi</div>
                        </div>
                    </div>
                </div>

                <!-- Header Log Pelanggaran -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title m-0">
                        <i class="fa-solid fa-clipboard-list" style="color: var(--danger-red);"></i> Log Pelanggaran
                    </div>
                    <div class="live-badge">
                        <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> Live
                    </div>
                </div>

                <!-- Tabel Log -->
                <div class="ui-card p-0 overflow-hidden mb-4">
                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-borderless table-log mb-0 text-center">
                            <thead class="sticky-top" style="background-color: var(--bg-card); z-index: 2;">
                                <tr>
                                    <th>WAKTU</th>
                                    <th>STATUS</th>
                                    <th>ATTN</th>
                                    <th>DSI</th>
                                </tr>
                            </thead>
                            <tbody id="log-table-body">
                                <tr>
                                    <td colspan="4" class="text-muted py-5 text-center">
                                        <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status">
                                        </div>
                                        <br>Memuat log database...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 text-center" style="border-top: 1px solid var(--border-color);">
                        <a href="#" class="text-decoration-none fw-bold"
                            style="font-size: 0.85rem; color: var(--primary-blue);">Lihat Semua Log &rarr;</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SCRIPT JAVASCRIPT (SAMA PERSIS DENGAN SEBELUMNYA) -->
    <script>
        // ==========================================
        // 1. SINKRONISASI TEXT INPUT RANGE (SLIDER)
        // ==========================================
        const valBright = document.getElementById('valBright');
        const valContrast = document.getElementById('valContrast');

        document.getElementById('brightnessSlider').addEventListener('input', (e) => valBright.innerText = e.target.value + '%');
        document.getElementById('contrastSlider').addEventListener('input', (e) => valContrast.innerText = e.target.value + '%');

        // ==========================================
        // 2. ELEMEN DOM & VARIABEL
        // ==========================================
        const uiStatus = document.getElementById('ui-status');
        const uiDsi = document.getElementById('ui-dsi');
        const uiAttentionText = document.getElementById('ui-attention-text');
        const uiAttentionBar = document.getElementById('ui-attention-bar');
        const uiEar = document.getElementById('ui-ear');
        const uiYawning = document.getElementById('ui-yawning');
        const uiFpsMonitor = document.getElementById('ui-fps');
        const connectionStatus = document.getElementById('connection-status');

        const camFeed = document.getElementById('camera-feed');
        const camOffline = document.getElementById('cam-offline');
        const videoContainer = document.getElementById('videoContainer');
        const fpsSelect = document.getElementById('fpsSelect');

        let lastLogTime = 0;
        let isPaused = false;
        let targetFps = fpsSelect.value; // Ambil nilai awal dari dropdown

        // Update Target FPS saat dropdown diubah
        fpsSelect.addEventListener('change', (e) => {
            targetFps = e.target.value;
            if (isPaused || camFeed.style.display === 'none') {
                let statusText = isPaused ? "(PAUSED)" : "";
                if (uiFpsMonitor) uiFpsMonitor.innerText = `Live FPS: 0 ${statusText} | Target: ${targetFps}`;
            }
        });

        // ==========================================
        // 3. LOGIKA FITUR TAMBAHAN
        // ==========================================

        // A. Fitur Light Mode (Tombol Toggle)
        const themeBtns = document.querySelectorAll('.theme-toggle-btn');
        themeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                themeBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // B. Fitur Pause Live Feed
        document.getElementById('pauseFeedCheck').addEventListener('change', (e) => {
            isPaused = e.target.checked;
            if (isPaused) {
                camFeed.style.display = 'none';
                camOffline.style.display = 'block';
                camOffline.innerHTML = '<i class="fa-solid fa-circle-pause fs-1 mb-3 text-warning"></i><h5 class="font-heading fw-bold">Live Feed Dijeda</h5><p class="text-white-50 m-0" style="font-size: 0.85rem;">Matikan fitur Pause untuk melanjutkan.</p>';
            } else {
                camOffline.innerHTML = '<i class="fa-solid fa-video-slash fs-2 mb-3"></i><h5 class="font-heading fw-bold">Kamera Terputus</h5><p class="text-white-50 m-0" style="font-size: 0.85rem;">Pastikan modul Computer Vision (app.py) berjalan.</p>';
            }
        });

        // C. Fitur Fullscreen Video
        document.getElementById('fullscreenCheck').addEventListener('change', (e) => {
            if (e.target.checked) {
                if (videoContainer.requestFullscreen) videoContainer.requestFullscreen();
                else if (videoContainer.webkitRequestFullscreen) videoContainer.webkitRequestFullscreen();
            } else {
                if (document.fullscreenElement) {
                    if (document.exitFullscreen) document.exitFullscreen();
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                }
            }
        });

        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) document.getElementById('fullscreenCheck').checked = false;
        });

        // D. Fitur Export CSV (Menjalankan API PHP)
        document.getElementById('exportCsvBtn').addEventListener('click', () => {
            window.location.href = window.location.pathname + '?action=export_csv';
        });

        // ==========================================
        // 4. LOGIKA UTAMA (KAMERA & METRIK)
        // ==========================================

        function resetMetrics() {
            uiStatus.innerText = "WAITING"; uiStatus.style.color = "var(--primary-blue)";
            uiDsi.innerText = "WAITING"; uiDsi.style.color = "var(--primary-blue)";
            uiAttentionText.innerText = "0%"; uiAttentionText.style.color = "var(--text-main)";
            uiAttentionBar.style.width = "0%"; uiAttentionBar.className = "progress-bar bg-secondary";
            uiEar.innerText = "0.00"; uiEar.style.color = "var(--text-main)";
            uiYawning.innerText = "-"; uiYawning.style.color = "var(--text-main)";

            if (uiFpsMonitor) uiFpsMonitor.innerText = `Live FPS: 0 | Target: ${targetFps}`;
            if (connectionStatus) connectionStatus.innerHTML = '<i class="fa-solid fa-video-slash"></i> Offline';

            camFeed.style.display = 'none';
            camOffline.style.display = 'block';
        }

        function fetchCameraData() {
            if (isPaused) return;

            fetch('http://localhost:5000/data')
                .then(response => {
                    if (!response.ok) throw new Error("Flask Server tidak merespon");
                    return response.json();
                })
                .then(data => {
                    camFeed.style.display = 'block';
                    camOffline.style.display = 'none';

                    if (uiFpsMonitor) uiFpsMonitor.innerText = `Live FPS: ${data.fps} | Target: ${targetFps}`;

                    uiEar.innerText = data.ear.toFixed(2);
                    uiYawning.innerText = data.yawning;
                    uiYawning.style.color = (data.yawning === 'YES') ? 'var(--danger-red)' : 'var(--text-main)';

                    uiStatus.innerText = data.status;
                    if (data.status === 'NORMAL') { uiStatus.style.color = 'var(--success-green)'; }
                    else if (data.status === 'DROWSY') { uiStatus.style.color = '#f59e0b'; }
                    else { uiStatus.style.color = 'var(--danger-red)'; }

                    uiAttentionText.innerText = data.attention + '%';
                    uiAttentionBar.style.width = data.attention + '%';
                    if (data.attention >= 80) {
                        uiAttentionBar.className = 'progress-bar bg-success'; uiAttentionText.style.color = 'var(--success-green)';
                    } else if (data.attention >= 50) {
                        uiAttentionBar.className = 'progress-bar bg-warning'; uiAttentionText.style.color = '#f59e0b';
                    } else {
                        uiAttentionBar.className = 'progress-bar bg-danger'; uiAttentionText.style.color = 'var(--danger-red)';
                    }

                    uiDsi.innerText = data.dsi;
                    if (data.dsi === 'SAFE') { uiDsi.style.color = 'var(--success-green)'; }
                    else if (data.dsi === 'WARNING') { uiDsi.style.color = '#f59e0b'; }
                    else { uiDsi.style.color = 'var(--danger-red)'; }

                    const now = Date.now();
                    if ((data.status !== 'NORMAL' || data.yawning === 'YES') && (now - lastLogTime > 5000)) {
                        saveLogToDatabase(data.status, data.attention, data.dsi, data.yawning);
                        lastLogTime = now;
                    }

                    camFeed.src = 'http://localhost:5000/video_feed?' + new Date().getTime();
                })
                .catch(error => {
                    resetMetrics();
                });
        }

        function saveLogToDatabase(status, attention, dsi, yawning) {
            const formData = new FormData();
            formData.append('action', 'save_log');
            formData.append('status', status);
            formData.append('attention', attention);
            formData.append('dsi', dsi);
            formData.append('yawning', yawning);

            fetch(window.location.pathname, { method: 'POST', body: formData })
                .then(() => refreshLogTable());
        }

        function refreshLogTable() {
            fetch(window.location.pathname + '?action=get_logs')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('log-table-body').innerHTML = html;
                })
                .catch(err => console.log('Gagal memuat tabel:', err));
        }

        // Jalankan Interval
        setInterval(fetchCameraData, 500);
        setInterval(refreshLogTable, 2000);

        refreshLogTable();
    </script>
</body>

</html>