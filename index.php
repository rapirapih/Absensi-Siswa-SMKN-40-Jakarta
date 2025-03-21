<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi - SMK Negeri 40 Jakarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #283593;
            --accent-color: #3949ab;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --mouse-x: 50%;
            --mouse-y: 50%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #333;
            position: relative;
            overflow-x: hidden;
            background: 
                radial-gradient(circle at var(--mouse-x) var(--mouse-y), 
                    rgba(26, 35, 126, 0.8), 
                    rgba(63, 81, 181, 0.6) 30%,
                    rgba(3, 169, 244, 0.4) 60%,
                    rgba(0, 188, 212, 0.2) 90%
                ),
                linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            background-size: 200% 200%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hero-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 4rem 2rem;
            margin-top: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
            overflow: hidden;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.8s ease forwards;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, 
                rgba(255,255,255,0.1) 0%,
                rgba(255,255,255,0.05) 100%);
            z-index: -1;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
            cursor: pointer;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            color: white;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover .feature-icon {
            background: white;
            -webkit-background-clip: text;
        }

        .feature-card:hover h4,
        .feature-card:hover p {
            color: white;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 15px;
            z-index: -1;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover::after {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .btn-login {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 1rem 3rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255,255,255,0.2),
                transparent
            );
            transition: 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            color: white;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login i,
        .btn-login span {
            position: relative;
            z-index: 1;
        }

        .school-logo {
            max-width: 120px;
            margin-bottom: 1.5rem;
            animation: float 3s ease-in-out infinite;
            transition: transform 0.3s ease;
        }

        .school-logo:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .footer {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem 0;
            margin-top: 4rem;
            color: white;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Add floating animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Add scroll reveal animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stats counter section */
        .stats-section {
            margin: 4rem 0;
            padding: 2rem 0;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }

        .stat-card:hover::before {
            opacity: 0.1;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        /* Scroll to top button */
        .scroll-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .scroll-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        /* Enhanced Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-color);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            transition: opacity 0.5s ease;
        }

        .loader {
            width: 80px;
            height: 80px;
            border: 4px solid rgba(255,255,255,0.1);
            border-left-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Add these styles in your <style> section */
        .school-info-section {
            padding: 2rem 0;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .info-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .info-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .info-card h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .contact-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .contact-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #666;
        }

        .contact-list li i {
            color: var(--primary-color);
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-link {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-3px);
            color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .info-link {
            color: var(--primary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .info-link:hover {
            color: var(--accent-color);
            transform: translateX(5px);
        }

        /* Update the school logo and title styling in the <style> section */
        .school-logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }

        .school-logo {
            max-width: 120px;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
            transition: transform 0.3s ease;
        }

        .school-logo:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .school-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            text-align: center;
            margin: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
    <!-- Add a loading screen -->
    <div class="loading-screen">
        <div class="loader"></div>
        <p>Memuat Sistem Absensi...</p>
    </div>

    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section text-center">
            <div class="school-logo-container">
                <img src="Logo 40.png" alt="Logo SMK Negeri 40 Jakarta" class="school-logo">
                <p class="school-title">SMK NEGERI 40 JAKARTA</p>
            </div>
            <h1 class="mb-4">Sistem Absensi Digital</h1>
            <p class="lead mb-5">Platform digital untuk pencatatan dan pemantauan kehadiran siswa secara efisien dan akurat</p>
            <a href="login.php" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i>
                Masuk ke Sistem
            </a>
        </div>

        <!-- Add stats section -->
        <div class="stats-section">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" data-target="1000">0</div>
                        <p>Total Siswa</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" data-target="50">0</div>
                        <p>Guru</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" data-target="95">0</div>
                        <p>Tingkat Kehadiran</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" data-target="24">0</div>
                        <p>Kelas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-shield-check feature-icon"></i>
                    <h4>Absensi Digital</h4>
                    <p>Sistem absensi modern dengan validasi kehadiran secara real-time</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-graph-up feature-icon"></i>
                    <h4>Monitoring Kehadiran</h4>
                    <p>Pemantauan kehadiran siswa secara terperinci dan terorganisir</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="bi bi-clock-history feature-icon"></i>
                    <h4>Riwayat Absensi</h4>
                    <p>Akses cepat ke riwayat kehadiran dan laporan absensi</p>
                </div>
            </div>
        </div>

        <!-- Add this after the Features Section and before the Footer -->
        <div class="school-info-section my-5">
            <div class="row g-4">
                <!-- Contact Info -->
                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h4>Alamat Sekolah</h4>
                        <p>Jl. Nanas II No.9, RT.9/RW.10, Utan Kayu Utara, Kec. Matraman, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13120</p>
                        <a href="https://maps.google.com" target="_blank" class="info-link">
                            <i class="bi bi-map"></i> Lihat di Maps
                        </a>
                    </div>
                </div>

                <!-- Contact Details -->
                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <h4>Kontak</h4>
                        <ul class="contact-list">
                            <li>
                                <i class="bi bi-telephone"></i>
                                <span>(021) 4890479</span>
                            </li>
                            <li>
                                <i class="bi bi-envelope"></i>
                                <span>info@smkn40jakarta.sch.id</span>
                            </li>
                            <li>
                                <i class="bi bi-globe"></i>
                                <span>www.smkn40jakarta.sch.id</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-share-fill"></i>
                        </div>
                        <h4>Media Sosial</h4>
                        <div class="social-links">
                            <a href="#" class="social-link">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="social-link">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social-link">
                                <i class="bi bi-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <p class="mb-0">© <?= date('Y') ?> SMK Negeri 40 Jakarta. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Add scroll to top button -->
    <div class="scroll-top">
        <i class="bi bi-arrow-up"></i>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth) * 100;
            const y = (e.clientY / window.innerHeight) * 100;
            
            // Add smooth easing
            requestAnimationFrame(() => {
                document.documentElement.style.setProperty('--mouse-x', `${x}%`);
                document.documentElement.style.setProperty('--mouse-y', `${y}%`);
            });
        });

        // Add touch support for mobile devices
        document.addEventListener('touchmove', (e) => {
            const touch = e.touches[0];
            const x = (touch.clientX / window.innerWidth) * 100;
            const y = (touch.clientY / window.innerHeight) * 100;
            
            requestAnimationFrame(() => {
                document.documentElement.style.setProperty('--mouse-x', `${x}%`);
                document.documentElement.style.setProperty('--mouse-y', `${y}%`);
            });
        }, { passive: true });

        // Add subtle animation when no interaction
        let angle = 0;
        function moveBackground() {
            if (!document.documentElement.style.getPropertyValue('--mouse-x')) {
                angle += 0.1;
                const x = 50 + Math.sin(angle) * 10;
                const y = 50 + Math.cos(angle) * 10;
                
                document.documentElement.style.setProperty('--mouse-x', `${x}%`);
                document.documentElement.style.setProperty('--mouse-y', `${y}%`);
            }
            requestAnimationFrame(moveBackground);
        }
        moveBackground();

        // Add loading screen
        window.addEventListener('load', () => {
            document.querySelector('.loading-screen').style.opacity = '0';
            setTimeout(() => {
                document.querySelector('.loading-screen').style.display = 'none';
            }, 500);
        });

        // Animate stats counter
        const stats = document.querySelectorAll('.stat-number');
        stats.forEach(stat => {
            const target = parseInt(stat.getAttribute('data-target'));
            const increment = target / 200;
            let current = 0;

            const updateStat = () => {
                if (current < target) {
                    current += increment;
                    stat.textContent = Math.ceil(current);
                    requestAnimationFrame(updateStat);
                } else {
                    stat.textContent = target;
                }
            };

            // Start animation when element is in view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateStat();
                        observer.unobserve(entry.target);
                    }
                });
            });

            observer.observe(stat);
        });

        // Scroll to top functionality
        window.addEventListener('scroll', () => {
            const scrollBtn = document.querySelector('.scroll-top');
            if (window.scrollY > 300) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });

        document.querySelector('.scroll-top').addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Add hover effect for feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px) scale(1.02)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add this to your existing script section
        document.addEventListener('DOMContentLoaded', function() {
            // Animate elements on scroll
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.feature-card, .stat-card');
                elements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementBottom = element.getBoundingClientRect().bottom;
                    
                    if (elementTop < window.innerHeight && elementBottom > 0) {
                        element.style.transform = 'translateY(0)';
                        element.style.opacity = '1';
                    }
                });
            };

            // Initial animation setup
            document.querySelectorAll('.feature-card, .stat-card').forEach(element => {
                element.style.transform = 'translateY(50px)';
                element.style.opacity = '0';
                element.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            });

            // Listen for scroll events
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Initial check

            // Enhanced stats counter with easing
            const easeOutQuad = t => t * (2 - t);
            const animateValue = (element, start, end, duration) => {
                let startTimestamp = null;
                
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const easedProgress = easeOutQuad(progress);
                    
                    element.textContent = Math.floor(start + (end - start) * easedProgress);
                    
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                
                window.requestAnimationFrame(step);
            };

            // Observe stat numbers
            const statObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(entry.target.getAttribute('data-target'));
                        animateValue(entry.target, 0, target, 2000);
                        statObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            document.querySelectorAll('.stat-number').forEach(stat => {
                statObserver.observe(stat);
            });
        });
    </script>
</body>
</html>