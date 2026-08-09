<!-- INTEGRASI AI JAVASCRIPT -->
<
script >
    const uiSafetyScore = document.getElementById('ui-safety-score');
const uiFatigueLevel = document.getElementById('ui-fatigue-level');
const iconStatus = document.getElementById('icon-status');
const uiFps = document.getElementById('ui-fps');
const uiEar = document.getElementById('ui-ear');
const riskBar = document.getElementById('risk-bar');
const riskText = document.getElementById('risk-text');

function fetchAIData() {
    fetch('http://localhost:5000/data')
        .then(response => {
            if (!response.ok) throw new Error("Flask Server Offline");
            return response.json();
        })
        .then(data => {
            // Update EAR dan Yawning
            uiEar.innerText = `EAR: ${data.ear.toFixed(2)} | Yawning: ${data.yawning}`;
            uiFps.innerText = `FPS: ${data.fps}`;

            // Pemetaan Attention Score (AWAS 1.0) menjadi Safety Score (AWAS 2.0)
            let safetyScore = data.attention;
            uiSafetyScore.innerHTML = `${safetyScore}<span style="font-size: 1rem; color: var(--text-muted);">/100</span>`;

            // Pemetaan Status menjadi Fatigue Level (0-5)
            let fatigueStatus = "AWAKE";
            let iconHTML = '<i class="fa-solid fa-face-smile"></i>';
            let riskPercent = 10;

            iconStatus.className = "stat-icon";

            if (data.status === "NORMAL") {
                fatigueStatus = "AWAKE";
                iconStatus.classList.add("bg-success", "bg-opacity-25", "text-success");
                uiFatigueLevel.className = "m-0 fw-bold text-success";

                riskPercent = 10;
                riskText.innerText = "LOW RISK";
                riskText.className = "text-success fw-bold";
                riskBar.className = "progress-bar bg-success";

            } else if (data.status === "DROWSY") {
                fatigueStatus = "MILD DROWSINESS";
                iconHTML = '<i class="fa-solid fa-face-meh"></i>';
                iconStatus.classList.add("bg-warning", "bg-opacity-25", "text-warning");
                uiFatigueLevel.className = "m-0 fw-bold text-warning";

                riskPercent = 60;
                riskText.innerText = "MEDIUM RISK";
                riskText.className = "text-warning fw-bold";
                riskBar.className = "progress-bar bg-warning";

            } else { // DANGER / MICROSLEEP
                fatigueStatus = "CRITICAL / UNRESPONSIVE";
                iconHTML = '<i class="fa-solid fa-triangle-exclamation fa-beat"></i>';
                iconStatus.classList.add("bg-danger", "bg-opacity-25", "text-danger");
                uiFatigueLevel.className = "m-0 fw-bold text-danger";

                riskPercent = 95;
                riskText.innerText = "HIGH RISK";
                riskText.className = "text-danger fw-bold";
                riskBar.className = "progress-bar bg-danger";
            }

            uiFatigueLevel.innerText = fatigueStatus;
            iconStatus.innerHTML = iconHTML;
            riskBar.style.width = riskPercent + "%";
        })
        .catch(error => {
            console.log("Kamera/Flask AI tidak menyala.", error);
        });
}
let lastLogTime = 0;

function saveLogToDatabase(status, attention, dsi) {
    const formData = new FormData();
    formData.append('action', 'save_log');
    formData.append('status', status);
    formData.append('attention', attention);
    formData.append('dsi', dsi);

    fetch('api_logs.php', { method: 'POST', body: formData })
        .then(() => refreshEventLog());
}

function refreshEventLog() {
    fetch('api_logs.php?action=get_logs')
        .then(response => response.text())
        .then(html => {
            const logContainer = document.getElementById('event-log-container');
            if (logContainer) logContainer.innerHTML = html;
        });
}

// Panggil refresh log setiap 3 detik
setInterval(refreshEventLog, 3000);
// Fetch data AI dari kamera setiap 500ms
setInterval(fetchAIData, 500); <
/script>