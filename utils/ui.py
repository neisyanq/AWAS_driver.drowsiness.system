import cv2
import numpy as np

def draw_panel(frame, status, ear, closed_time, fps, attention_score, is_yawning, dsi):
    h, w, _ = frame.shape
    
    # Lebar panel UI di sebelah kanan
    ui_width = 380 
    
    # 1. Buat kanvas kosong baru yang lebih lebar dari kamera
    # Warna background estetis (Dark Navy/Gray: BGR 40, 30, 25)
    canvas = np.zeros((h, w + ui_width, 3), dtype=np.uint8)
    canvas[:] = (35, 25, 20) 
    
    # 2. Tempelkan video kamera di sisi paling kiri kanvas
    canvas[0:h, 0:w] = frame
    
    # --- MULAI MENGGAMBAR DASHBOARD DI KANAN KAMERA ---
    ui_x = w + 20  # Titik awal X untuk dashboard
    start_y = 30   # Titik awal Y
    
    # Fungsi pembantu untuk menggambar kotak-kotak metrik yang rapi
    def draw_card(y, title, value, val_color, show_bar=False, bar_pct=0):
        # Background kotak metrik (agak terang dari background utama)
        cv2.rectangle(canvas, (ui_x, y), (ui_x + ui_width - 40, y + 65), (55, 45, 35), -1)
        
        # Teks Judul Metrik
        cv2.putText(canvas, title, (ui_x + 15, y + 25), cv2.FONT_HERSHEY_SIMPLEX, 0.45, (180, 180, 180), 1)
        
        # Teks Nilai (Besar)
        cv2.putText(canvas, str(value), (ui_x + 15, y + 55), cv2.FONT_HERSHEY_DUPLEX, 0.8, val_color, 2)
        
        # Jika butuh progress bar (untuk Attention Score)
        if show_bar:
            bar_x = ui_x + 150
            cv2.rectangle(canvas, (bar_x, y + 40), (bar_x + 160, y + 50), (40, 40, 40), -1) # BG Bar
            
            fill_w = int((bar_pct / 100) * 160)
            cv2.rectangle(canvas, (bar_x, y + 40), (bar_x + fill_w, y + 50), val_color, -1) # Fill Bar
            
        return y + 80 # Jarak antar kotak

    # Tentukan Warna Dinamis
    status_color = (100, 255, 0) if status == "NORMAL" else (0, 255, 255) if status == "DROWSY" else (0, 0, 255)
    dsi_color = (100, 255, 0) if dsi == "SAFE" else (0, 255, 255) if dsi == "WARNING" else (0, 0, 255)
    att_color = (100, 255, 0) if attention_score >= 80 else (0, 255, 255) if attention_score >= 50 else (0, 0, 255)
    
    yawn_text = "YES" if is_yawning else "NO"
    yawn_color = (0, 0, 255) if is_yawning else (100, 255, 0)
    
    # 3. Susun kotak-kotak hasil deteksi ke bawah
    curr_y = start_y
    curr_y = draw_card(curr_y, "DRIVER STATUS", status, status_color)
    curr_y = draw_card(curr_y, "SAFETY INDEX (DSI)", dsi, dsi_color)
    curr_y = draw_card(curr_y, "ATTENTION SCORE", f"{attention_score}%", att_color, show_bar=True, bar_pct=attention_score)
    curr_y = draw_card(curr_y, "EYE ASPECT RATIO", f"{ear:.2f}", (255, 255, 255))
    curr_y = draw_card(curr_y, "YAWNING DETECTED", yawn_text, yawn_color)
    
    # Garis pembatas tipis antara Kamera dan Dashboard
    cv2.line(canvas, (w, 0), (w, h), (80, 70, 60), 1)

    return canvas