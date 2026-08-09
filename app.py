import os
import cv2
import dlib
import time
import threading
from datetime import datetime

from flask import Flask, Response, jsonify
from flask_cors import CORS
from imutils import face_utils

# Import modul bawaan kamu (Pastikan folder utils dan file-nya ada)
from utils.ear import eye_aspect_ratio, mouth_aspect_ratio
from utils.alarm import start_alarm, start_warning

# ==========================================
# INISIALISASI FLASK SERVER
# ==========================================
app = Flask(__name__)
CORS(app) # Mengizinkan akses dari PHP (Localhost)

# ==========================================
# KONFIGURASI ENGINE AWAS 2.0
# ==========================================
EAR_THRESHOLD = 0.25
MAR_THRESHOLD = 0.65 

# Escalation Timing (Detik)
WARNING_TIME = 2        # STAGE 1: Drowsy / Mild Fatigue
MICRO_SLEEP_TIME = 3    # STAGE 2: Severe Drowsiness
DANGER_TIME = 4         # STAGE 3: Critical / Unresponsive -> Emergency Mode
YAWN_WARNING_TIME = 2 

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(BASE_DIR, "models", "shape_predictor_68_face_landmarks.dat")

detector = dlib.get_frontal_face_detector()
predictor = dlib.shape_predictor(model_path)

(lStart, lEnd) = face_utils.FACIAL_LANDMARKS_IDXS["left_eye"]
(rStart, rEnd) = face_utils.FACIAL_LANDMARKS_IDXS["right_eye"]
(mStart, mEnd) = face_utils.FACIAL_LANDMARKS_IDXS["mouth"]

# Global Data untuk dilempar ke PHP Dashboard (AJAX)
system_data = {
    "status": "NORMAL",
    "ear": 0.00,
    "yawning": "NO",
    "attention": 100, # Sekarang berfungsi sebagai Safety Score
    "dsi": "SAFE",
    "fps": 0,
    "emergency_mode": False
}

# ==========================================
# AI VIDEO GENERATOR (FATIGUE ENGINE)
# ==========================================
def generate_frames():
    global system_data
    cap = cv2.VideoCapture(0)
    
    # Resolusi standar web
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, 800) 
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 600)
    
    closed_start = None
    yawn_start = None
    alarm_played = False
    warning_played = False
    prev_time = time.time()
    
    # Safety Score Base
    safety_score = 100.0

    while True:
        ret, frame = cap.read()
        if not ret: break
        
        # Mirror effect
        frame = cv2.flip(frame, 1)

        # Hitung FPS Real-time
        curr_time = time.time()
        fps = int(1 / max(curr_time - prev_time, 0.001))
        prev_time = curr_time

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = detector(gray)

        status = "NORMAL"
        ear = 0.0
        is_yawning = False
        emergency_triggered = False

        # Recovery Safety Score perlahan jika mata terbuka
        safety_score += 0.5
        if safety_score > 100: safety_score = 100

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

            # Gambar titik di wajah (Visual modern AI)
            for point in leftEye: cv2.circle(frame, tuple(point), 2, (0, 255, 0), -1)
            for point in rightEye: cv2.circle(frame, tuple(point), 2, (0, 255, 0), -1)
            for point in mouth: cv2.circle(frame, tuple(point), 2, (0, 255, 255), -1)

            # ----------------------------------
            # DETEKSI YAWNING (MENGUAP)
            # ----------------------------------
            if mar > MAR_THRESHOLD:
                if yawn_start is None: yawn_start = time.time()
                if time.time() - yawn_start >= YAWN_WARNING_TIME:
                    is_yawning = True
                    safety_score -= 1.0 # Penalti Safety Score
                    cv2.putText(frame, "YAWNING DETECTED", (x1, y1 - 15), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 165, 255), 2)
            else:
                yawn_start = None

            # ----------------------------------
            # DETEKSI KANTUK & ESCALATION MODE
            # ----------------------------------
            if ear < EAR_THRESHOLD:
                if closed_start is None: closed_start = time.time()
                closed_time = time.time() - closed_start
                
                # Penalti Safety Score lebih besar saat mata tertutup
                safety_score -= 1.5

                # STAGE 3: EMERGENCY MODE (UNRESPONSIVE)
                if closed_time >= DANGER_TIME:
                    status = "DANGER"
                    emergency_triggered = True
                    box_color = (0, 0, 255)
                    cv2.putText(frame, "CRITICAL: EMERGENCY PROTOCOL", (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 0, 255), 2)
                    if not warning_played:
                        threading.Thread(target=start_warning, daemon=True).start()
                        warning_played = True

                # STAGE 2: SEVERE DROWSINESS (MICROSLEEP)
                elif closed_time >= MICRO_SLEEP_TIME:
                    status = "MICRO-SLEEP"
                    box_color = (0, 0, 255)
                    cv2.putText(frame, "SEVERE DROWSINESS", (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 0, 255), 2)

                # STAGE 1: MILD FATIGUE / DROWSY
                elif closed_time >= WARNING_TIME:
                    status = "DROWSY"
                    box_color = (0, 255, 255)
                    cv2.putText(frame, "WARNING: PLEASE WAKE UP", (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 255), 2)
                    if not alarm_played:
                        threading.Thread(target=start_alarm, daemon=True).start()
                        alarm_played = True
            else:
                closed_start = None
                alarm_played = False
                warning_played = False
                status = "NORMAL"
                box_color = (0, 255, 100)

            # Bounding Box Wajah dengan warna dinamis
            cv2.rectangle(frame, (x1, y1), (x2, y2), box_color, 2)

        # ----------------------------------
        # FINALISASI DATA & STREAMING
        # ----------------------------------
        safety_score = max(0, min(100, safety_score))
        
        # Klasifikasi DSI untuk PHP
        if safety_score >= 80: dsi_status = "SAFE"
        elif safety_score >= 50: dsi_status = "WARNING"
        else: dsi_status = "HIGH RISK"

        # Update JSON Data untuk Endpoint API
        system_data["status"] = status
        system_data["ear"] = round(ear, 2)
        system_data["yawning"] = "YES" if is_yawning else "NO"
        system_data["attention"] = int(safety_score)
        system_data["dsi"] = dsi_status
        system_data["fps"] = fps
        system_data["emergency_mode"] = emergency_triggered

        # Encode Frame to JPEG for Web Stream
        # TIDAK ADA LAGI UI DASHBOARD OPENCV, SEMUA MURNI VIDEO & LANDMARKS
        ret, buffer = cv2.imencode('.jpg', frame)
        frame_bytes = buffer.tobytes()
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

# ==========================================
# FLASK ROUTING (JEMBATAN KE PHP)
# ==========================================
@app.route('/video_feed')
def video_feed():
    # Route ini dipanggil oleh tag <img> di dashboard.php
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/data')
def get_data():
    # Route ini dipanggil oleh AJAX fetch() di dashboard.php
    return jsonify(system_data)

if __name__ == '__main__':
    print("[INFO] AWAS 2.0 AI Engine Berjalan di Port 5000.")
    print("[INFO] Silakan buka http://localhost/driver-drowsiness-system/index.php")
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)