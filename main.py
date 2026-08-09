import os
import cv2
import dlib
import time
import threading
import csv
from datetime import datetime

# === TAMBAHAN FLASK AGAR KAMERA BISA TAMPIL DI WEB ===
from flask import Flask, Response, jsonify
from flask_cors import CORS

from imutils import face_utils

from utils.ear import eye_aspect_ratio, mouth_aspect_ratio
from utils.alarm import start_alarm, start_warning
# Fungsi draw_panel tidak dihapus importnya, tapi dimatikan di bawah agar tidak double
from utils.ui import draw_panel 

# =========================
# INISIALISASI FLASK SERVER
# =========================
app = Flask(__name__)
CORS(app) # Mengizinkan akses dari PHP (Localhost)

# =========================
# CONFIGURATION
# =========================
EAR_THRESHOLD = 0.25
MAR_THRESHOLD = 0.65 
WARNING_TIME = 2      
MICRO_SLEEP_TIME = 3  
DANGER_TIME = 4       
YAWN_WARNING_TIME = 2 

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
EVIDENCE_DIR = os.path.join(BASE_DIR, "evidence")

if not os.path.exists(EVIDENCE_DIR):
    os.makedirs(EVIDENCE_DIR)

CSV_FILE = os.path.join(BASE_DIR, "driver_log.csv")

if not os.path.exists(CSV_FILE):
    with open(CSV_FILE, mode='w', newline='') as f:
        writer = csv.writer(f)
        writer.writerow(["Timestamp", "Status", "Attention Score", "DSI", "Yawning"])

def log_event(status, attention, dsi, yawning):
    with open(CSV_FILE, mode='a', newline='') as f:
        writer = csv.writer(f)
        writer.writerow([datetime.now().strftime("%Y-%m-%d %H:%M:%S"), status, attention, dsi, yawning])

# =========================
# DLIB & CAMERA
# =========================
detector = dlib.get_frontal_face_detector()
model_path = os.path.join(BASE_DIR, "models", "shape_predictor_68_face_landmarks.dat")
predictor = dlib.shape_predictor(model_path)

(lStart, lEnd) = face_utils.FACIAL_LANDMARKS_IDXS["left_eye"]
(rStart, rEnd) = face_utils.FACIAL_LANDMARKS_IDXS["right_eye"]
(mStart, mEnd) = face_utils.FACIAL_LANDMARKS_IDXS["mouth"]

cap = cv2.VideoCapture(0)
cap.set(cv2.CAP_PROP_FRAME_WIDTH, 800) 
cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 600)

# DATA GLOBAL UNTUK DIKIRIM KE DASHBOARD WEB
system_data = {
    "status": "WAITING",
    "ear": 0.00,
    "yawning": "NO",
    "attention": 100,
    "dsi": "WAITING",
    "fps": 0
}

# =========================
# MAIN LOOP (DIUBAH JADI GENERATOR UNTUK FLASK)
# =========================
def generate_frames():
    global system_data
    closed_start = None
    yawn_start = None
    alarm_played = False
    warning_played = False
    prev_time = time.time()
    last_log_time = time.time()
    last_screenshot_time = 0
    attention_score = 100.0

    while True:
        ret, frame = cap.read()
        if not ret: break
        frame = cv2.flip(frame, 1)

        curr_time = time.time()
        fps = int(1 / max(curr_time - prev_time, 0.001))
        prev_time = curr_time

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = detector(gray)

        status = "NORMAL"
        ear = 0
        closed_time = 0
        is_yawning = False

        attention_score += 0.5
        if attention_score > 100: attention_score = 100

        for face in faces:
            x1, y1 = face.left(), face.top()
            x2, y2 = face.right(), face.bottom()

            shape = predictor(gray, face)
            shape = face_utils.shape_to_np(shape)

            leftEye = shape[lStart:lEnd]
            rightEye = shape[rStart:rEnd]
            mouth = shape[mStart:mEnd]

            leftEAR = eye_aspect_ratio(leftEye)
            rightEAR = eye_aspect_ratio(rightEye)
            ear = (leftEAR + rightEAR) / 2.0
            mar = mouth_aspect_ratio(mouth)

            for point in leftEye: cv2.circle(frame, tuple(point), 2, (255, 255, 0), -1)
            for point in rightEye: cv2.circle(frame, tuple(point), 2, (255, 255, 0), -1)
            
            if mar > MAR_THRESHOLD:
                if yawn_start is None: yawn_start = time.time()
                if time.time() - yawn_start >= YAWN_WARNING_TIME:
                    is_yawning = True
                    attention_score -= 1.0
                    cv2.putText(frame, "YAWNING!", (x1, y1 - 35), cv2.FONT_HERSHEY_DUPLEX, 0.8, (0, 165, 255), 2)
            else:
                yawn_start = None

            if ear < EAR_THRESHOLD:
                if closed_start is None: closed_start = time.time()
                closed_time = time.time() - closed_start
                attention_score -= 1.5

                if closed_time >= DANGER_TIME:
                    status = "DANGER"
                    cv2.putText(frame, "DANGER!", (x1, y1 - 10), cv2.FONT_HERSHEY_DUPLEX, 0.8, (0, 0, 255), 2)
                    if not warning_played:
                        threading.Thread(target=start_warning, daemon=True).start()
                        warning_played = True

                elif closed_time >= MICRO_SLEEP_TIME:
                    status = "MICRO-SLEEP"
                    cv2.putText(frame, "MICRO-SLEEP!", (x1, y1 - 10), cv2.FONT_HERSHEY_DUPLEX, 0.8, (0, 0, 255), 2)

                elif closed_time >= WARNING_TIME:
                    status = "DROWSY"
                    cv2.putText(frame, "WAKE UP!", (x1, y1 - 10), cv2.FONT_HERSHEY_DUPLEX, 0.8, (0, 255, 255), 2)
                    if not alarm_played:
                        threading.Thread(target=start_alarm, daemon=True).start()
                        alarm_played = True
            else:
                closed_start = None
                closed_time = 0
                alarm_played = False
                warning_played = False
                status = "NORMAL"

            box_color = (0, 255, 100) if status == "NORMAL" else (0, 255, 255) if status == "DROWSY" else (0, 0, 255)
            cv2.rectangle(frame, (x1, y1), (x2, y2), box_color, 2)

        attention_score = max(0, min(100, attention_score))
        
        if attention_score >= 80:
            dsi_status = "SAFE"
        elif attention_score >= 50:
            dsi_status = "WARNING"
        else:
            dsi_status = "HIGH RISK"
            if time.time() - last_screenshot_time > 5:
                timestamp = datetime.now().strftime("%Y_%m_%d_%H_%M_%S")
                filepath = os.path.join(EVIDENCE_DIR, f"danger_{timestamp}.jpg")
                cv2.imwrite(filepath, frame)
                last_screenshot_time = time.time()

        danger_percent = min(int((closed_time / DANGER_TIME) * 100), 100)
        cv2.rectangle(frame, (20, frame.shape[0] - 40), (320, frame.shape[0] - 20), (40, 40, 40), -1)
        cv2.rectangle(frame, (20, frame.shape[0] - 40), (20 + int(danger_percent * 3), frame.shape[0] - 20), (0, 0, 255), -1)
        cv2.putText(frame, f"EYE CLOSED: {danger_percent}%", (20, frame.shape[0] - 48), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 1)

        # ========================================================
        # PERBAIKAN DI SINI: MATIKAN DRAW_PANEL AGAR TIDAK DOUBLE
        # ========================================================
        # final_canvas = draw_panel(
        #     frame, status, ear, closed_time, fps, int(attention_score), is_yawning, dsi_status
        # )
        final_canvas = frame # LANGSUNG PAKAI FRAME ASLI!

        if time.time() - last_log_time >= 5:
            log_event(status, int(attention_score), dsi_status, is_yawning)
            last_log_time = time.time()

        # Update JSON Data untuk endpoint API
        system_data["status"] = status
        system_data["ear"] = float(ear)
        system_data["yawning"] = "YES" if is_yawning else "NO"
        system_data["attention"] = int(attention_score)
        system_data["dsi"] = dsi_status
        system_data["fps"] = fps

        # Konversi gambar untuk dikirim streaming ke WEB
        ret, buffer = cv2.imencode('.jpg', final_canvas)
        frame_bytes = buffer.tobytes()
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

# =========================
# FLASK ROUTING (JEMBATAN KE INDEX.PHP)
# =========================
@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/data')
def get_data():
    return jsonify(system_data)

if __name__ == '__main__':
    print("[INFO] Server berjalan. Silakan buka file INDEX.PHP di browser kamu!")
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)