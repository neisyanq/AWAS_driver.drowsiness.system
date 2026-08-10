<?php
session_start();
// Halaman ini khusus untuk UI Login & Register
// Jika sudah login, arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWAS 2.0 - Intelligent Driver Safety System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    <style>
        /* ==========================================
           1. CORE VARIABLES (VIBRANT UI/UX THEME)
           ========================================== */
        :root {
            var(--primary-color): #4338ca;
            --primary-color: #4338ca;
            --secondary-color: #0ea5e9;
            --accent-color: #f43f5e;

            --gradient-main: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
            --gradient-accent: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
            --gradient-sunset: linear-gradient(135deg, #f43f5e 0%, #fb923c 100%);

            --bg-body: #f8fafc;
            --bg-white: #ffffff;
            --bg-glass: rgba(255, 255, 255, 0.85);

            --text-dark: #0f172a;
            --text-gray: #475569;
            --text-light: #94a3b8;

            --shadow-sm: 0 4px 15px -3px rgba(67, 56, 202, 0.08);
            --shadow-md: 0 10px 25px -5px rgba(67, 56, 202, 0.12);
            --shadow-lg: 0 20px 40px -10px rgba(67, 56, 202, 0.15);
            --shadow-glow: 0 10px 20px rgba(99, 102, 241, 0.3);

            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-xl: 24px;
        }

        body {
            background-color: var(--bg-body);
            background-image:
                radial-gradient(at 0% 0%, rgba(224, 231, 255, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(224, 242, 254, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(254, 226, 226, 0.4) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .navbar-brand,
        .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.5px;
        }

        /* ==========================================
           2. ANIMATIONS & AESTHETIC EFFECTS
           ========================================== */
        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1) rotate(0deg);
            }

            33% {
                transform: translate(20px, -30px) scale(1.05) rotate(5deg);
            }

            66% {
                transform: translate(-10px, 15px) scale(0.95) rotate(-5deg);
            }

            100% {
                transform: translate(0px, 0px) scale(1) rotate(0deg);
            }
        }

        .gradient-text {
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .gradient-text-accent {
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        /* ==========================================
           ILLUSTRASI COMPUTER VISION (CSS ONLY)
           ========================================== */
        .cv-illustration-wrapper {
            position: relative;
            width: 100%;
            height: 320px;
            background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #dbeafe;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
        }

        .cv-dashboard {
            position: relative;
            width: 90%;
            height: 80%;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(67, 56, 202, 0.1);
            display: flex;
            padding: 15px;
            gap: 15px;
            z-index: 2;
        }

        .cv-camera-feed {
            position: relative;
            flex: 1;
            background: #0f172a;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #1e293b;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .cv-face {
            position: relative;
            font-size: 6rem;
            color: #475569;
        }

        .cv-bounding-box {
            position: absolute;
            width: 110px;
            height: 130px;
            border: 2px solid #38bdf8;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.2) inset, 0 0 15px rgba(56, 189, 248, 0.2);
            background: rgba(56, 189, 248, 0.05);
        }

        .cv-scan-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: #22c55e;
            box-shadow: 0 0 10px #22c55e, 0 0 20px #22c55e;
            animation: scanVertical 2.5s ease-in-out infinite;
        }

        @keyframes scanVertical {

            0%,
            100% {
                top: 5%;
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            50% {
                top: 95%;
            }
        }

        @keyframes cvPulse {
            0% {
                transform: scale(0.8);
                opacity: 0.7;
            }

            100% {
                transform: scale(1.3);
                opacity: 1;
            }
        }

        .cv-eye-track {
            position: absolute;
            width: 8px;
            height: 8px;
            background: #f43f5e;
            border-radius: 50%;
            box-shadow: 0 0 10px #f43f5e;
            animation: cvPulse 1.5s infinite alternate;
        }

        .cv-eye-left {
            top: 38px;
            left: 28px;
        }

        .cv-eye-right {
            top: 38px;
            right: 28px;
        }

        .cv-mouth-track {
            position: absolute;
            width: 12px;
            height: 6px;
            border-radius: 10px;
            top: 90px;
            left: 49px;
            background: #eab308;
            box-shadow: 0 0 10px #eab308;
            animation: cvPulse 1.5s infinite alternate;
        }

        .cv-stats {
            width: 140px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .cv-stat-box {
            background: white;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .cv-stat-bar {
            height: 5px;
            background: #f1f5f9;
            border-radius: 3px;
            margin-top: 6px;
            overflow: hidden;
        }

        .cv-stat-fill {
            height: 100%;
            background: var(--gradient-main);
            width: 85%;
        }

        .cv-corner {
            position: absolute;
            width: 12px;
            height: 12px;
            border-color: #38bdf8;
            border-style: solid;
        }

        .cv-c-tl {
            top: -2px;
            left: -2px;
            border-width: 3px 0 0 3px;
        }

        .cv-c-tr {
            top: -2px;
            right: -2px;
            border-width: 3px 3px 0 0;
        }

        .cv-c-bl {
            bottom: -2px;
            left: -2px;
            border-width: 0 0 3px 3px;
        }

        .cv-c-br {
            bottom: -2px;
            right: -2px;
            border-width: 0 3px 3px 0;
        }

        /* ==========================================
           3. FULL WIDTH NAVBAR
           ========================================== */
        .navbar-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            padding: 0;
            transition: all 0.4s ease;
        }

        .navbar-custom {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 0;
            padding: 15px 5%;
            box-shadow: var(--shadow-sm);
            max-width: 100%;
            margin: 0;
        }

        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color) !important;
            font-size: 1.1rem;
        }

        .nav-item-lp .nav-link {
            font-weight: 600;
            color: var(--text-gray);
            margin: 0 5px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border-radius: 50rem;
            padding: 8px 18px !important;
        }

        .nav-item-lp .nav-link:hover,
        .nav-item-lp .nav-link.active {
            color: var(--primary-color);
            background: #e0e7ff;
        }

        .btn-login-nav {
            background: var(--primary-color);
            color: white;
            font-weight: 700;
            border-radius: 50rem;
            padding: 10px 28px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-glow);
            border: none;
        }

        .btn-login-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(67, 56, 202, 0.4);
            color: white;
            background: #3730a3;
        }

        /* ==========================================
           4. HERO SECTION (FULL FRAME BACKGROUND)
           ========================================== */
        .hero-section {
            padding: 100px 0 0 0;
            position: relative;
            z-index: 1;
            background-image: linear-gradient(90deg, rgba(248, 250, 252, 0.95) 0%, rgba(248, 250, 252, 0.6) 45%, rgba(255, 255, 255, 0) 100%), url('img/gambar1.png');
            background-size: 70%;
            background-position: center right;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero-blob {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            opacity: 0.6;
            border-radius: 50%;
            animation: blob 15s infinite alternate ease-in-out;
        }

        .blob-1 {
            top: 10%;
            left: -5%;
            width: 350px;
            height: 350px;
            background: #e0e7ff;
        }

        .blob-2 {
            bottom: -10%;
            right: 0%;
            width: 450px;
            height: 450px;
            background: #e0f2fe;
            animation-delay: 2s;
        }

        .blob-3 {
            top: 20%;
            left: 50%;
            width: 250px;
            height: 250px;
            background: #fce7f3;
            animation-delay: 4s;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.2;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .hero-subtitle {
            font-size: 1.05rem;
            color: var(--text-gray);
            margin-bottom: 35px;
            line-height: 1.7;
            max-width: 90%;
        }

        .btn-hero-primary {
            background: var(--gradient-main);
            color: white;
            border-radius: 50rem;
            padding: 14px 30px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            box-shadow: var(--shadow-glow);
            transition: all 0.3s ease;
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4);
            color: white;
        }

        .btn-hero-secondary {
            background: white;
            color: var(--text-dark);
            border-radius: 50rem;
            padding: 14px 30px;
            font-weight: 700;
            font-size: 1rem;
            border: 2px solid transparent;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .btn-hero-secondary:hover {
            border-color: #e2e8f0;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        /* ==========================================
           5. SECTION PADDING
           ========================================== */
        .section-padding {
            padding: 100px 0;
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 15px;
            color: var(--text-dark);
            letter-spacing: -1px;
        }

        .section-subtitle {
            color: var(--text-gray);
            margin-bottom: 40px;
            font-size: 1rem;
            line-height: 1.7;
        }

        /* ==========================================
           6. NEW CSS FOR TENTANG SECTION (REDESIGN)
           ========================================== */
        .badge-early-warning {
            background: #e0f2fe;
            color: #0284c7;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 8px 16px;
            border-radius: 50rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .feature-card-new {
            background: #ffffff;
            border-radius: 24px;
            padding: 32px 24px 24px 24px;
            height: 100%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid #f1f5f9;
            position: relative;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .feature-card-new:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(67, 56, 202, 0.08);
            border-color: transparent;
        }

        .fc-badge {
            position: absolute;
            top: 24px;
            right: 24px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.85rem;
        }

        .fc-b-1 {
            background: #e0e7ff;
            color: #4338ca;
        }

        .fc-b-2 {
            background: #e0f2fe;
            color: #0ea5e9;
        }

        .fc-b-3 {
            background: #ffe4e6;
            color: #f43f5e;
        }

        .fc-b-4 {
            background: #fef3c7;
            color: #f59e0b;
        }

        .fc-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 24px;
        }

        .fc-i-1 {
            background: #e0e7ff;
            color: #4338ca;
        }

        .fc-i-2 {
            background: #e0f2fe;
            color: #0ea5e9;
        }

        .fc-i-3 {
            background: #ffe4e6;
            color: #f43f5e;
        }

        .fc-i-4 {
            background: #fef3c7;
            color: #f59e0b;
        }

        .fc-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .fc-desc {
            color: var(--text-gray);
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .fc-visual-box {
            margin-top: auto;
            border-radius: 16px;
            height: 130px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ==========================================
           7. TIMELINE & ADDITIONAL CARDS (REVISED LAYOUT)
           ========================================== */
        .timeline-section {
            background: linear-gradient(180deg, #f8fafc 0%, #e0e7ff 100%);
            border-radius: 40px;
            margin: 0 15px;
        }

        .timeline-horizontal-row {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            position: relative;
            padding-bottom: 20px;
            scrollbar-width: none;
            z-index: 1;
        }

        .timeline-horizontal-row::-webkit-scrollbar {
            display: none;
        }

        .timeline-horizontal-row::before {
            content: '';
            position: absolute;
            top: 45px;
            left: 25px;
            right: 25px;
            border-top: 2px dashed #94a3b8;
            z-index: -1;
        }

        .step-col-width {
            flex: 0 0 auto;
            width: 16.666667%;
        }

        @media (max-width: 1200px) {
            .step-col-width {
                width: 25%;
            }
        }

        @media (max-width: 992px) {
            .step-col-width {
                width: 40%;
            }
        }

        @media (max-width: 768px) {
            .step-col-width {
                width: 75%;
            }
        }

        .step-box {
            background: var(--bg-white);
            padding: 20px 15px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            overflow: hidden;
        }

        .step-box:hover {
            transform: translateY(-10px);
            border-color: #c7d2fe;
            box-shadow: var(--shadow-lg);
        }

        .step-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-main);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 15px;
            box-shadow: var(--shadow-glow);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .step-box:hover .step-icon-wrapper {
            transform: scale(1.15) rotate(8deg);
        }

        .step-number {
            display: none;
        }

        /* ==========================================
           8. CUSTOM PENGADUAN & FAQ
           ========================================== */
        .card-custom-new {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-custom-new:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .heading-blue {
            color: #2563eb;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .subheading-new {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .form-label-new {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control-new {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.9rem;
            color: #1e293b;
            transition: all 0.3s ease;
        }

        .form-control-new:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
            background: #ffffff;
        }

        .btn-submit-new {
            background: #2563eb;
            color: white;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            border: none;
            transition: 0.3s;
        }

        .btn-submit-new:hover {
            background: #1d4ed8;
            color: white;
            transform: translateY(-2px);
        }

        .accordion-new .accordion-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px !important;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .accordion-new .accordion-button {
            background: #ffffff;
            color: #334155;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 16px;
            box-shadow: none;
        }

        .accordion-new .accordion-button:not(.collapsed) {
            background: #eff6ff;
            color: #2563eb;
            border-bottom: 1px solid #e2e8f0;
        }

        .accordion-new .accordion-body {
            background: #ffffff;
            color: #475569;
            font-size: 0.85rem;
            padding: 16px;
            line-height: 1.6;
        }

        .help-box-new {
            background: #eff6ff;
            border-radius: 8px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
        }

        .help-box-new .btn-help {
            background: #ffffff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }

        .help-box-new .btn-help:hover {
            background: #2563eb;
            color: white;
        }

        /* ==========================================
           9. FOOTER AESTHETIC
           ========================================== */
        .footer-aesthetic {
            background-color: #2563eb;
            color: #ffffff;
            position: relative;
            padding-top: 50px;
            padding-bottom: 20px;
            margin-top: 80px;
            border: none;
        }

        .footer-wave {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            transform: translateY(-99%);
        }

        .footer-wave svg {
            display: block;
            width: calc(100% + 1.3px);
            height: 65px;
        }

        .footer-wave .shape-fill {
            fill: #2563eb;
        }

        .footer-aesthetic h5,
        .footer-aesthetic h6 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 1.2rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .footer-aesthetic p,
        .footer-aesthetic ul li a,
        .footer-aesthetic .contact-info {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .footer-aesthetic ul li a:hover {
            color: #ffffff;
            padding-left: 5px;
        }

        .footer-aesthetic .contact-info i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }

        .footer-social-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #ffffff;
            color: #2563eb;
            border-radius: 50%;
            margin-right: 10px;
            transition: 0.3s;
            text-decoration: none;
        }

        .footer-social-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            color: #1d4ed8;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
            position: relative;
        }

        .back-to-top-btn {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background-color: #ec4899;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(236, 72, 153, 0.4);
            transition: 0.3s;
        }

        .back-to-top-btn:hover {
            background-color: #db2777;
            color: white;
        }

        /* ==========================================
           10. LIVE TRACKING MAP (REDESIGN)
           ========================================== */
        .map-wrapper {
            position: relative;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.8);
            z-index: 5;
        }

        #tracking-map {
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .map-overlay-card {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: var(--radius-lg);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            z-index: 10;
            border: 1px solid rgba(255, 255, 255, 0.6);
            transition: all 0.3s ease;
        }

        .map-overlay-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .driver-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .driver-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            box-shadow: 0 4px 10px rgba(67, 56, 202, 0.2);
        }

        .status-badge {
            background: #dcfce7;
            color: #166534;
            padding: 5px 10px;
            border-radius: 50rem;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .route-stats {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }

        .stat-item h6 {
            font-size: 0.75rem;
            color: var(--text-gray);
            margin-bottom: 4px;
            font-weight: 600;
        }

        .stat-item h4 {
            font-size: 1.1rem;
            color: var(--text-dark);
            font-weight: 800;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gps-ping {
            width: 20px;
            height: 20px;
            background-color: var(--primary-color);
            border-radius: 50%;
            position: absolute;
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes ping {

            75%,
            100% {
                transform: scale(2.5);
                opacity: 0;
            }
        }

        .telemetry-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .telemetry-card:hover {
            background: #ffffff;
            border-color: #e2e8f0;
            box-shadow: var(--shadow-sm);
            transform: translateY(-2px);
        }

        .telemetry-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        /* ==========================================
           11. CUSTOM ALERT MODAL (PENGGANTI DEFAULT BROWSER ALERT)
           ========================================== */
        .custom-alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .custom-alert-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .custom-alert-box {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px 30px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: translateY(30px) scale(0.95);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .custom-alert-overlay.show .custom-alert-box {
            transform: translateY(0) scale(1);
        }

        .custom-alert-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--gradient-main);
        }

        .alert-icon-wrapper {
            width: 80px;
            height: 80px;
            background: #e0e7ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            position: relative;
        }

        .alert-icon-wrapper i {
            font-size: 2.2rem;
            color: var(--primary-color);
            z-index: 2;
            animation: float-icon 3s ease-in-out infinite;
        }

        .alert-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            background: #c7d2fe;
            border-radius: 50%;
            z-index: 1;
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }

        @keyframes float-icon {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.95);
                opacity: 1;
            }

            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        .alert-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .alert-message {
            color: var(--text-gray);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .alert-btn {
            background: var(--gradient-main);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 50rem;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-glow);
            width: 100%;
        }

        .alert-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(67, 56, 202, 0.4);
            background: #3730a3;
        }
    </style>
</head>

<body>

    <!-- CUSTOM GPS ALERT MODAL -->
    <div id="customGpsAlert" class="custom-alert-overlay">
        <div class="custom-alert-box">
            <div class="alert-icon-wrapper">
                <div class="alert-pulse"></div>
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <h3 class="alert-title">PERINGATAN AWAS 2.0</h3>
            <p class="alert-message">Harap pastikan Anda <b>mengaktifkan fitur lokasi (GPS)</b> pada perangkat Anda agar
                sistem dapat memantau perjalanan dan mengaktifkan fitur Live Tracking dengan akurat.</p>
            <button class="alert-btn" onclick="closeGpsAlert()">Mengerti & Izinkan</button>
        </div>
    </div>

    <!-- NAVBAR WRAPPER -->
    <div class="navbar-wrapper">
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid px-2">
                <a class="navbar-brand d-flex align-items-center gap-2" href="#" data-aos="fade-right">
                    <div class="text-white d-flex justify-content-center align-items-center"
                        style="width: 32px; height: 32px; background: var(--gradient-main); border-radius: 10px; box-shadow: 0 4px 10px rgba(67,56,202,0.3);">
                        <i class="fa-solid fa-shield-halved fs-6"></i>
                    </div>
                    AWAS 2.0
                </a>
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">
                    <i class="fa-solid fa-bars-staggered text-primary fs-4"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto nav-item-lp" data-aos="fade-down" data-aos-delay="100">
                        <li class="nav-item"><a class="nav-link active" href="#beranda">Beranda</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tentang">Keunggulan</a></li>
                        <li class="nav-item"><a class="nav-link" href="#cara-kerja">Alur Sistem</a></li>
                        <li class="nav-item"><a class="nav-link" href="#live-tracking">Peta Live</a></li>
                        <li class="nav-item"><a class="nav-link" href="#pengaduan-faq">Bantuan</a></li>
                    </ul>
                    <div class="d-flex mt-3 mt-lg-0 gap-2" data-aos="fade-left" data-aos-delay="200">
                        <a href="#auth-section" onclick="document.getElementById('pills-login-tab').click();"
                            class="btn btn-hero-secondary text-decoration-none px-4 py-2 fw-bold d-flex align-items-center"
                            style="border-radius: 50rem; font-size: 0.95rem;">
                            Masuk
                        </a>
                        <a href="#auth-section" onclick="document.getElementById('pills-register-tab').click();"
                            class="btn btn-login-nav w-100 w-lg-auto d-flex align-items-center justify-content-center gap-2 px-4">
                            Daftar <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <!-- HERO SECTION -->
    <section id="beranda" class="hero-section">
        <div class="blob-1 hero-blob"></div>
        <div class="blob-2 hero-blob"></div>
        <div class="blob-3 hero-blob"></div>

        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-7 hero-content text-center text-lg-start mb-5 mb-lg-0" data-aos="fade-up"
                    data-aos-duration="1000" style="position: relative; z-index: 2;">
                    <h1 class="hero-title">Sistem Keselamatan Pengemudi Berbasis <span class="gradient-text">Computer
                            Vision</span></h1>
                    <p class="hero-subtitle mx-auto mx-lg-0">AWAS membantu memantau kondisi pengemudi selama perjalanan
                        melalui kamera. Sistem mengenali tanda-tanda kantuk dan memberikan peringatan ketika tingkat
                        kewaspadaan mulai menurun</p>

                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <a href="#auth-section" onclick="document.getElementById('pills-register-tab').click();"
                            class="btn-hero-primary text-decoration-none text-center">
                            Mulai Pemantauan
                        </a>
                        <a href="#cara-kerja"
                            class="btn-hero-secondary text-decoration-none text-center d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-circle-play text-primary fs-5"></i> Lihat Cara Kerja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TENTANG SECTION (REDESIGN SESUAI GAMBAR 2) -->
    <section id="tentang" class="section-padding" style="background-color: #f8fafc;">
        <div class="container">
            <!-- Header Section -->
            <div class="row align-items-center mb-5">
                <div class="col-lg-6 text-start mb-4 mb-lg-0" data-aos="fade-right">
                    <h2 class="section-title text-start mb-3"
                        style="font-size: 3.5rem; line-height: 1.1; font-family: 'Plus Jakarta Sans', sans-serif;">
                        Mengapa<br><span style="color: #4338ca;">AWAS?</span>
                    </h2>
                    <div
                        style="width: 50px; height: 4px; background: #4338ca; margin-bottom: 24px; border-radius: 2px;">
                    </div>
                    <p class="section-subtitle text-start m-0" style="max-width: 90%; font-size: 1.05rem;">
                        AWAS dirancang untuk membantu pengemudi mengenali tanda-tanda kantuk sejak dini melalui
                        pemantauan berbasis computer vision dan memberikan peringatan ketika kewaspadaan mulai menurun.
                    </p>
                </div>

                <!-- BAGIAN YANG DIUBAH MENJADI ILUSTRASI CSS MURNI -->
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="cv-illustration-wrapper">
                        <!-- Decorative blur blobs -->
                        <div
                            style="position: absolute; width: 200px; height: 200px; background: rgba(99, 102, 241, 0.2); border-radius: 50%; filter: blur(40px); top: -50px; right: -50px;">
                        </div>
                        <div
                            style="position: absolute; width: 150px; height: 150px; background: rgba(14, 165, 233, 0.2); border-radius: 50%; filter: blur(40px); bottom: -30px; left: -30px;">
                        </div>

                        <!-- Dashboard Mockup -->
                        <div class="cv-dashboard">
                            <!-- Left: Camera Feed -->
                            <div class="cv-camera-feed">
                                <i class="fa-solid fa-user-tie cv-face"></i>
                                <div class="cv-bounding-box">
                                    <div class="cv-eye-track cv-eye-left"></div>
                                    <div class="cv-eye-track cv-eye-right"></div>
                                    <div class="cv-mouth-track"></div>
                                    <div class="cv-scan-line"></div>
                                    <!-- UI Corners for Bounding Box -->
                                    <div class="cv-corner cv-c-tl"></div>
                                    <div class="cv-corner cv-c-tr"></div>
                                    <div class="cv-corner cv-c-bl"></div>
                                    <div class="cv-corner cv-c-br"></div>
                                </div>
                                <!-- Camera Rec indicator -->
                                <div
                                    style="position: absolute; top: 12px; right: 12px; display: flex; align-items: center; gap: 5px;">
                                    <div
                                        style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%; animation: blinkPulse 1s infinite;">
                                    </div>
                                    <span
                                        style="color: rgba(255,255,255,0.9); font-size: 0.6rem; font-weight: bold; letter-spacing: 1px;">REC</span>
                                </div>
                            </div>

                            <!-- Right: Stats Panel -->
                            <div class="cv-stats">
                                <div class="cv-stat-box">
                                    <div style="font-size: 0.6rem; color: #64748b; font-weight: bold;">STATUS</div>
                                    <div
                                        style="font-size: 0.85rem; color: #10b981; font-weight: 800; display: flex; align-items: center; gap: 5px; margin-top: 2px;">
                                        <i class="fa-solid fa-shield-check"></i> Waspada
                                    </div>
                                </div>
                                <div class="cv-stat-box">
                                    <div style="font-size: 0.6rem; color: #64748b; font-weight: bold;">EAR (Mata)</div>
                                    <div style="font-size: 0.85rem; color: #0f172a; font-weight: 800; margin-top: 2px;">
                                        0.32</div>
                                    <div class="cv-stat-bar">
                                        <div class="cv-stat-fill" style="width: 85%;"></div>
                                    </div>
                                </div>
                                <div class="cv-stat-box">
                                    <div style="font-size: 0.6rem; color: #64748b; font-weight: bold;">MAR (Mulut)</div>
                                    <div style="font-size: 0.85rem; color: #0f172a; font-weight: 800; margin-top: 2px;">
                                        0.15</div>
                                    <div class="cv-stat-bar">
                                        <div class="cv-stat-fill" style="width: 30%; background: #0ea5e9;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- AKHIR BAGIAN YANG DIUBAH -->

            </div>

            <!-- Cards Section -->
            <div class="row g-4 mb-5">
                <!-- Card 1 -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card-new">
                        <div class="fc-badge fc-b-1 shadow-sm">01</div>
                        <div class="fc-icon fc-i-1"><i class="fa-solid fa-eye"></i></div>
                        <h4 class="fc-title">Deteksi Tanda Kantuk</h4>
                        <p class="fc-desc">Memantau kondisi mata dan wajah pengemudi secara real-time untuk mengenali
                            tanda-tanda penurunan kewaspadaan.</p>
                        <div class="fc-visual-box"
                            style="background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);">
                            <i class="fa-solid fa-expand position-absolute"
                                style="color: rgba(255,255,255,0.2); font-size: 6rem;"></i>
                            <i class="fa-regular fa-face-smile position-absolute"
                                style="color: rgba(255,255,255,0.9); font-size: 3.5rem;"></i>
                            <div class="position-absolute bg-white px-3 py-2 rounded-3 shadow-sm d-flex flex-column"
                                style="bottom: 12px; right: 12px;">
                                <small style="font-size: 0.6rem; color: #64748b; font-weight: 700;">Status</small>
                                <div class="d-flex align-items-center gap-1">
                                    <div style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%;">
                                    </div>
                                    <small class="fw-bold m-0"
                                        style="font-size: 0.75rem; color: #10b981;">Waspada</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card-new">
                        <div class="fc-badge fc-b-2 shadow-sm">02</div>
                        <div class="fc-icon fc-i-2"><i class="fa-solid fa-brain"></i></div>
                        <h4 class="fc-title">Penilaian Tingkat Kewaspadaan</h4>
                        <p class="fc-desc">Menganalisis indikator kantuk untuk menentukan kondisi pengemudi dan
                            memberikan peringatan sesuai tingkat risikonya.</p>
                        <div class="fc-visual-box bg-white border d-flex flex-column justify-content-end pb-3">
                            <div class="position-relative"
                                style="width: 120px; height: 60px; overflow: hidden; margin-top: 10px;">
                                <div
                                    style="width: 120px; height: 120px; border-radius: 50%; border: 18px solid #f1f5f9; border-top-color: #0ea5e9; border-right-color: #0ea5e9; transform: rotate(-45deg);">
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark mt-2 mb-0" style="font-size: 1.8rem;">72<span
                                    style="font-size: 1.2rem;">%</span></h3>
                            <small class="text-muted fw-bold" style="font-size: 0.75rem;">Tingkat Kewaspadaan</small>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card-new">
                        <div class="fc-badge fc-b-3 shadow-sm">03</div>
                        <div class="fc-icon fc-i-3"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h4 class="fc-title">Pemantauan Perjalanan</h4>
                        <p class="fc-desc">Menampilkan informasi perjalanan dan lokasi kendaraan untuk membantu memantau
                            kondisi berkendara secara lebih menyeluruh.</p>
                        <div class="fc-visual-box" style="background: #e0e7ff;">
                            <i class="fa-solid fa-route"
                                style="font-size: 5rem; color: #c7d2fe; position: absolute; left: 10px; top: 20px;"></i>
                            <div class="position-absolute bg-white px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2"
                                style="bottom: 12px; right: 12px;">
                                <i class="fa-solid fa-location-dot text-danger"></i>
                                <div>
                                    <small class="d-block fw-bold text-dark" style="font-size: 0.7rem;">Lokasi Saat
                                        Ini</small>
                                    <small class="d-block text-muted" style="font-size: 0.6rem;">Jalan Tol Trans Jawa KM
                                        123</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card-new">
                        <div class="fc-badge fc-b-4 shadow-sm">04</div>
                        <div class="fc-icon fc-i-4"><i class="fa-solid fa-truck-medical"></i></div>
                        <h4 class="fc-title">Respons Keadaan Darurat</h4>
                        <p class="fc-desc">Menyediakan mekanisme pemberitahuan kepada kontak darurat ketika kondisi
                            pengemudi terdeteksi membutuhkan bantuan.</p>
                        <div class="fc-visual-box p-3"
                            style="background: #fff1f2; border: 1px solid #ffe4e6; display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
                            <div class="d-flex align-items-center gap-2">
                                <div
                                    style="width: 36px; height: 36px; background: #fecdd3; color: #e11d48; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold m-0 text-dark" style="font-size: 0.8rem;">SOS Darurat</h6>
                                    <small
                                        style="font-size: 0.6rem; color: #64748b; line-height: 1.2; display: block;">Bantuan
                                        akan segera dikirim ke kontak darurat</small>
                                </div>
                            </div>
                            <i class="fa-solid fa-heart-pulse text-danger"
                                style="font-size: 1.5rem; opacity: 0.6; flex-shrink: 0;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CARA KERJA SECTION (HORIZONTAL 1 ROW REVISED) -->
    <section id="cara-kerja" class="section-padding timeline-section position-relative mt-5">
        <div
            style="position: absolute; top: 10%; right: 10%; width: 150px; height: 150px; background: rgba(255,255,255,0.5); border-radius: 50%; filter: blur(30px);">
        </div>
        <div
            style="position: absolute; bottom: 10%; left: 10%; width: 200px; height: 200px; background: rgba(14, 165, 233, 0.1); border-radius: 50%; filter: blur(50px);">
        </div>

        <!-- Container-fluid dengan px-4/px-lg-5 agar memanfaatkan ruang kosong samping -->
        <div class="container-fluid px-3 px-lg-5 position-relative z-2">
            <div class="row text-center mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="section-title">Bagaimana <span class="gradient-text-accent">AWAS</span> Bekerja?</h2>
                    <p class="section-subtitle mx-auto text-dark" style="max-width: 600px; opacity: 0.7;">
                        AWAS memantau kondisi pengemudi melalui kamera, mengenali tanda-tanda kantuk, kemudian
                        memberikan peringatan sesuai kondisi yang terdeteksi
                    </p>
                </div>
            </div>

            <!-- Row Horizontal -->
            <div class="timeline-horizontal-row mt-2 px-2">

                <div class="step-col-width px-2" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-box">
                        <div class="step-icon-wrapper"><i class="fa-solid fa-user-shield"></i></div>
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Identitas Pengemudi</h6>
                        <p class="text-gray m-0" style="font-size: 0.8rem; line-height: 1.5;">Pengguna masuk ke akun dan
                            menghubungkan data pengemudi dengan kendaraan yang digunakan untuk pemantauan</p>
                    </div>
                </div>

                <div class="step-col-width px-2" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-box">
                        <div class="step-icon-wrapper" style="background: var(--gradient-accent);"><i
                                class="fa-solid fa-camera"></i></div>
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Pemantauan Kondisi Pengemudi</h6>
                        <p class="text-gray m-0" style="font-size: 0.8rem; line-height: 1.5;">Kamera memantau wajah dan
                            kondisi mata pengemudi secara real-time untuk mengenali tanda-tanda kantuk</p>
                    </div>
                </div>

                <div class="step-col-width px-2" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-box">
                        <div class="step-icon-wrapper" style="background: var(--gradient-sunset);"><i
                                class="fa-solid fa-chart-line"></i></div>
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Analisis Tingkat Kantuk</h6>
                        <p class="text-gray m-0" style="font-size: 0.8rem; line-height: 1.5;">Sistem menganalisis
                            indikator kantuk dan menentukan tingkat kewaspadaan pengemudi berdasarkan kondisi yang
                            terdeteksi</p>
                    </div>
                </div>

                <div class="step-col-width px-2" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-box">
                        <div class="step-icon-wrapper"
                            style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);"><i
                                class="fa-solid fa-bell"></i></div>
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Peringatan Bertahap</h6>
                        <p class="text-gray m-0" style="font-size: 0.8rem; line-height: 1.5;">Ketika tanda-tanda kantuk
                            terdeteksi, sistem memberikan peringatan sesuai tingkat kondisi pengemudi</p>
                    </div>
                </div>

                <div class="step-col-width px-2" data-aos="fade-up" data-aos-delay="500">
                    <div class="step-box">
                        <div class="step-icon-wrapper"
                            style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);"><i
                                class="fa-solid fa-bed"></i></div>
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Pemantauan Perjalanan</h6>
                        <p class="text-gray m-0" style="font-size: 0.8rem; line-height: 1.5;">Menampilkan informasi
                            perjalanan seperti lokasi kendaraan, rute, durasi, dan titik istirahat yang dapat digunakan
                            pengemudi ketika membutuhkan jeda perjalanan</p>
                    </div>
                </div>

                <div class="step-col-width px-2" data-aos="fade-up" data-aos-delay="600">
                    <div class="step-box">
                        <div class="step-icon-wrapper"
                            style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);"><i
                                class="fa-solid fa-truck-medical"></i></div>
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Respons Keadaan Darurat</h6>
                        <p class="text-gray m-0" style="font-size: 0.8rem; line-height: 1.5;">Jika pengemudi berada
                            dalam kondisi kritis dan tidak memberikan respons setelah peringatan diberikan, sistem dapat
                            meneruskan informasi kejadian dan lokasi kepada kontak darurat yang telah terdaftar</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- ========================================================
         LIVE TRACKING MAP SECTION (REDESIGN SESUAI GAMBAR 1)
         ======================================================== -->
    <section id="live-tracking" class="section-padding position-relative" style="background-color: #f4f7fa;">
        <div class="container">
            <div class="row align-items-center mb-5" data-aos="fade-up">

                <!-- BAGIAN KIRI: Teks & Fitur -->
                <div class="col-lg-5 mb-5 mb-lg-0 pe-lg-4">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white text-primary mb-4 shadow-sm"
                        style="font-size: 0.85rem; font-weight: 600; border: 1px solid #e0e7ff;">
                    </div>

                    <h2 class="section-title mb-4" style="font-size: 2.8rem; line-height: 1.2;">
                        Pantau Perjalanan,<br>Utamakan <span style="color: var(--primary-color);">Keselamatan.</span>
                    </h2>

                    <p class="text-secondary mb-5" style="font-size: 1.05rem; line-height: 1.7;">
                        AWAS 2.0 membantu memantau kendaraan, kecepatan, dan status pengemudi secara real-time dengan
                        peta interaktif yang modern dan detail hingga jalan-jalan kecil.
                    </p>

                    <div class="row g-4">
                        <!-- Fitur 1 -->
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-white text-primary d-flex align-items-center justify-content-center shadow-sm rounded-circle"
                                    style="width: 45px; height: 45px; flex-shrink: 0; font-size: 1.2rem;">
                                    <i class="fa-solid fa-car-side"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Live Tracking</h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">Lokasi real-time</small>
                                </div>
                            </div>
                        </div>
                        <!-- Fitur 2 -->
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-white text-success d-flex align-items-center justify-content-center shadow-sm rounded-circle"
                                    style="width: 45px; height: 45px; flex-shrink: 0; font-size: 1.2rem;">
                                    <i class="fa-solid fa-gauge-high"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Pantau Kecepatan</h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">Monitoring kecepatan</small>
                                </div>
                            </div>
                        </div>
                        <!-- Fitur 3 -->
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-white text-warning d-flex align-items-center justify-content-center shadow-sm rounded-circle"
                                    style="width: 45px; height: 45px; flex-shrink: 0; font-size: 1.2rem;">
                                    <i class="fa-regular fa-bell"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Peringatan Otomatis</h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">Deteksi risiko</small>
                                </div>
                            </div>
                        </div>
                        <!-- Fitur 4 -->
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-white d-flex align-items-center justify-content-center shadow-sm rounded-circle"
                                    style="width: 45px; height: 45px; flex-shrink: 0; color: #8b5cf6; font-size: 1.2rem;">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Keamanan Maksimal</h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">Perjalanan lebih aman</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN KANAN: Peta -->
                <div class="col-lg-7" data-aos="fade-left" data-aos-delay="200">
                    <div class="position-relative bg-white p-2 rounded-4 shadow-lg" style="border: 1px solid #e2e8f0;">
                        <!-- Peta Container -->
                        <div class="map-wrapper"
                            style="height: 480px; border-radius: 1rem; overflow: hidden; position: relative;">
                            <div id="tracking-map" style="width: 100%; height: 100%; z-index: 1;"></div>

                            <!-- Badge Live Top Left -->
                            <div class="position-absolute bg-white px-3 py-2 shadow-sm d-flex align-items-center gap-2"
                                style="top: 20px; left: 20px; border-radius: 50rem; z-index: 10; border: 1px solid #f1f5f9;">
                                <div style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%;"></div>
                                <span class="text-muted" style="font-size: 0.85rem;">Terhubung</span>
                            </div>

                            <!-- Overlay Card Bottom (TANPA NAMA IDENTITAS) -->
                            <div class="position-absolute bg-white p-3 shadow-lg d-flex align-items-center justify-content-between flex-wrap gap-3"
                                style="bottom: 20px; left: 50%; transform: translateX(-50%); width: 92%; border-radius: 16px; z-index: 10;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm"
                                        style="width: 50px; height: 50px; font-size: 1.2rem;">
                                        <i class="fa-solid fa-location-crosshairs"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">Posisi Saat Ini</h6>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-4">
                                    <div class="border-start ps-3">
                                        <small class="text-muted d-block mb-1"
                                            style="font-size: 0.75rem;">Kecepatan</small>
                                        <h6 class="fw-bold m-0" style="font-size: 1.1rem;">65 <span
                                                style="font-size: 0.8rem; font-weight: normal; color: #64748b;">km/h</span>
                                        </h6>
                                    </div>
                                    <div class="border-start ps-3">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Sisa
                                            Waktu</small>
                                        <h6 class="fw-bold m-0" style="font-size: 1.1rem;">15 <span
                                                style="font-size: 0.8rem; font-weight: normal; color: #64748b;">mnt</span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Banner -->
            <div class="bg-white rounded-4 shadow-sm p-4 mt-2" data-aos="fade-up" style="border: 1px solid #e2e8f0;">
                <div class="row text-center text-md-start align-items-center g-4">
                    <div class="col-md-4 d-flex align-items-center justify-content-md-start justify-content-center gap-3 px-4"
                        style="border-right: 1px solid #e2e8f0;">
                        <div class="text-primary bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="fa-solid fa-shield-halved fs-5"></i>
                        </div>
                        <div class="text-start">
                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">Sistem Monitoring Real-time
                            </h6>
                            <small class="text-muted" style="font-size: 0.8rem; line-height: 1.4; display: block;">Data
                                diperbarui setiap detik untuk memberikan informasi terkini.</small>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-md-start justify-content-center gap-3 px-4"
                        style="border-right: 1px solid #e2e8f0;">
                        <div class="text-success bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="fa-solid fa-phone-volume fs-5"></i>
                        </div>
                        <div class="text-start">
                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">Peringatan Darurat Otomatis
                            </h6>
                            <small class="text-muted"
                                style="font-size: 0.8rem; line-height: 1.4; display: block;">Sistem akan menghubungi
                                kontak darurat jika terjadi kondisi berisiko.</small>
                        </div>
                    </div>
                    <div
                        class="col-md-4 d-flex align-items-center justify-content-md-start justify-content-center gap-3 px-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px; flex-shrink: 0; color: #8b5cf6; background: rgba(139, 92, 246, 0.1);">
                            <i class="fa-regular fa-map fs-5"></i>
                        </div>
                        <div class="text-start">
                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">Peta Interaktif Detail</h6>
                            <small class="text-muted"
                                style="font-size: 0.8rem; line-height: 1.4; display: block;">Menampilkan rumah, toko,
                                dan jalan dengan lebih lengkap.</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Bottom Banner -->

        </div>
    </section>

    <!-- ========================================================
         FITUR PENGADUAN & FAQ SIDE-BY-SIDE SECTION 
         ======================================================== -->
    <section id="pengaduan-faq" class="section-padding position-relative" style="background-color: #f8fafc;">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="section-title">Pusat Bantuan & <span class="gradient-text">Pengaduan</span></h2>
                    <p class="section-subtitle mx-auto" style="max-width: 600px;">
                        Sampaikan kendala teknis Anda atau temukan jawaban instan seputar penggunaan sistem AWAS
                    </p>
                </div>
            </div>

            <div class="row g-4 align-items-stretch">
                <!-- KIRI: FORM PENGADUAN -->
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="card-custom-new d-flex flex-column">
                        <h4 class="heading-blue">Formulir Pengaduan</h4>
                        <p class="subheading-new">Kirimkan laporan atau saran Anda</p>

                        <form action="#" method="POST" class="d-flex flex-column flex-grow-1">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label-new">Nama Lengkap</label>
                                    <input type="text" class="form-control form-control-new"
                                        placeholder="Masukkan nama lengkap" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-new">Alamat Email</label>
                                    <input type="email" class="form-control form-control-new"
                                        placeholder="Masukkan email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-new">Kategori Laporan</label>
                                <select class="form-select form-control-new" required>
                                    <option value="" disabled selected>Pilih kategori laporan</option>
                                    <option value="error">Sistem Error / Deteksi Wajah Gagal</option>
                                    <option value="gps">Kendala GPS / Tracking Route</option>
                                    <option value="akun">Masalah Login / Pemulihan Akun</option>
                                    <option value="lainnya">Saran & Masukan Lainnya</option>
                                </select>
                            </div>

                            <div class="mb-4 flex-grow-1 d-flex flex-column">
                                <label class="form-label-new">Detail Pengaduan</label>
                                <textarea class="form-control form-control-new flex-grow-1" style="min-height: 120px;"
                                    placeholder="Ceritakan detail kendala secara spesifik..." required></textarea>
                                <div class="text-end mt-1 text-muted" style="font-size: 0.75rem;">0 / 1000</div>
                            </div>

                            <button type="submit" class="btn-submit-new mt-auto">
                                Kirim Laporan <i class="fa-solid fa-paper-plane ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- KANAN: FAQ ACCORDION -->
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-custom-new d-flex flex-column">
                        <h4 class="heading-blue">Pertanyaan Umum (FAQ)</h4>
                        <p class="subheading-new">Solusi cepat untuk Anda</p>

                        <div class="accordion accordion-new flex-grow-1" id="accordionFAQ">
                            <!-- FAQ Item 1 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        Bagaimana cara kerja deteksi kantuk AI?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                    data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Sistem melacak <i>Eye Aspect Ratio</i> (EAR) dan pergerakan mulut secara
                                        real-time via kamera dashboard.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                        Apakah butuh perangkat keras khusus?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse show"
                                    aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Tidak, cukup gunakan smartphone Android/iOS di dashboard atau head-unit yang
                                        kompatibel.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseThree" aria-expanded="false"
                                        aria-controls="collapseThree">
                                        Bagaimana kondisi dalam mobil gelap?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse"
                                    aria-labelledby="headingThree" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Dilatih dengan dataset <i>low-light</i>, atau disarankan menggunakan aksesoris
                                        IR (Inframerah) opsional.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseFour" aria-expanded="false"
                                        aria-controls="collapseFour">
                                        Siapa penerima notifikasi SOS?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                    data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Kontak darurat pilihan Anda, dikirim otomatis berupa SMS dan link Google Maps
                                        koordinat terakhir.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bantuan Tambahan Box -->
                        <div class="help-box-new mt-auto">
                            <div class="d-flex align-items-center gap-3">
                                <div
                                    style="background: #2563eb; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-info"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: #1e3a8a; font-size: 0.9rem;">Masih memiliki
                                        pertanyaan?</h6>
                                    <p class="mb-0 text-muted" style="font-size: 0.8rem;">Kunjungi halaman
                                        <b>Bantuan</b> untuk informasi lebih lengkap.
                                    </p>
                                </div>
                            </div>
                            <a href="#" class="btn-help text-nowrap ms-2">Buka Bantuan <i
                                    class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER AESTHETIC -->
    <footer class="footer-aesthetic">
        <!-- SVG Wave -->
        <div class="footer-wave">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"
                preserveAspectRatio="none">
                <path
                    d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"
                    class="shape-fill"></path>
            </svg>
        </div>

        <div class="container">
            <div class="row g-4" data-aos="fade-up">
                <!-- Column 1: Logo & Info -->
                <div class="col-lg-4 pe-lg-5 mb-4 mb-lg-0">
                    <h4 class="fw-bold text-white mb-3 d-flex align-items-center gap-2"
                        style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        <span style="color: #faf6f7;">AWAS</span><span style="color: #f43f5e;">.</span>
                    </h4>
                    <p class="mb-4" style="color: rgba(255, 255, 255, 0.75); font-size: 0.85rem; line-height: 1.6;">
                        Semua konten di website ini dilindungi oleh hak cipta dan tidak boleh digunakan tanpa izin dari
                        AWAS
                    </p>
                    <div class="d-flex">
                        <a href="#" class="footer-social-box"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="footer-social-box"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="footer-social-box"><i class="fa-brands fa-twitter"></i></a>
                    </div>
                </div>

                <!-- Column 2: Navigasi -->
                <div class="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0">
                    <h6 class="mb-3">Navigasi</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#tentang">Keunggulan</a></li>
                        <li><a href="#cara-kerja">Alur Sistem</a></li>
                        <li><a href="#live-tracking">Peta Live</a></li>
                        <li><a href="#pengaduan-faq">Bantuan</a></li>
                    </ul>
                </div>

                <!-- Column 3: Legal -->
                <div class="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0">
                    <h6 class="mb-3">Legal</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#">Privasi & Keamanan</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                        <li><a href="#">Panduan Pengguna</a></li>
                        <li><a href="#">Pembaruan</a></li>
                    </ul>
                </div>

                <!-- Column 4: Kontak -->
                <div class="col-lg-4 col-md-4 mb-4 mb-lg-0">
                    <h6 class="mb-3">Kontak</h6>
                    <div class="d-flex flex-column gap-3">
                        <a href="#" class="contact-info d-flex align-items-center">
                            <i class="fa-solid fa-phone"></i> +123 456 7890
                        </a>
                        <a href="#" class="contact-info d-flex align-items-center">
                            <i class="fa-solid fa-envelope"></i> support@awas.com
                        </a>
                        <div class="contact-info d-flex align-items-start">
                            <i class="fa-solid fa-location-dot mt-1"></i>
                            <span>Gedung IT Pusat,<br>Area Kampus Akademik</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom mt-5">
                <!-- Back to Top Button -->
                <a href="#beranda" class="back-to-top-btn" title="Back to Top">
                    <i class="fa-solid fa-arrow-up"></i>
                </a>
                <p class="mb-0 pt-2" style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">
                    Copyright &copy; 2026 AWAS 2.0. All Rights Reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Script Inisialisasi & Navbar Effect -->
    <script>
        // Inisialisasi AOS (Animate On Scroll)
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });

        // Script efek transisi navbar saat scroll
        window.addEventListener('scroll', function () {
            var navbar = document.querySelector('.navbar-custom');

            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 15px 30px rgba(67, 56, 202, 0.08)';
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.borderBottom = '1px solid #e2e8f0';
            } else {
                navbar.style.boxShadow = '0 4px 15px -3px rgba(67, 56, 202, 0.08)';
                navbar.style.background = 'rgba(255, 255, 255, 0.85)';
                navbar.style.borderBottom = '1px solid rgba(255, 255, 255, 0.9)';
            }
        });

        // Fungsi untuk menutup custom GPS Alert
        function closeGpsAlert() {
            document.getElementById('customGpsAlert').classList.remove('show');
        }

        // ==========================================
        // SCRIPT INISIALISASI LIVE TRACKING MAP & PERINGATAN LOKASI
        // ==========================================
        document.addEventListener("DOMContentLoaded", function () {

            // Menampilkan custom pop up alert dengan sedikit delay untuk efek transisi
            setTimeout(function () {
                document.getElementById('customGpsAlert').classList.add('show');
            }, 600); // Muncul dalam 0.6 detik setelah halaman dimuat

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        console.log("Lokasi diizinkan: Lat " + position.coords.latitude + ", Lng " + position.coords.longitude);
                    },
                    function (error) {
                        console.warn("Lokasi tidak diizinkan atau gagal diambil.");
                    }
                );
            }

            // Inisialisasi map dengan level zoom di-tingkatkan (16) agar rumah dan bangunan terlihat sangat jelas
            var map = L.map('tracking-map', {
                zoomControl: false
            }).setView([-7.1311, 112.7277], 16);

            // MENGGUNAKAN OPENSTREETMAP STANDAR AGAR SANGAT DETAIL (Toko, Jalan Kecil, Rumah terlihat jelas)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            L.control.zoom({
                position: 'topright'
            }).addTo(map);

            // Icon Marker GPS
            var customIcon = L.divIcon({
                className: 'custom-gps-marker',
                html: `
                    <div style="position: relative; width: 20px; height: 20px;">
                        <div class="gps-ping"></div>
                        <div style="width: 20px; height: 20px; background-color: var(--primary-color); border: 3px solid white; border-radius: 50%; position: absolute; box-shadow: 0 0 10px rgba(0,0,0,0.4); z-index: 2;"></div>
                    </div>
                `,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            // Menambahkan marker ke map
            var marker = L.marker([-7.1311, 112.7277], { icon: customIcon }).addTo(map);

            // GARIS UNGU/ROUTE DIHAPUS SESUAI INSTRUKSI!

            // Animasi kecil di awal agar terlihat smooth (tanpa mengikuti rute)
            setTimeout(() => {
                map.panBy([0, 40], { animate: true, duration: 1.5 });
            }, 1500);

            setTimeout(() => {
                map.invalidateSize();
            }, 500);
        });
    </script>
</body>

</html>