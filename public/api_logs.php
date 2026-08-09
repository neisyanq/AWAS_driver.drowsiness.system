<?php
session_start();
error_reporting(0); // Matikan error agar tidak merusak response JSON/AJAX

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_drowsiness';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    exit;
}

// ---------------------------------------------------------
// POST: MENYIMPAN LOG DARI KAMERA PYTHON (AJAX)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_log') {
    $status = $_POST['status']; // NORMAL, DROWSY, MICRO-SLEEP, DANGER
    $attention = $_POST['attention'];
    $dsi = $_POST['dsi'];

    // Dummy Trip ID = 1 dan Lokasi statis untuk Prototype Web
    $trip_id = 1;
    $lat = -7.0253; // Contoh: Bangkalan
    $lng = 112.7483;

    // Mapping Status dari Python ke Event Type Database
    $event_type = 'MILD_FATIGUE';
    if ($status == 'DROWSY')
        $event_type = 'DROWSY';
    if ($status == 'MICRO-SLEEP')
        $event_type = 'MICROSLEEP';
    if ($status == 'DANGER')
        $event_type = 'SEVERE_DROWSINESS';

    // Cegah spam log (hanya simpan jika status bukan NORMAL)
    if ($status != 'NORMAL') {
        $stmt = $conn->prepare("INSERT INTO drowsiness_events (trip_id, event_type, attention_score, dsi_status, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isissd", $trip_id, $event_type, $attention, $dsi, $lat, $lng);
        $stmt->execute();
        $stmt->close();
    }
    exit;
}

// ---------------------------------------------------------
// GET: MENGAMBIL LOG UNTUK DITAMPILKAN DI DASHBOARD
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_logs') {
    $result = $conn->query("SELECT event_type, timestamp FROM drowsiness_events ORDER BY id DESC LIMIT 5");

    $html = '';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $waktu = date('h:i A', strtotime($row['timestamp']));
            $event = $row['event_type'];

            // Konfigurasi UI berdasarkan Severity
            $icon = 'fa-eye-lowered';
            $color = 'warning';
            $title = 'Drowsiness Detected';

            if ($event == 'SEVERE_DROWSINESS' || $event == 'MICROSLEEP') {
                $icon = 'fa-triangle-exclamation fa-beat';
                $color = 'danger';
                $title = 'CRITICAL EMERGENCY';
            }

            $html .= '
            <div class="d-flex align-items-start mb-3 border-bottom border-secondary pb-2">
                <div class="bg-' . $color . ' bg-opacity-25 text-' . $color . ' p-2 rounded me-3"><i class="fa-solid ' . $icon . '"></i></div>
                <div>
                    <h6 class="m-0" style="font-size: 0.9rem;">' . $title . '</h6>
                    <small class="text-muted">Bangkalan Area • ' . $waktu . '</small>
                </div>
            </div>';
        }
    } else {
        $html = '<div class="text-muted text-center mt-4"><small>Belum ada log kejadian hari ini.</small></div>';
    }
    echo $html;
    exit;
}
?>