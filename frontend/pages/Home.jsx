import React, { useEffect } from 'react';
import Navbar from '../components/Navbar';
import AOS from 'aos';
import 'aos/dist/aos.css';

import '../assets/css/style.css';

const Home = () => {
    useEffect(() => {
        AOS.init({ once: true, offset: 50, duration: 800, easing: 'ease-out-cubic' });

        setTimeout(() => {
            const alertBox = document.getElementById('customGpsAlert');
            if (alertBox) alertBox.classList.add('show');
        }, 600);

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => console.log("Lokasi diizinkan:", position),
                (error) => console.warn("Lokasi gagal diambil.")
            );
        }

        // Inisialisasi Peta Leaflet
        const mapContainer = document.getElementById('tracking-map');
        if (mapContainer && !mapContainer._leaflet_id && window.L) {
            const map = window.L.map('tracking-map', { zoomControl: false }).setView([-7.1311, 112.7277], 16);
            window.mapInstance = map; 

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(map);

            window.L.control.zoom({ position: 'topright' }).addTo(map);

            const customIcon = window.L.divIcon({
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

            window.L.marker([-7.1311, 112.7277], { icon: customIcon }).addTo(map);

            setTimeout(() => map.panBy([0, 40], { animate: true, duration: 1.5 }), 1500);
            setTimeout(() => map.invalidateSize(), 500);
        }

        return () => {
            if (window.mapInstance) {
                window.mapInstance.remove();
                window.mapInstance = null;
            }
        };
    }, []);

    const closeGpsAlert = () => {
        const alertBox = document.getElementById('customGpsAlert');
        if (alertBox) alertBox.classList.remove('show');
    };

    return (
        <>
            {/* CUSTOM GPS ALERT MODAL */}
            <div id="customGpsAlert" className="custom-alert-overlay">
                <div className="custom-alert-box">
                    <div className="alert-icon-wrapper">
                        <div className="alert-pulse"></div>
                        <i className="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h3 className="alert-title">PERINGATAN AWAS 2.0</h3>
                    <p className="alert-message">
                        Harap pastikan Anda <b>mengaktifkan fitur lokasi (GPS)</b> pada perangkat Anda agar
                        sistem dapat memantau perjalanan dan mengaktifkan fitur Live Tracking dengan akurat.
                    </p>
                    <button className="alert-btn" onClick={closeGpsAlert}>Mengerti & Izinkan</button>
                </div>
            </div>

            {/* NAVBAR KOMPONEN */}
            <Navbar />

            {/* HERO SECTION */}
            <section id="beranda" className="hero-section">
                <div className="blob-1 hero-blob"></div>
                <div className="blob-2 hero-blob"></div>
                <div className="blob-3 hero-blob"></div>

                <div className="container">
                    <div className="row align-items-center min-vh-75">
                        <div className="col-lg-7 hero-content text-center text-lg-start mb-5 mb-lg-0" data-aos="fade-up"
                            data-aos-duration="1000" style={{ position: 'relative', zIndex: 2 }}>
                            <h1 className="hero-title">Sistem Keselamatan Pengemudi Berbasis <span className="gradient-text">Computer Vision</span></h1>
                            <p className="hero-subtitle mx-auto mx-lg-0">
                                AWAS membantu memantau kondisi pengemudi selama perjalanan melalui kamera. Sistem mengenali tanda-tanda kantuk dan memberikan peringatan ketika tingkat kewaspadaan mulai menurun
                            </p>

                            <div className="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                                <a href="#auth-section" onClick={() => document.getElementById('pills-register-tab')?.click()}
                                    className="btn-hero-primary text-decoration-none text-center">
                                    Mulai Pemantauan
                                </a>
                                <a href="#cara-kerja"
                                    className="btn-hero-secondary text-decoration-none text-center d-flex align-items-center justify-content-center gap-2">
                                    <i className="fa-solid fa-circle-play text-primary fs-5"></i> Lihat Cara Kerja
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* TENTANG SECTION */}
            <section id="tentang" className="section-padding" style={{ backgroundColor: '#f8fafc' }}>
                <div className="container">
                    <div className="row align-items-center mb-5">
                        <div className="col-lg-6 text-start mb-4 mb-lg-0" data-aos="fade-right">
                            <h2 className="section-title text-start mb-3"
                                style={{ fontSize: '3.5rem', lineHeight: 1.1, fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                                Mengapa<br /><span style={{ color: '#4338ca' }}>AWAS?</span>
                            </h2>
                            <div style={{ width: '50px', height: '4px', background: '#4338ca', marginBottom: '24px', borderRadius: '2px' }}></div>
                            <p className="section-subtitle text-start m-0" style={{ maxWidth: '90%', fontSize: '1.05rem' }}>
                                AWAS dirancang untuk membantu pengemudi mengenali tanda-tanda kantuk sejak dini melalui
                                pemantauan berbasis computer vision dan memberikan peringatan ketika kewaspadaan mulai menurun.
                            </p>
                        </div>

                        <div className="col-lg-6" data-aos="fade-left">
                            <div className="cv-illustration-wrapper">
                                <div style={{ position: 'absolute', width: '200px', height: '200px', background: 'rgba(99, 102, 241, 0.2)', borderRadius: '50%', filter: 'blur(40px)', top: '-50px', right: '-50px' }}></div>
                                <div style={{ position: 'absolute', width: '150px', height: '150px', background: 'rgba(14, 165, 233, 0.2)', borderRadius: '50%', filter: 'blur(40px)', bottom: '-30px', left: '-30px' }}></div>

                                <div className="cv-dashboard">
                                    <div className="cv-camera-feed">
                                        <i className="fa-solid fa-user-tie cv-face"></i>
                                        <div className="cv-bounding-box">
                                            <div className="cv-eye-track cv-eye-left"></div>
                                            <div className="cv-eye-track cv-eye-right"></div>
                                            <div className="cv-mouth-track"></div>
                                            <div className="cv-scan-line"></div>
                                            <div className="cv-corner cv-c-tl"></div>
                                            <div className="cv-corner cv-c-tr"></div>
                                            <div className="cv-corner cv-c-bl"></div>
                                            <div className="cv-corner cv-c-br"></div>
                                        </div>
                                        <div style={{ position: 'absolute', top: '12px', right: '12px', display: 'flex', alignItems: 'center', gap: '5px' }}>
                                            <div style={{ width: '8px', height: '8px', background: '#ef4444', borderRadius: '50%', animation: 'blinkPulse 1s infinite' }}></div>
                                            <span style={{ color: 'rgba(255,255,255,0.9)', fontSize: '0.6rem', fontWeight: 'bold', letterSpacing: '1px' }}>REC</span>
                                        </div>
                                    </div>

                                    <div className="cv-stats">
                                        <div className="cv-stat-box">
                                            <div style={{ fontSize: '0.6rem', color: '#64748b', fontWeight: 'bold' }}>STATUS</div>
                                            <div style={{ fontSize: '0.85rem', color: '#10b981', fontWeight: 800, display: 'flex', alignItems: 'center', gap: '5px', marginTop: '2px' }}>
                                                <i className="fa-solid fa-shield-check"></i> Waspada
                                            </div>
                                        </div>
                                        <div className="cv-stat-box">
                                            <div style={{ fontSize: '0.6rem', color: '#64748b', fontWeight: 'bold' }}>EAR (Mata)</div>
                                            <div style={{ fontSize: '0.85rem', color: '#0f172a', fontWeight: 800, marginTop: '2px' }}>0.32</div>
                                            <div className="cv-stat-bar">
                                                <div className="cv-stat-fill" style={{ width: '85%' }}></div>
                                            </div>
                                        </div>
                                        <div className="cv-stat-box">
                                            <div style={{ fontSize: '0.6rem', color: '#64748b', fontWeight: 'bold' }}>MAR (Mulut)</div>
                                            <div style={{ fontSize: '0.85rem', color: '#0f172a', fontWeight: 800, marginTop: '2px' }}>0.15</div>
                                            <div className="cv-stat-bar">
                                                <div className="cv-stat-fill" style={{ width: '30%', background: '#0ea5e9' }}></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="row g-4 mb-5">
                        <div className="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                            <div className="feature-card-new">
                                <div className="fc-badge fc-b-1 shadow-sm">01</div>
                                <div className="fc-icon fc-i-1"><i className="fa-solid fa-eye"></i></div>
                                <h4 className="fc-title">Deteksi Tanda Kantuk</h4>
                                <p className="fc-desc">Memantau kondisi mata dan wajah pengemudi secara real-time untuk mengenali tanda-tanda penurunan kewaspadaan.</p>
                                <div className="fc-visual-box" style={{ background: 'linear-gradient(135deg, #4338ca 0%, #6366f1 100%)' }}>
                                    <i className="fa-solid fa-expand position-absolute" style={{ color: 'rgba(255,255,255,0.2)', fontSize: '6rem' }}></i>
                                    <i className="fa-regular fa-face-smile position-absolute" style={{ color: 'rgba(255,255,255,0.9)', fontSize: '3.5rem' }}></i>
                                    <div className="position-absolute bg-white px-3 py-2 rounded-3 shadow-sm d-flex flex-column" style={{ bottom: '12px', right: '12px' }}>
                                        <small style={{ fontSize: '0.6rem', color: '#64748b', fontWeight: 700 }}>Status</small>
                                        <div className="d-flex align-items-center gap-1">
                                            <div style={{ width: '8px', height: '8px', background: '#22c55e', borderRadius: '50%' }}></div>
                                            <small className="fw-bold m-0" style={{ fontSize: '0.75rem', color: '#10b981' }}>Waspada</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                            <div className="feature-card-new">
                                <div className="fc-badge fc-b-2 shadow-sm">02</div>
                                <div className="fc-icon fc-i-2"><i className="fa-solid fa-brain"></i></div>
                                <h4 className="fc-title">Penilaian Tingkat Kewaspadaan</h4>
                                <p className="fc-desc">Menganalisis indikator kantuk untuk menentukan kondisi pengemudi dan memberikan peringatan sesuai tingkat risikonya.</p>
                                <div className="fc-visual-box bg-white border d-flex flex-column justify-content-end pb-3">
                                    <div className="position-relative" style={{ width: '120px', height: '60px', overflow: 'hidden', marginTop: '10px' }}>
                                        <div style={{ width: '120px', height: '120px', borderRadius: '50%', border: '18px solid #f1f5f9', borderTopColor: '#0ea5e9', borderRightColor: '#0ea5e9', transform: 'rotate(-45deg)' }}></div>
                                    </div>
                                    <h3 className="fw-bold text-dark mt-2 mb-0" style={{ fontSize: '1.8rem' }}>72<span style={{ fontSize: '1.2rem' }}>%</span></h3>
                                    <small className="text-muted fw-bold" style={{ fontSize: '0.75rem' }}>Tingkat Kewaspadaan</small>
                                </div>
                            </div>
                        </div>

                        <div className="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                            <div className="feature-card-new">
                                <div className="fc-badge fc-b-3 shadow-sm">03</div>
                                <div className="fc-icon fc-i-3"><i className="fa-solid fa-map-location-dot"></i></div>
                                <h4 className="fc-title">Pemantauan Perjalanan</h4>
                                <p className="fc-desc">Menampilkan informasi perjalanan dan lokasi kendaraan untuk membantu memantau kondisi berkendara secara lebih menyeluruh.</p>
                                <div className="fc-visual-box" style={{ background: '#e0e7ff' }}>
                                    <i className="fa-solid fa-route" style={{ fontSize: '5rem', color: '#c7d2fe', position: 'absolute', left: '10px', top: '20px' }}></i>
                                    <div className="position-absolute bg-white px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" style={{ bottom: '12px', right: '12px' }}>
                                        <i className="fa-solid fa-location-dot text-danger"></i>
                                        <div>
                                            <small className="d-block fw-bold text-dark" style={{ fontSize: '0.7rem' }}>Lokasi Saat Ini</small>
                                            <small className="d-block text-muted" style={{ fontSize: '0.6rem' }}>Jalan Tol Trans Jawa KM 123</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                            <div className="feature-card-new">
                                <div className="fc-badge fc-b-4 shadow-sm">04</div>
                                <div className="fc-icon fc-i-4"><i className="fa-solid fa-truck-medical"></i></div>
                                <h4 className="fc-title">Respons Keadaan Darurat</h4>
                                <p className="fc-desc">Menyediakan mekanisme pemberitahuan kepada kontak darurat ketika kondisi pengemudi terdeteksi membutuhkan bantuan.</p>
                                <div className="fc-visual-box p-3" style={{ background: '#fff1f2', border: '1px solid #ffe4e6', display: 'flex', flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
                                    <div className="d-flex align-items-center gap-2">
                                        <div style={{ width: '36px', height: '36px', background: '#fecdd3', color: '#e11d48', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                            <i className="fa-solid fa-bell"></i>
                                        </div>
                                        <div>
                                            <h6 className="fw-bold m-0 text-dark" style={{ fontSize: '0.8rem' }}>SOS Darurat</h6>
                                            <small style={{ fontSize: '0.6rem', color: '#64748b', lineHeight: 1.2, display: 'block' }}>Bantuan akan segera dikirim ke kontak darurat</small>
                                        </div>
                                    </div>
                                    <i className="fa-solid fa-heart-pulse text-danger" style={{ fontSize: '1.5rem', opacity: 0.6, flexShrink: 0 }}></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* CARA KERJA SECTION */}
            <section id="cara-kerja" className="section-padding timeline-section position-relative mt-5">
                <div style={{ position: 'absolute', top: '10%', right: '10%', width: '150px', height: '150px', background: 'rgba(255,255,255,0.5)', borderRadius: '50%', filter: 'blur(30px)' }}></div>
                <div style={{ position: 'absolute', bottom: '10%', left: '10%', width: '200px', height: '200px', background: 'rgba(14, 165, 233, 0.1)', borderRadius: '50%', filter: 'blur(50px)' }}></div>

                <div className="container-fluid px-3 px-lg-5 position-relative z-2">
                    <div className="row text-center mb-5" data-aos="fade-up">
                        <div className="col-12">
                            <h2 className="section-title">Bagaimana <span className="gradient-text-accent">AWAS</span> Bekerja?</h2>
                            <p className="section-subtitle mx-auto text-dark" style={{ maxWidth: '600px', opacity: 0.7 }}>
                                AWAS memantau kondisi pengemudi melalui kamera, mengenali tanda-tanda kantuk, kemudian memberikan peringatan sesuai kondisi yang terdeteksi
                            </p>
                        </div>
                    </div>

                    <div className="timeline-horizontal-row mt-2 px-2">
                        <div className="step-col-width px-2" data-aos="fade-up" data-aos-delay="100">
                            <div className="step-box">
                                <div className="step-icon-wrapper"><i className="fa-solid fa-user-shield"></i></div>
                                <h6 className="fw-bold text-dark mb-2" style={{ fontSize: '0.95rem' }}>Identitas Pengemudi</h6>
                                <p className="text-gray m-0" style={{ fontSize: '0.8rem', lineHeight: 1.5 }}>Pengguna masuk ke akun dan menghubungkan data pengemudi dengan kendaraan yang digunakan untuk pemantauan</p>
                            </div>
                        </div>

                        <div className="step-col-width px-2" data-aos="fade-up" data-aos-delay="200">
                            <div className="step-box">
                                <div className="step-icon-wrapper" style={{ background: 'var(--gradient-accent)' }}><i className="fa-solid fa-camera"></i></div>
                                <h6 className="fw-bold text-dark mb-2" style={{ fontSize: '0.95rem' }}>Pemantauan Kondisi Pengemudi</h6>
                                <p className="text-gray m-0" style={{ fontSize: '0.8rem', lineHeight: 1.5 }}>Kamera memantau wajah dan kondisi mata pengemudi secara real-time untuk mengenali tanda-tanda kantuk</p>
                            </div>
                        </div>

                        <div className="step-col-width px-2" data-aos="fade-up" data-aos-delay="300">
                            <div className="step-box">
                                <div className="step-icon-wrapper" style={{ background: 'var(--gradient-sunset)' }}><i className="fa-solid fa-chart-line"></i></div>
                                <h6 className="fw-bold text-dark mb-2" style={{ fontSize: '0.95rem' }}>Analisis Tingkat Kantuk</h6>
                                <p className="text-gray m-0" style={{ fontSize: '0.8rem', lineHeight: 1.5 }}>Sistem menganalisis indikator kantuk dan menentukan tingkat kewaspadaan pengemudi berdasarkan kondisi yang terdeteksi</p>
                            </div>
                        </div>

                        <div className="step-col-width px-2" data-aos="fade-up" data-aos-delay="400">
                            <div className="step-box">
                                <div className="step-icon-wrapper" style={{ background: 'linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%)' }}><i className="fa-solid fa-bell"></i></div>
                                <h6 className="fw-bold text-dark mb-2" style={{ fontSize: '0.95rem' }}>Peringatan Bertahap</h6>
                                <p className="text-gray m-0" style={{ fontSize: '0.8rem', lineHeight: 1.5 }}>Ketika tanda-tanda kantuk terdeteksi, sistem memberikan peringatan sesuai tingkat kondisi pengemudi</p>
                            </div>
                        </div>

                        <div className="step-col-width px-2" data-aos="fade-up" data-aos-delay="500">
                            <div className="step-box">
                                <div className="step-icon-wrapper" style={{ background: 'linear-gradient(135deg, #10b981 0%, #34d399 100%)' }}><i className="fa-solid fa-bed"></i></div>
                                <h6 className="fw-bold text-dark mb-2" style={{ fontSize: '0.95rem' }}>Pemantauan Perjalanan</h6>
                                <p className="text-gray m-0" style={{ fontSize: '0.8rem', lineHeight: 1.5 }}>Menampilkan informasi perjalanan seperti lokasi kendaraan, rute, durasi, dan titik istirahat yang dapat digunakan pengemudi ketika membutuhkan jeda perjalanan</p>
                            </div>
                        </div>

                        <div className="step-col-width px-2" data-aos="fade-up" data-aos-delay="600">
                            <div className="step-box">
                                <div className="step-icon-wrapper" style={{ background: 'linear-gradient(135deg, #ef4444 0%, #f87171 100%)' }}><i className="fa-solid fa-truck-medical"></i></div>
                                <h6 className="fw-bold text-dark mb-2" style={{ fontSize: '0.95rem' }}>Respons Keadaan Darurat</h6>
                                <p className="text-gray m-0" style={{ fontSize: '0.8rem', lineHeight: 1.5 }}>Jika pengemudi berada dalam kondisi kritis dan tidak memberikan respons setelah peringatan diberikan, sistem dapat meneruskan informasi kejadian dan lokasi kepada kontak darurat yang telah terdaftar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* LIVE TRACKING MAP SECTION */}
            <section id="live-tracking" className="section-padding position-relative" style={{ backgroundColor: '#f4f7fa' }}>
                <div className="container">
                    <div className="row align-items-center mb-5" data-aos="fade-up">

                        {/* BAGIAN KIRI: Teks & Fitur */}
                        <div className="col-lg-5 mb-5 mb-lg-0 pe-lg-4">
                            <div className="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white text-primary mb-4 shadow-sm"
                                style={{ fontSize: '0.85rem', fontWeight: 600, border: '1px solid #e0e7ff' }}>
                            </div>

                            <h2 className="section-title mb-4" style={{ fontSize: '2.8rem', lineHeight: 1.2 }}>
                                Pantau Perjalanan,<br />Utamakan <span style={{ color: 'var(--primary-color)' }}>Keselamatan.</span>
                            </h2>

                            <p className="text-secondary mb-5" style={{ fontSize: '1.05rem', lineHeight: 1.7 }}>
                                AWAS 2.0 membantu memantau kendaraan, kecepatan, dan status pengemudi secara real-time dengan
                                peta interaktif yang modern dan detail hingga jalan-jalan kecil.
                            </p>

                            <div className="row g-4">
                                <div className="col-6">
                                    <div className="d-flex align-items-start gap-3">
                                        <div className="bg-white text-primary d-flex align-items-center justify-content-center shadow-sm rounded-circle"
                                            style={{ width: '45px', height: '45px', flexShrink: 0, fontSize: '1.2rem' }}>
                                            <i className="fa-solid fa-car-side"></i>
                                        </div>
                                        <div>
                                            <h6 className="fw-bold mb-1" style={{ fontSize: '0.9rem' }}>Live Tracking</h6>
                                            <small className="text-muted" style={{ fontSize: '0.75rem' }}>Lokasi real-time</small>
                                        </div>
                                    </div>
                                </div>
                                <div className="col-6">
                                    <div className="d-flex align-items-start gap-3">
                                        <div className="bg-white text-success d-flex align-items-center justify-content-center shadow-sm rounded-circle"
                                            style={{ width: '45px', height: '45px', flexShrink: 0, fontSize: '1.2rem' }}>
                                            <i className="fa-solid fa-gauge-high"></i>
                                        </div>
                                        <div>
                                            <h6 className="fw-bold mb-1" style={{ fontSize: '0.9rem' }}>Pantau Kecepatan</h6>
                                            <small className="text-muted" style={{ fontSize: '0.75rem' }}>Monitoring kecepatan</small>
                                        </div>
                                    </div>
                                </div>
                                <div className="col-6">
                                    <div className="d-flex align-items-start gap-3">
                                        <div className="bg-white text-warning d-flex align-items-center justify-content-center shadow-sm rounded-circle"
                                            style={{ width: '45px', height: '45px', flexShrink: 0, fontSize: '1.2rem' }}>
                                            <i className="fa-regular fa-bell"></i>
                                        </div>
                                        <div>
                                            <h6 className="fw-bold mb-1" style={{ fontSize: '0.9rem' }}>Peringatan Otomatis</h6>
                                            <small className="text-muted" style={{ fontSize: '0.75rem' }}>Deteksi risiko</small>
                                        </div>
                                    </div>
                                </div>
                                <div className="col-6">
                                    <div className="d-flex align-items-start gap-3">
                                        <div className="bg-white d-flex align-items-center justify-content-center shadow-sm rounded-circle"
                                            style={{ width: '45px', height: '45px', flexShrink: 0, color: '#8b5cf6', fontSize: '1.2rem' }}>
                                            <i className="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <div>
                                            <h6 className="fw-bold mb-1" style={{ fontSize: '0.9rem' }}>Keamanan Maksimal</h6>
                                            <small className="text-muted" style={{ fontSize: '0.75rem' }}>Perjalanan lebih aman</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* BAGIAN KANAN: Peta */}
                        <div className="col-lg-7" data-aos="fade-left" data-aos-delay="200">
                            <div className="position-relative bg-white p-2 rounded-4 shadow-lg" style={{ border: '1px solid #e2e8f0' }}>
                                <div className="map-wrapper" style={{ height: '480px', borderRadius: '1rem', overflow: 'hidden', position: 'relative' }}>
                                    <div id="tracking-map" style={{ width: '100%', height: '100%', zIndex: 1 }}></div>

                                    {/* Badge Live Top Left */}
                                    <div className="position-absolute bg-white px-3 py-2 shadow-sm d-flex align-items-center gap-2"
                                        style={{ top: '20px', left: '20px', borderRadius: '50rem', zIndex: 10, border: '1px solid #f1f5f9' }}>
                                        <div style={{ width: '8px', height: '8px', background: '#22c55e', borderRadius: '50%' }}></div>
                                        <span className="text-muted" style={{ fontSize: '0.85rem' }}>Terhubung</span>
                                    </div>

                                    {/* Overlay Card Bottom */}
                                    <div className="position-absolute bg-white p-3 shadow-lg d-flex align-items-center justify-content-between flex-wrap gap-3"
                                        style={{ bottom: '20px', left: '50%', transform: 'translateX(-50%)', width: '92%', borderRadius: '16px', zIndex: 10 }}>
                                        <div className="d-flex align-items-center gap-3">
                                            <div className="bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm"
                                                style={{ width: '50px', height: '50px', fontSize: '1.2rem' }}>
                                                <i className="fa-solid fa-location-crosshairs"></i>
                                            </div>
                                            <div>
                                                <h6 className="fw-bold mb-1 text-dark" style={{ fontSize: '1rem' }}>Posisi Saat Ini</h6>
                                            </div>
                                        </div>
                                        <div className="d-flex align-items-center gap-4">
                                            <div className="border-start ps-3">
                                                <small className="text-muted d-block mb-1" style={{ fontSize: '0.75rem' }}>Kecepatan</small>
                                                <h6 className="fw-bold m-0" style={{ fontSize: '1.1rem' }}>65 <span style={{ fontSize: '0.8rem', fontWeight: 'normal', color: '#64748b' }}>km/h</span></h6>
                                            </div>
                                            <div className="border-start ps-3">
                                                <small className="text-muted d-block mb-1" style={{ fontSize: '0.75rem' }}>Sisa Waktu</small>
                                                <h6 className="fw-bold m-0" style={{ fontSize: '1.1rem' }}>15 <span style={{ fontSize: '0.8rem', fontWeight: 'normal', color: '#64748b' }}>mnt</span></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Bottom Banner */}
                    <div className="bg-white rounded-4 shadow-sm p-4 mt-2" data-aos="fade-up" style={{ border: '1px solid #e2e8f0' }}>
                        <div className="row text-center text-md-start align-items-center g-4">
                            <div className="col-md-4 d-flex align-items-center justify-content-md-start justify-content-center gap-3 px-4"
                                style={{ borderRight: '1px solid #e2e8f0' }}>
                                <div className="text-primary bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                    style={{ width: '50px', height: '50px', flexShrink: 0 }}>
                                    <i className="fa-solid fa-shield-halved fs-5"></i>
                                </div>
                                <div className="text-start">
                                    <h6 className="fw-bold mb-1 text-dark" style={{ fontSize: '0.95rem' }}>Sistem Monitoring Real-time</h6>
                                    <small className="text-muted" style={{ fontSize: '0.8rem', lineHeight: 1.4, display: 'block' }}>Data diperbarui setiap detik untuk memberikan informasi terkini.</small>
                                </div>
                            </div>
                            <div className="col-md-4 d-flex align-items-center justify-content-md-start justify-content-center gap-3 px-4"
                                style={{ borderRight: '1px solid #e2e8f0' }}>
                                <div className="text-success bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                    style={{ width: '50px', height: '50px', flexShrink: 0 }}>
                                    <i className="fa-solid fa-phone-volume fs-5"></i>
                                </div>
                                <div className="text-start">
                                    <h6 className="fw-bold mb-1 text-dark" style={{ fontSize: '0.95rem' }}>Peringatan Darurat Otomatis</h6>
                                    <small className="text-muted" style={{ fontSize: '0.8rem', lineHeight: 1.4, display: 'block' }}>Sistem akan menghubungi kontak darurat jika terjadi kondisi berisiko.</small>
                                </div>
                            </div>
                            <div className="col-md-4 d-flex align-items-center justify-content-md-start justify-content-center gap-3 px-4">
                                <div className="rounded-circle d-flex align-items-center justify-content-center"
                                    style={{ width: '50px', height: '50px', flexShrink: 0, color: '#8b5cf6', background: 'rgba(139, 92, 246, 0.1)' }}>
                                    <i className="fa-regular fa-map fs-5"></i>
                                </div>
                                <div className="text-start">
                                    <h6 className="fw-bold mb-1 text-dark" style={{ fontSize: '0.95rem' }}>Peta Interaktif Detail</h6>
                                    <small className="text-muted" style={{ fontSize: '0.8rem', lineHeight: 1.4, display: 'block' }}>Menampilkan rumah, toko, dan jalan dengan lebih lengkap.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* PENGADUAN & FAQ SECTION */}
            <section id="pengaduan-faq" className="section-padding position-relative" style={{ backgroundColor: '#f8fafc' }}>
                <div className="container">
                    <div className="row text-center mb-5" data-aos="fade-up">
                        <div className="col-12">
                            <h2 className="section-title">Pusat Bantuan & <span className="gradient-text">Pengaduan</span></h2>
                            <p className="section-subtitle mx-auto" style={{ maxWidth: '600px' }}>
                                Sampaikan kendala teknis Anda atau temukan jawaban instan seputar penggunaan sistem AWAS
                            </p>
                        </div>
                    </div>

                    <div className="row g-4 align-items-stretch">
                        {/* KIRI: FORM PENGADUAN */}
                        <div className="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                            <div className="card-custom-new d-flex flex-column">
                                <h4 className="heading-blue">Formulir Pengaduan</h4>
                                <p className="subheading-new">Kirimkan laporan atau saran Anda</p>

                                <form action="#" method="POST" className="d-flex flex-column flex-grow-1">
                                    <div className="row g-3 mb-3">
                                        <div className="col-md-6">
                                            <label className="form-label-new">Nama Lengkap</label>
                                            <input type="text" className="form-control form-control-new" placeholder="Masukkan nama lengkap" required />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label-new">Alamat Email</label>
                                            <input type="email" className="form-control form-control-new" placeholder="Masukkan email" required />
                                        </div>
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label-new">Kategori Laporan</label>
                                        <select className="form-select form-control-new" required defaultValue="">
                                            <option value="" disabled>Pilih kategori laporan</option>
                                            <option value="error">Sistem Error / Deteksi Wajah Gagal</option>
                                            <option value="gps">Kendala GPS / Tracking Route</option>
                                            <option value="akun">Masalah Login / Pemulihan Akun</option>
                                            <option value="lainnya">Saran & Masukan Lainnya</option>
                                        </select>
                                    </div>

                                    <div className="mb-4 flex-grow-1 d-flex flex-column">
                                        <label className="form-label-new">Detail Pengaduan</label>
                                        <textarea className="form-control form-control-new flex-grow-1" style={{ minHeight: '120px' }} placeholder="Ceritakan detail kendala secara spesifik..." required></textarea>
                                        <div className="text-end mt-1 text-muted" style={{ fontSize: '0.75rem' }}>0 / 1000</div>
                                    </div>

                                    <button type="submit" className="btn-submit-new mt-auto">
                                        Kirim Laporan <i className="fa-solid fa-paper-plane ms-1"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {/* KANAN: FAQ ACCORDION */}
                        <div className="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                            <div className="card-custom-new d-flex flex-column">
                                <h4 className="heading-blue">Pertanyaan Umum (FAQ)</h4>
                                <p className="subheading-new">Solusi cepat untuk Anda</p>

                                <div className="accordion accordion-new flex-grow-1" id="accordionFAQ">
                                    <div className="accordion-item">
                                        <h2 className="accordion-header" id="headingOne">
                                            <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                                Bagaimana cara kerja deteksi kantuk AI?
                                            </button>
                                        </h2>
                                        <div id="collapseOne" className="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
                                            <div className="accordion-body">
                                                Sistem melacak <i>Eye Aspect Ratio</i> (EAR) dan pergerakan mulut secara real-time via kamera dashboard.
                                            </div>
                                        </div>
                                    </div>

                                    <div className="accordion-item">
                                        <h2 className="accordion-header" id="headingTwo">
                                            <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                                Apakah butuh perangkat keras khusus?
                                            </button>
                                        </h2>
                                        <div id="collapseTwo" className="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
                                            <div className="accordion-body">
                                                Tidak, cukup gunakan smartphone Android/iOS di dashboard atau head-unit yang kompatibel.
                                            </div>
                                        </div>
                                    </div>

                                    <div className="accordion-item">
                                        <h2 className="accordion-header" id="headingThree">
                                            <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                Bagaimana kondisi dalam mobil gelap?
                                            </button>
                                        </h2>
                                        <div id="collapseThree" className="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFAQ">
                                            <div className="accordion-body">
                                                Dilatih dengan dataset <i>low-light</i>, atau disarankan menggunakan aksesoris IR (Inframerah) opsional.
                                            </div>
                                        </div>
                                    </div>

                                    <div className="accordion-item">
                                        <h2 className="accordion-header" id="headingFour">
                                            <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                                Siapa penerima notifikasi SOS?
                                            </button>
                                        </h2>
                                        <div id="collapseFour" className="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFAQ">
                                            <div className="accordion-body">
                                                Kontak darurat pilihan Anda, dikirim otomatis berupa SMS dan link Google Maps koordinat terakhir.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="help-box-new mt-auto">
                                    <div className="d-flex align-items-center gap-3">
                                        <div style={{ background: '#2563eb', color: 'white', width: '32px', height: '32px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1rem' }}>
                                            <i className="fa-solid fa-info"></i>
                                        </div>
                                        <div>
                                            <h6 className="fw-bold mb-1" style={{ color: '#1e3a8a', fontSize: '0.9rem' }}>Masih memiliki pertanyaan?</h6>
                                            <p className="mb-0 text-muted" style={{ fontSize: '0.8rem' }}>Kunjungi halaman <b>Bantuan</b> untuk informasi lebih lengkap.</p>
                                        </div>
                                    </div>
                                    <a href="#" className="btn-help text-nowrap ms-2">Buka Bantuan <i className="fa-solid fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* FOOTER AESTHETIC */}
            <footer className="footer-aesthetic">
                <div className="footer-wave">
                    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                        <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" className="shape-fill"></path>
                    </svg>
                </div>

                <div className="container">
                    <div className="row g-4" data-aos="fade-up">
                        <div className="col-lg-4 pe-lg-5 mb-4 mb-lg-0">
                            <h4 className="fw-bold text-white mb-3 d-flex align-items-center gap-2" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                                <span style={{ color: '#faf6f7' }}>AWAS</span><span style={{ color: '#f43f5e' }}>.</span>
                            </h4>
                            <p className="mb-4" style={{ color: 'rgba(255, 255, 255, 0.75)', fontSize: '0.85rem', lineHeight: 1.6 }}>
                                Semua konten di website ini dilindungi oleh hak cipta dan tidak boleh digunakan tanpa izin dari AWAS
                            </p>
                            <div className="d-flex">
                                <a href="#" className="footer-social-box"><i className="fa-brands fa-instagram"></i></a>
                                <a href="#" className="footer-social-box"><i className="fa-brands fa-facebook-f"></i></a>
                                <a href="#" className="footer-social-box"><i className="fa-brands fa-twitter"></i></a>
                            </div>
                        </div>

                        <div className="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0">
                            <h6 className="mb-3">Navigasi</h6>
                            <ul className="list-unstyled d-flex flex-column gap-2">
                                <li><a href="#beranda">Beranda</a></li>
                                <li><a href="#tentang">Keunggulan</a></li>
                                <li><a href="#cara-kerja">Alur Sistem</a></li>
                                <li><a href="#live-tracking">Peta Live</a></li>
                                <li><a href="#pengaduan-faq">Bantuan</a></li>
                            </ul>
                        </div>

                        <div className="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0">
                            <h6 className="mb-3">Legal</h6>
                            <ul className="list-unstyled d-flex flex-column gap-2">
                                <li><a href="#">Privasi & Keamanan</a></li>
                                <li><a href="#">Syarat & Ketentuan</a></li>
                                <li><a href="#">Panduan Pengguna</a></li>
                                <li><a href="#">Pembaruan</a></li>
                            </ul>
                        </div>

                        <div className="col-lg-4 col-md-4 mb-4 mb-lg-0">
                            <h6 className="mb-3">Kontak</h6>
                            <div className="d-flex flex-column gap-3">
                                <a href="#" className="contact-info d-flex align-items-center">
                                    <i className="fa-solid fa-phone"></i> +123 456 7890
                                </a>
                                <a href="#" className="contact-info d-flex align-items-center">
                                    <i className="fa-solid fa-envelope"></i> support@awas.com
                                </a>
                                <div className="contact-info d-flex align-items-start">
                                    <i className="fa-solid fa-location-dot mt-1"></i>
                                    <span>Gedung IT Pusat,<br />Area Kampus Akademik</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="footer-bottom mt-5">
                        <a href="#beranda" className="back-to-top-btn" title="Back to Top">
                            <i className="fa-solid fa-arrow-up"></i>
                        </a>
                        <p className="mb-0 pt-2" style={{ fontSize: '0.8rem', color: 'rgba(255,255,255,0.6)' }}>
                            Copyright &copy; 2026 AWAS 2.0. All Rights Reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </>
    );
};

export default Home;