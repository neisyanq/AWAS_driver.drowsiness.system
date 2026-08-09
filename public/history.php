<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_drowsiness';
$conn = new mysqli($host, $user, $pass, $db);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safety History - AWAS 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- (Gunakan style CSS yang sama persis dengan dashboard.php di sini) -->
    <style>
        :root {
            --bg-main: #f8fafc;
            /* Menggunakan Light/Clean Mode untuk tabel data agar profesional */
            --bg-panel: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .panel-card {
            background-color: var(--bg-panel);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Level Dots Indicator */
        .dot {
            height: 12px;
            width: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-green {
            background-color: #10b981;
            box-shadow: 0 0 5px rgba(16, 185, 129, 0.5);
        }

        .dot-yellow {
            background-color: #facc15;
            box-shadow: 0 0 5px rgba(250, 204, 21, 0.5);
        }

        .dot-orange {
            background-color: #f97316;
            box-shadow: 0 0 5px rgba(249, 115, 22, 0.5);
        }

        .dot-red {
            background-color: #ef4444;
            box-shadow: 0 0 5px rgba(239, 68, 68, 0.5);
        }

        .custom-table th {
            background-color: transparent;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.9rem;
            border-bottom: 2px solid var(--border-color);
        }

        .custom-table td {
            vertical-align: middle;
            padding: 15px 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            font-weight: 500;
        }
    </style>
</head>

<body>

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0"><i class="fa-solid fa-scroll text-primary me-2"></i> DRIVER SAFETY HISTORY</h3>
            <a href="dashboard.php" class="btn btn-outline-primary fw-bold"><i class="fa-solid fa-arrow-left me-2"></i>
                Back to Dashboard</a>
        </div>

        <div class="panel-card">
            <p class="text-muted mb-4">Setiap kejadian dan peringatan keselamatan selama perjalanan Anda tersimpan
                secara otomatis di sistem.</p>

            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Kejadian</th>
                            <th>Level</th>
                            <th>Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil riwayat dari database
                        $sql = "SELECT * FROM drowsiness_events ORDER BY id DESC LIMIT 20";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $waktu = date('H:i', strtotime($row['timestamp']));
                                $kejadian = str_replace('_', ' ', $row['event_type']);
                                $kejadian = ucwords(strtolower($kejadian));

                                // Mapping Level Dot
                                $dot_class = 'dot-green';
                                if ($row['event_type'] == 'MILD_FATIGUE')
                                    $dot_class = 'dot-yellow';
                                if ($row['event_type'] == 'DROWSY' || $row['event_type'] == 'YAWNING')
                                    $dot_class = 'dot-orange';
                                if ($row['event_type'] == 'SEVERE_DROWSINESS' || $row['event_type'] == 'MICROSLEEP')
                                    $dot_class = 'dot-red';

                                // Lokasi Statis untuk Prototype, idealnya ditarik dari kolom latitude/longitude
                                $lokasi = ($row['event_type'] == 'MILD_FATIGUE') ? 'Bangkalan' : 'Surabaya';

                                echo "<tr>";
                                echo "<td>{$waktu}</td>";
                                echo "<td>{$kejadian}</td>";
                                echo "<td><span class='dot {$dot_class}'></span></td>";
                                echo "<td>{$lokasi}</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-muted py-4'>Belum ada riwayat keselamatan. Data akan muncul ketika sistem mendeteksi aktivitas.</td></tr>";
                        }
                        ?>
                        <!-- Contoh Normal Status -->
                        <tr>
                            <td>10:01</td>
                            <td>Normal / Awake</td>
                            <td><span class="dot dot-green"></span></td>
                            <td>Surabaya</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold mb-2">My Safety History Report</h6>
                <button class="btn btn-primary btn-sm fw-bold"><i class="fa-solid fa-download me-2"></i> Export to
                    PDF</button>
            </div>
        </div>
    </div>

</body>

</html>