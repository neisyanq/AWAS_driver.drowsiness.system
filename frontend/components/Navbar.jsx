import React from 'react';

const Navbar = () => {
    return (
        <div className="navbar-wrapper">
            <nav className="navbar navbar-expand-lg navbar-custom">
                <div className="container-fluid px-2">
                    <a className="navbar-brand d-flex align-items-center gap-2" href="#" data-aos="fade-right">
                        <div className="text-white d-flex justify-content-center align-items-center"
                            style={{ width: '32px', height: '32px', background: 'var(--gradient-main)', borderRadius: '10px', boxShadow: '0 4px 10px rgba(67,56,202,0.3)' }}>
                            <i className="fa-solid fa-shield-halved fs-6"></i>
                        </div>
                        AWAS 2.0
                    </a>
                    <button className="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNav">
                        <i className="fa-solid fa-bars-staggered text-primary fs-4"></i>
                    </button>
                    <div className="collapse navbar-collapse" id="navbarNav">
                        <ul className="navbar-nav mx-auto nav-item-lp" data-aos="fade-down" data-aos-delay="100">
                            <li className="nav-item"><a className="nav-link active" href="#beranda">Beranda</a></li>
                            <li className="nav-item"><a className="nav-link" href="#tentang">Keunggulan</a></li>
                            <li className="nav-item"><a className="nav-link" href="#cara-kerja">Alur Sistem</a></li>
                            <li className="nav-item"><a className="nav-link" href="#live-tracking">Peta Live</a></li>
                            <li className="nav-item"><a className="nav-link" href="#pengaduan-faq">Bantuan</a></li>
                        </ul>
                        <div className="d-flex mt-3 mt-lg-0 gap-2" data-aos="fade-left" data-aos-delay="200">
                            <a href="#auth-section" onClick={() => document.getElementById('pills-login-tab')?.click()}
                                className="btn btn-hero-secondary text-decoration-none px-4 py-2 fw-bold d-flex align-items-center"
                                style={{ borderRadius: '50rem', fontSize: '0.95rem' }}>
                                Masuk
                            </a>
                            <a href="#auth-section" onClick={() => document.getElementById('pills-register-tab')?.click()}
                                className="btn btn-login-nav w-100 w-lg-auto d-flex align-items-center justify-content-center gap-2 px-4">
                                Daftar <i className="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    );
};

export default Navbar;