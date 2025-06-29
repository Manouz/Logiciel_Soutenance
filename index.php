<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaSoutenance - Plateforme de Gestion des Soutenances</title>
    <style>
        :root {
            --primary-color: rgb(0, 51, 41);
            --primary-light: rgba(0, 51, 41, 0.1);
            --primary-dark: rgb(0, 35, 28);
            --secondary-color: #10b981;
            --accent-color: #34d399;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
            color: var(--gray-900);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header/Navbar */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo::before {
            content: "🎓";
            font-size: 1.8rem;
        }

        .auth {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }

        .btn-login {
            color: var(--primary-color);
            background: transparent;
            border: 2px solid var(--primary-color);
        }

        .btn-login:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .btn-signup {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Hero Section */
        .hero-wrapper {
            padding-top: 100px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, var(--primary-light) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 2;
        }

        .hero-text {
            animation: slideInLeft 1s ease-out;
        }

        .hero-text h1 {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            line-height: 1.1;
            margin-bottom: 1.5rem;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.02em;
        }

        .hero-text .highlight {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-text p {
            font-size: 1.25rem;
            color: var(--gray-600);
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .btn-main {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-main::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-main:hover::before {
            left: 100%;
        }

        .btn-main:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        .btn-main::after {
            content: "→";
            transition: transform 0.3s ease;
        }

        .btn-main:hover::after {
            transform: translateX(4px);
        }

        /* Carousel Container */
        .carousel-container {
            position: relative;
            animation: slideInRight 1s ease-out;
        }

        .carousel {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            background: var(--white);
        }

        .carousel-wrapper {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .carousel-slide {
            min-width: 100%;
            position: relative;
            aspect-ratio: 16/10;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .carousel-slide:hover img {
            transform: scale(1.05);
        }

        .carousel-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: var(--white);
            padding: 2rem 1.5rem 1.5rem;
            transform: translateY(100%);
            transition: var(--transition);
        }

        .carousel-slide:hover .carousel-caption {
            transform: translateY(0);
        }

        .carousel-caption h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }

        /* Navigation Buttons */
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: var(--white);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
            box-shadow: var(--shadow-lg);
            color: var(--primary-color);
        }

        .carousel-btn:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-50%) scale(1.1);
            box-shadow: var(--shadow-xl);
        }

        .carousel-prev {
            left: 20px;
        }

        .carousel-next {
            right: 20px;
        }

        /* Indicators */
        .carousel-indicators {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 1.5rem;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid var(--gray-300);
            background: transparent;
            cursor: pointer;
            transition: var(--transition);
        }

        .indicator.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: scale(1.3);
        }

        .indicator:hover {
            border-color: var(--secondary-color);
            background: var(--secondary-color);
        }

        /* Slide Counter */
        .slide-counter {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--gray-500);
            font-weight: 600;
        }

        #currentSlideNumber {
            color: var(--primary-color);
            font-weight: 800;
        }

        /* Features Section */
        .features {
            padding: 6rem 2rem;
            background: var(--white);
            position: relative;
        }

        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gray-200), transparent);
        }

        .features-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .features h2 {
            font-size: 2.5rem;
            color: var(--gray-900);
            margin-bottom: 1rem;
            font-weight: 800;
        }

        .features p {
            font-size: 1.2rem;
            color: var(--gray-600);
            margin-bottom: 3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-100);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        /* Loading States */
        .carousel-slide img:not([src]) {
            background: linear-gradient(45deg, var(--gray-100) 25%, transparent 25%),
                       linear-gradient(-45deg, var(--gray-100) 25%, transparent 25%),
                       linear-gradient(45deg, transparent 75%, var(--gray-100) 75%),
                       linear-gradient(-45deg, transparent 75%, var(--gray-100) 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            animation: shimmer 1.5s infinite;
        }

        /* Animations */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            }
            100% {
                background-position: 20px 0, 20px 10px, 30px -10px, 10px 0px;
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .hero {
                grid-template-columns: 1fr;
                gap: 3rem;
                text-align: center;
            }
            
            .hero-text h1 {
                font-size: clamp(2rem, 8vw, 3.5rem);
            }
        }

        @media (max-width: 768px) {
            .header-content {
                padding: 1rem;
            }
            
            .hero {
                padding: 1rem;
                gap: 2rem;
            }
            
            .carousel-btn {
                width: 40px;
                height: 40px;
            }
            
            .carousel-prev {
                left: 10px;
            }
            
            .carousel-next {
                right: 10px;
            }
            
            .auth {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .hero-text h1 {
                font-size: 2rem;
            }
            
            .btn-main {
                width: 100%;
                justify-content: center;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        .carousel-btn:focus,
        .indicator:focus,
        .btn:focus {
            outline: 2px solid var(--secondary-color);
            outline-offset: 2px;
        }

        /* Scroll indicator */
        .scroll-indicator {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 50px;
            background: var(--gray-300);
            border-radius: 2px;
            opacity: 0.7;
        }

        .scroll-indicator::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 30%;
            background: var(--primary-color);
            border-radius: 2px;
            animation: scroll-animation 2s ease-in-out infinite;
        }

        @keyframes scroll-animation {
            0% {
                transform: translateY(0);
                opacity: 1;
            }
            100% {
                transform: translateY(200%);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header/Navbar -->
    <header class="header">
        <div class="header-content">
            <div class="logo">MaSoutenance</div>
            <div class="auth">
                <a href="login.php" class="btn btn-login">Connexion</a>
                <!--a href="#register" class="btn btn-signup">Inscription</-!a-->
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <div class="hero-wrapper">
        <main class="hero">
            <div class="hero-text">
                <h1>Organisez, <span class="highlight">gérez</span> <br>et <span class="highlight">réussissez</span></h1>
                <p>Planifiez, organisez et suivez chaque étape de votre diplôme d'ingénieur avec notre plateforme intuitive et moderne</p>
                <a href="login.php" class="btn-main" ">
                    Commencez dès maintenant
                </a>
            </div>

            <!-- Carousel d'images -->
            <div class="carousel-container">
                <div class="carousel">
                    <div class="carousel-wrapper" id="carouselWrapper">
                        <!-- Slide 1 -->
                        <div class="carousel-slide active">
                            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=500&fit=crop&crop=center" 
                                 alt="Présentation de soutenance"
                                 title="Planification de soutenance">
                            <div class="carousel-caption">
                                <h3>Planification de soutenance</h3>
                            </div>
                        </div>
                        
                        <!-- Slide 2 -->
                        <div class="carousel-slide">
                            <img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=500&fit=crop&crop=center" 
                                 alt="Gestion des jurys"
                                 title="Gestion des jurys">
                            <div class="carousel-caption">
                                <h3>Gestion des jurys</h3>
                            </div>
                        </div>
                        
                        <!-- Slide 3 -->
                        <div class="carousel-slide">
                            <img src="https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=800&h=500&fit=crop&crop=center" 
                                 alt="Calendrier académique"
                                 title="Calendrier intégré">
                            <div class="carousel-caption">
                                <h3>Calendrier intégré</h3>
                            </div>
                        </div>
                        
                        <!-- Slide 4 -->
                        <div class="carousel-slide">
                            <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=500&fit=crop&crop=center" 
                                 alt="Suivi des étudiants"
                                 title="Suivi personnalisé">
                            <div class="carousel-caption">
                                <h3>Suivi personnalisé</h3>
                            </div>
                        </div>
                        
                        <!-- Slide 5 -->
                        <div class="carousel-slide">
                            <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&h=500&fit=crop&crop=center" 
                                 alt="Rapports et statistiques"
                                 title="Rapports détaillés">
                            <div class="carousel-caption">
                                <h3>Rapports détaillés</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons de navigation -->
                    <button class="carousel-btn carousel-prev" id="prevBtn" aria-label="Image précédente">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                    </button>
                    <button class="carousel-btn carousel-next" id="nextBtn" aria-label="Image suivante">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- Indicateurs -->
                <div class="carousel-indicators" id="carouselIndicators">
                    <button class="indicator active" data-slide="0" aria-label="Aller à l'image 1"></button>
                    <button class="indicator" data-slide="1" aria-label="Aller à l'image 2"></button>
                    <button class="indicator" data-slide="2" aria-label="Aller à l'image 3"></button>
                    <button class="indicator" data-slide="3" aria-label="Aller à l'image 4"></button>
                    <button class="indicator" data-slide="4" aria-label="Aller à l'image 5"></button>
                </div>

                <!-- Compteur de slides -->
                <div class="slide-counter">
                    <span id="currentSlideNumber">1</span> / 5
                </div>
            </div>
        </main>
        
        <div class="scroll-indicator"></div>
    </div>

    <!-- Features Section -->
    <section class="features">
        <div class="features-content">
            <h2>Pourquoi choisir MaSoutenance ?</h2>
            <p>Une plateforme complète pour simplifier la gestion des soutenances académiques</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <h3>📅 Planification intelligente</h3>
                    <p>Organisez automatiquement les créneaux de soutenance selon les disponibilités</p>
                </div>
                <div class="feature-card">
                    <h3>👥 Gestion des jurys</h3>
                    <p>Assignez et coordonnez facilement les membres du jury pour chaque soutenance</p>
                </div>
                <div class="feature-card">
                    <h3>📊 Suivi en temps réel</h3>
                    <p>Monitorer l'avancement et générer des rapports détaillés automatiquement</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Carousel Class
        class Carousel {
            constructor() {
                this.currentSlide = 0;
                this.slides = document.querySelectorAll('.carousel-slide');
                this.indicators = document.querySelectorAll('.indicator');
                this.wrapper = document.getElementById('carouselWrapper');
                this.prevBtn = document.getElementById('prevBtn');
                this.nextBtn = document.getElementById('nextBtn');
                this.slideCounter = document.getElementById('currentSlideNumber');
                this.totalSlides = this.slides.length;
                this.autoPlayInterval = null;
                this.isTransitioning = false;

                this.init();
            }

            init() {
                if (this.totalSlides === 0) return;

                this.prevBtn.addEventListener('click', () => this.prevSlide());
                this.nextBtn.addEventListener('click', () => this.nextSlide());

                this.indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', () => this.goToSlide(index));
                });

                this.startAutoPlay();

                const carousel = document.querySelector('.carousel');
                carousel.addEventListener('mouseenter', () => this.stopAutoPlay());
                carousel.addEventListener('mouseleave', () => this.startAutoPlay());

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') this.prevSlide();
                    if (e.key === 'ArrowRight') this.nextSlide();
                });

                this.addTouchSupport();
                this.updateCarousel();
            }

            updateCarousel() {
                if (this.isTransitioning) return;

                this.isTransitioning = true;
                const translateX = -this.currentSlide * 100;
                this.wrapper.style.transform = `translateX(${translateX}%)`;

                this.indicators.forEach((indicator, index) => {
                    indicator.classList.toggle('active', index === this.currentSlide);
                });

                this.slides.forEach((slide, index) => {
                    slide.classList.toggle('active', index === this.currentSlide);
                });

                if (this.slideCounter) {
                    this.slideCounter.textContent = this.currentSlide + 1;
                }

                setTimeout(() => {
                    this.isTransitioning = false;
                }, 600);
            }

            nextSlide() {
                if (this.isTransitioning) return;
                this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                this.updateCarousel();
            }

            prevSlide() {
                if (this.isTransitioning) return;
                this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                this.updateCarousel();
            }

            goToSlide(index) {
                if (this.isTransitioning || index === this.currentSlide) return;
                this.currentSlide = index;
                this.updateCarousel();
            }

            startAutoPlay() {
                this.stopAutoPlay();
                this.autoPlayInterval = setInterval(() => {
                    this.nextSlide();
                }, 5000);
            }

            stopAutoPlay() {
                if (this.autoPlayInterval) {
                    clearInterval(this.autoPlayInterval);
                    this.autoPlayInterval = null;
                }
            }

            addTouchSupport() {
                const carousel = document.querySelector('.carousel');
                let startX = 0;
                let endX = 0;

                carousel.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                    this.stopAutoPlay();
                }, { passive: true });

                carousel.addEventListener('touchend', (e) => {
                    endX = e.changedTouches[0].clientX;
                    this.handleSwipe(startX, endX);
                    this.startAutoPlay();
                }, { passive: true });
            }

            handleSwipe(startX, endX) {
                const threshold = 50;
                const diff = startX - endX;

                if (Math.abs(diff) > threshold) {
                    if (diff > 0) {
                        this.nextSlide();
                    } else {
                        this.prevSlide();
                    }
                }
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            const carousel = new Carousel();
            window.carousel = carousel;
            
            // Smooth scrolling for navigation
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Header scroll effect
            let lastScrollTop = 0;
            const header = document.querySelector('.header');

            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 100) {
                    header.style.background = 'rgba(255, 255, 255, 0.98)';
                    header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
                } else {
                    header.style.background = 'rgba(255, 255, 255, 0.95)';
                    header.style.boxShadow = 'none';
                }
            });
        });

        // CTA Button Handler
        //function handleCTA() {
            //console.log('CTA clicked - Redirection vers le dashboard');
            
            // Animation de feedback
            /*const btn = event.target;
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                btn.style.transform = 'translateY(-3px)';
            }, 150);
            
            // Simulation de redirection (à remplacer par votre logique PHP)
            setTimeout(() => {
                showNotification('Redirection vers le tableau de bord...', 'success');
                // window.location.href = 'dashboard.php';
            }, 500);
        }*/

        // Notification System
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            
            const colors = {
                success: 'var(--success-color)',
                error: 'var(--error-color)',
                warning: 'var(--warning-color)',
                info: 'var(--primary-color)'
            };
            
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                padding: 1rem 1.5rem;
                background: ${colors[type]};
                color: white;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-lg);
                z-index: 2000;
                opacity: 0;
                transform: translateX(100%);
                transition: var(--transition);
                font-weight: 600;
                max-width: 300px;
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 100);

            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Auth Button Handlers
        document.addEventListener('DOMContentLoaded', () => {
            const loginBtn = document.querySelector('.btn-login');
            //const signupBtn = document.querySelector('.btn-signup');
            
            loginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                //showNotification('Redirection vers la page de connexion...', 'info');
                 window.location.href = 'login.php';
            });
            
           /* signupBtn.addEventListener('click', (e) => {
                e.preventDefault();
                showNotification('Redirection vers la page d\'inscription...', 'info');
                // window.location.href = 'register.php';
            });*/
        });

        // Enhanced Carousel with progressive image loading
        class EnhancedCarousel extends Carousel {
            constructor() {
                super();
                this.preloadImages();
            }
            
            preloadImages() {
                this.slides.forEach((slide, index) => {
                    const img = slide.querySelector('img');
                    if (img && img.src) {
                        img.addEventListener('load', () => {
                            img.style.opacity = '1';
                            slide.classList.add('loaded');
                        });
                        
                        img.addEventListener('error', () => {
                            console.warn(`Erreur de chargement de l'image ${index + 1}`);
                            this.handleImageError(img, index);
                        });
                    }
                });
            }
            
            handleImageError(img, index) {
                // Remplacer par une image de placeholder avec le bon aspect ratio
                img.src = `https://via.placeholder.com/800x500/f3f4f6/6b7280?text=Image+${index + 1}`;
                img.alt = `Image de démonstration ${index + 1}`;
            }
        }

        // Performance optimizations
        function optimizePerformance() {
            // Lazy loading pour les images non visibles
            const images = document.querySelectorAll('img');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => {
                imageObserver.observe(img);
            });

            // Debounce scroll events
            let scrollTimeout;
            const originalScrollHandler = window.onscroll;
            
            window.addEventListener('scroll', () => {
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                }
                scrollTimeout = setTimeout(() => {
                    if (originalScrollHandler) originalScrollHandler();
                }, 16); // ~60fps
            }, { passive: true });
        }

        // Analytics and tracking (simulation)
        function trackEvent(eventName, properties = {}) {
            console.log('Analytics Event:', eventName, properties);
            // Ici vous pourriez intégrer Google Analytics, Mixpanel, etc.
        }

        // Enhanced initialization
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize enhanced carousel
            const enhancedCarousel = new EnhancedCarousel();
            window.carousel = enhancedCarousel;
            
            // Performance optimizations
            optimizePerformance();
            
            // Track page view
            trackEvent('page_view', {
                page: 'landing',
                timestamp: new Date().toISOString()
            });
            
            // Feature cards animation on scroll
            const featureCards = document.querySelectorAll('.feature-card');
            const cardObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                    }
                });
            }, { threshold: 0.1 });

            featureCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                cardObserver.observe(card);
            });
            
            // Easter egg - Konami code
            let konamiCode = [];
            const konamiSequence = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'KeyB', 'KeyA'];
            
            document.addEventListener('keydown', (e) => {
                konamiCode.push(e.code);
                if (konamiCode.length > konamiSequence.length) {
                    konamiCode.shift();
                }
                
                if (konamiCode.join('') === konamiSequence.join('')) {
                    showNotification('🎉 Code Konami activé ! Développeur détecté !', 'success');
                    document.body.style.filter = 'hue-rotate(180deg)';
                    setTimeout(() => {
                        document.body.style.filter = 'none';
                    }, 3000);
                    konamiCode = [];
                }
            });
        });
    </script>
</body>
</html>
