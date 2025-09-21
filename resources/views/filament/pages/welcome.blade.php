<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Warung Ayam Geprek Mantap | Ayam Geprek Terlezat di Jakarta</title>
        <meta name="description"
            content="Pesan ayam geprek, nasi uduk, dan minuman segar di Warung Ayam Geprek Mantap. Desain futuristik, glassmorphism, dan pengalaman makan yang tak terlupakan.">
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <!-- Fonts -->
        <link
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">

        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

        <!-- Custom CSS -->
        <style>
            /* Glassmorphism Variables */
            :root {
                --glass-bg: rgba(255, 255, 255, 0.08);
                --glass-border: rgba(255, 255, 255, 0.15);
                --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            }

            .dark {
                --glass-bg: rgba(0, 0, 0, 0.15);
                --glass-border: rgba(255, 255, 255, 0.08);
            }

            /* Glassmorphism Components */
            .glass {
                background: var(--glass-bg);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid var(--glass-border);
                box-shadow: var(--glass-shadow);
            }

            .glass-strong {
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.25);
            }

            .dark .glass-strong {
                background: rgba(0, 0, 0, 0.25);
                border: 1px solid rgba(255, 255, 255, 0.15);
            }

            /* Smooth Parallax */
            .parallax-element {
                will-change: transform;
                transform: translate3d(0, 0, 0);
            }

            /* Hero Section */
            .hero-section {
                height: 100vh;
                min-height: 700px;
                max-height: 1000px;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                overflow: hidden;
            }

            /* Animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(60px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            @keyframes float {

                0%,
                100% {
                    transform: translateY(0px) rotate(0deg);
                }

                33% {
                    transform: translateY(-15px) rotate(2deg);
                }

                66% {
                    transform: translateY(8px) rotate(-1deg);
                }
            }

            @keyframes glow {

                0%,
                100% {
                    text-shadow: 0 0 20px rgba(255, 215, 0, 0.8),
                        0 0 40px rgba(255, 215, 0, 0.6),
                        0 0 60px rgba(255, 215, 0, 0.4);
                }

                50% {
                    text-shadow: 0 0 30px rgba(255, 215, 0, 1),
                        0 0 50px rgba(255, 215, 0, 0.8),
                        0 0 70px rgba(255, 215, 0, 0.6);
                }
            }

            @keyframes pulse {

                0%,
                100% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.05);
                }
            }

            /* Animation Classes */
            .animate-fade-in-up {
                animation: fadeInUp 1s ease-out forwards;
            }

            .animate-fade-in {
                animation: fadeIn 0.8s ease-out forwards;
            }

            .animate-float {
                animation: float 6s ease-in-out infinite;
            }

            .animate-glow {
                animation: glow 3s ease-in-out infinite;
            }

            .animate-pulse-slow {
                animation: pulse 3s ease-in-out infinite;
            }

            /* Utility Classes */
            .text-glow {
                text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
            }

            .hover-glow:hover {
                box-shadow: 0 0 30px rgba(230, 57, 70, 0.4),
                    0 0 60px rgba(255, 215, 0, 0.3);
            }

            /* Smooth Transitions */
            * {
                transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
                transition-duration: 300ms;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
            }

            ::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.1);
            }

            ::-webkit-scrollbar-thumb {
                background: linear-gradient(to bottom, #E63946, #FFD700);
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(to bottom, #c52a38, #e6c200);
            }

            /* Performance Optimizations */
            .will-change-transform {
                will-change: transform;
            }

            .will-change-opacity {
                will-change: opacity;
            }

            /* Responsive Text */
            .hero-title {
                font-size: clamp(2.5rem, 8vw, 6rem);
                line-height: 1.1;
            }

            .hero-subtitle {
                font-size: clamp(1.1rem, 3vw, 1.8rem);
            }

            /* Hide scrollbar initially for smooth load */
            .no-scroll {
                overflow: hidden;
            }

            body::after {
                content: "";
                background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><filter id="f"><feTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="3" stitchTiles="stitch"/></filter><rect width="100%" height="100%" filter="url(%23f)"/></svg>');
                position: fixed;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                opacity: 0.1;
                z-index: -1;
                animation: grain 8s steps(10) infinite;
            }

            @keyframes grain {
              0%, 100% { transform: translate(0, 0); }
              10% { transform: translate(-5%, -10%); }
              20% { transform: translate(-15%, 5%); }
              30% { transform: translate(7%, -25%); }
              40% { transform: translate(-5%, 25%); }
              50% { transform: translate(-15%, 10%); }
              60% { transform: translate(15%, 0%); }
              70% { transform: translate(0%, 15%); }
              80% { transform: translate(-5%, 20%); }
              90% { transform: translate(10%, 5%); }
            }
        </style>
    </head>

    <body
        class="font-inter bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-black text-gray-900 dark:text-white no-scroll relative z-0">

        <!-- Navigation -->
        <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 py-6">
            <div class="container mx-auto px-4 flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-3 group cursor-pointer">
                    <div class="relative">
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-red-500 to-yellow-500 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-12">
                            <span class="text-white font-black text-xl">W</span>
                        </div>
                        <div
                            class="absolute inset-0 w-12 h-12 bg-gradient-to-r from-red-500 to-yellow-500 rounded-full opacity-0 group-hover:opacity-30 transition-opacity duration-300 animate-pulse-slow">
                        </div>
                    </div>
                    <div>
                        <span
                            class="font-black text-xl bg-gradient-to-r from-red-500 to-yellow-500 bg-clip-text text-transparent">
                            Warung Ayam Geprek
                        </span>
                        <div class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                            Pedasnya Bikin Nagih!
                        </div>
                    </div>
                </div>
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#hero"
                        class="nav-link relative font-semibold text-gray-700 dark:text-gray-300 hover:text-red-500 dark:hover:text-red-400 py-2">
                        Beranda
                        <span
                            class="nav-underline absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-red-500 to-yellow-500 transition-all duration-300"></span>
                    </a>
                    <a href="#about"
                        class="nav-link relative font-semibold text-gray-700 dark:text-gray-300 hover:text-red-500 dark:hover:text-red-400 py-2">
                        Tentang Kami
                        <span
                            class="nav-underline absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-red-500 to-yellow-500 transition-all duration-300"></span>
                    </a>
                    <a href="#menu"
                        class="nav-link relative font-semibold text-gray-700 dark:text-gray-300 hover:text-red-500 dark:hover:text-red-400 py-2">
                        Menu
                        <span
                            class="nav-underline absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-red-500 to-yellow-500 transition-all duration-300"></span>
                    </a>
                    <a href="#testimonial"
                        class="nav-link relative font-semibold text-gray-700 dark:text-gray-300 hover:text-red-500 dark:hover:text-red-400 py-2">
                        Testimoni
                        <span
                            class="nav-underline absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-red-500 to-yellow-500 transition-all duration-300"></span>
                    </a>
                    <a href="#contact"
                        class="nav-link relative font-semibold text-gray-700 dark:text-gray-300 hover:text-red-500 dark:hover:text-red-400 py-2">
                        Kontak
                        <span
                            class="nav-underline absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-red-500 to-yellow-500 transition-all duration-300"></span>
                    </a>

                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="glass p-3 rounded-full hover:scale-110 transition-all duration-300"
                        aria-label="Toggle theme">
                        <i data-lucide="sun" class="w-6 h-6 text-yellow-500 theme-icon-light"></i>
                        <i data-lucide="moon" class="w-6 h-6 text-blue-400 theme-icon-dark hidden"></i>
                    </button>
                </div>
                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center space-x-3">
                    <button id="theme-toggle-mobile" class="glass p-2 rounded-lg" aria-label="Toggle theme">
                        <i data-lucide="sun" class="w-5 h-5 text-yellow-500 theme-icon-light"></i>
                        <i data-lucide="moon" class="w-5 h-5 text-blue-400 theme-icon-dark hidden"></i>
                    </button>
                    <button id="mobile-menu-toggle" class="glass p-2 rounded-lg">
                        <i data-lucide="menu" class="w-6 h-6 text-gray-700 dark:text-gray-300 menu-icon"></i>
                        <i data-lucide="x" class="w-6 h-6 text-gray-700 dark:text-gray-300 close-icon hidden"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden max-h-0 opacity-0 overflow-hidden transition-all duration-300">
                <div class="glass mx-4 mt-4 p-4 rounded-2xl space-y-3">
                    <a href="#hero"
                        class="mobile-nav-link block py-3 px-4 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-white/10 dark:hover:bg-black/10 font-medium">Beranda</a>
                    <a href="#about"
                        class="mobile-nav-link block py-3 px-4 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-white/10 dark:hover:bg-black/10 font-medium">Tentang
                        Kami</a>
                    <a href="#menu"
                        class="mobile-nav-link block py-3 px-4 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-white/10 dark:hover:bg-black/10 font-medium">Menu</a>
                    <a href="#testimonial"
                        class="mobile-nav-link block py-3 px-4 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-white/10 dark:hover:bg-black/10 font-medium">Testimoni</a>
                    <a href="#contact"
                        class="mobile-nav-link block py-3 px-4 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-white/10 dark:hover:bg-black/10 font-medium">Kontak</a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section id="hero" class="hero-section relative overflow-hidden">
            <!-- Background Image with Parallax -->
            <div id="hero-bg" class="parallax-element absolute inset-0 bg-cover bg-center bg-no-repeat"
                style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://images.unsplash.com/photo-1696340034876-6245523babfa?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NDk1Nzd8MHwxfHNlYXJjaHwxfHxheWFtJTIwZ2VwcmVrfGVufDB8fHx8MTc1ODQ0MzA1OXww&ixlib=rb-4.1.0&q=85'); background-attachment: fixed;">
            </div>

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 via-transparent to-yellow-500/10"></div>

            <!-- Floating Elements -->

            <!-- Content -->
            <div class="relative z-10 text-center text-white px-4 max-w-6xl mx-auto">
                <div class="space-y-8">
                    <!-- Title -->
                    <h1 id="hero-title" class="hero-title font-black text-glow opacity-0"
                        style="font-family: 'Poppins', sans-serif;">
                        Ayam Geprek Mantap,
                        <br>
                        <span class="text-yellow-400 animate-glow">Pedasnya Bikin Nagih!</span>
                    </h1>

                    <!-- Subtitle -->
                    <p id="hero-subtitle" class="hero-subtitle opacity-90 leading-relaxed max-w-4xl mx-auto opacity-0">
                        Nikmati ayam geprek crispy dengan sambal rahasia kami.
                        <br class="hidden md:block">
                        Pesan online atau kunjungi warung kami sekarang!
                    </p>

                    <!-- CTA Buttons -->
                    <div id="hero-buttons"
                        class="flex flex-col sm:flex-row gap-6 justify-center items-center opacity-0">
                        <a href="#order"
                            class="glass-strong px-8 py-4 rounded-xl font-bold text-lg bg-gradient-to-r from-red-500/80 to-yellow-500/80 hover:from-red-600/90 hover:to-yellow-600/90 text-white shadow-xl hover:scale-105 hover-glow transition-all duration-300">
                            🔥 Pesan Sekarang
                        </a>

                        <a href="#menu"
                            class="glass-strong px-8 py-4 rounded-xl font-bold text-lg border-2 border-yellow-500/60 text-yellow-400 hover:bg-yellow-500/20 hover:border-yellow-500/80 hover:scale-105 transition-all duration-300">
                            📋 Lihat Menu
                        </a>
                    </div>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                <div class="glass p-3 rounded-full">
                    <div class="w-1 h-6 bg-gradient-to-b from-red-500 to-yellow-500 rounded-full animate-pulse-slow">
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-20 px-4 relative overflow-hidden" data-animate="fade-up">
            <div class="absolute top-0 left-0 w-64 h-64 bg-rose-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-amber-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/4 w-64 h-64 bg-pink-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
            <div
                class="absolute inset-0 bg-gradient-to-br from-red-50/50 to-yellow-50/50 dark:from-red-900/10 dark:to-yellow-900/10">
            </div>

            <div class="container mx-auto relative z-10">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Text Content -->
                    <div class="space-y-8">
                        <h2 class="text-4xl md:text-6xl font-black text-gray-800 dark:text-white mb-6"
                            style="font-family: 'Poppins', sans-serif;">
                            Tentang
                            <span class="bg-gradient-to-r from-red-500 to-yellow-500 bg-clip-text text-transparent">
                                Warung Kami
                            </span>
                        </h2>

                        <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
                            Warung Ayam Geprek Mantap lahir dari cinta terhadap rasa otentik dan inovasi.
                            Sejak 2020, kami menyajikan ayam geprek dengan sambal rahasia yang bikin nagih.
                        </p>

                        <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 leading-relaxed mb-8">
                            Setiap gigitan adalah perpaduan sempurna antara renyah, pedas, dan gurih.
                            Kami menggunakan bahan-bahan pilihan dan teknik memasak yang telah disempurnakan.
                        </p>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div
                                class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/10 dark:hover:bg-black/10 transition-colors duration-200">
                                <span class="text-2xl">🌿</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Bahan Segar Pilihan</span>
                            </div>
                            <div
                                class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/10 dark:hover:bg-black/10 transition-colors duration-200">
                                <span class="text-2xl">👨‍🍳</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Chef Berpengalaman</span>
                            </div>
                            <div
                                class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/10 dark:hover:bg-black/10 transition-colors duration-200">
                                <span class="text-2xl">🔥</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Sambal Rahasia</span>
                            </div>
                            <div
                                class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/10 dark:hover:bg-black/10 transition-colors duration-200">
                                <span class="text-2xl">⭐</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Sejak 2020</span>
                            </div>
                        </div>
                    </div>

                    <!-- Image -->
                    <div class="relative">
                        <div class="glass p-2 rounded-2xl hover:scale-105 transition-transform duration-500">
                            <div class="relative overflow-hidden rounded-xl">
                                <img src="https://images.unsplash.com/photo-1526069631228-723c945bea6b"
                                    alt="Interior Warung Ayam Geprek Mantap"
                                    class="w-full h-96 object-cover transition-transform duration-700 hover:scale-110">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent">
                                </div>

                                <!-- Stats Overlays -->
                                <div class="absolute top-4 right-4">
                                    <div class="glass p-3 rounded-xl">
                                        <div class="text-center text-white">
                                            <div class="text-2xl font-bold">5000+</div>
                                            <div class="text-xs">Pelanggan Puas</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="absolute bottom-4 left-4">
                                    <div class="glass p-3 rounded-xl">
                                        <div class="text-center text-white">
                                            <div class="text-2xl font-bold">4.8⭐</div>
                                            <div class="text-xs">Rating</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Decorative Elements -->
                        <div
                            class="absolute -top-6 -right-6 w-32 h-32 bg-gradient-to-r from-yellow-400/20 to-red-400/20 rounded-full blur-xl animate-float">
                        </div>
                        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-gradient-to-r from-red-400/20 to-yellow-400/20 rounded-full blur-lg animate-float"
                            style="animation-delay: 1s;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Menu Section -->
        <section id="menu"
            class="py-20 px-4 relative overflow-hidden bg-gradient-to-br from-gray-50/50 to-white/50 dark:from-gray-900/50 dark:to-black/50"
            data-animate="fade-up">
            <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-green-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 right-1/4 w-64 h-64 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-5 dark:opacity-10">
                <div class="absolute top-10 left-10 w-32 h-32 bg-red-500 rounded-full blur-3xl animate-float"></div>
                <div class="absolute bottom-10 right-10 w-40 h-40 bg-yellow-500 rounded-full blur-3xl animate-float"
                    style="animation-delay: 2s;"></div>
            </div>
            <div class="container mx-auto relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-800 dark:text-white mb-6"
                        style="font-family: 'Poppins', sans-serif;">
                        Menu
                        <span class="bg-gradient-to-r from-red-500 to-yellow-500 bg-clip-text text-transparent">
                            Favorit
                        </span>
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                        Pilihan menu terlezat dengan cita rasa yang tidak akan pernah Anda lupakan.
                        Setiap hidangan dibuat dengan penuh cinta dan keahlian.
                    </p>
                </div>
                <!-- Menu Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" id="menu-grid">
                    <!-- Menu items will be generated by JavaScript -->
                    <div class="glass group overflow-hidden p-0 opacity-0 hover:scale-105 rounded-2xl will-change-transform animate-fade-in-up"
                        style="animation-delay: 0ms;">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTY2NzR8MHwxfHNlYXJjaHwxfHxmcmllZCUyMGNoaWNrZW58ZW58MHx8fHwxNzU4NDQzMDkzfDA&ixlib=rb-4.1.0&q=85"
                                alt="Ayam Geprek Original"
                                class="w-full h-56 object-cover transition-transform duration-700 group-hover:scale-110">
                            <div
                                class="absolute top-4 left-4 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                TERLARIS
                            </div>
                            <div class="absolute top-4 right-4">
                                <div class="glass p-3 rounded-xl">
                                    <div class="text-center text-white">
                                        <div class="text-lg font-bold">Rp17.500</div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <h3
                                class="text-xl font-bold text-gray-800 dark:text-white group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors duration-200">
                                Ayam Geprek Original
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                Ayam crispy geprek dengan sambal pedas khas yang membakar lidah.
                            </p>
                            <button
                                class="w-full glass-strong px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-red-500/80 to-yellow-500/80 hover:from-red-600/90 hover:to-yellow-600/90 text-white hover:scale-105 transition-all duration-300">
                                🛒 Pesan Sekarang
                            </button>
                        </div>
                    </div>

                    <div class="glass group overflow-hidden p-0 opacity-0 hover:scale-105 rounded-2xl will-change-transform animate-fade-in-up"
                        style="animation-delay: 200ms;">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1562607635-4608ff48a859?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NDQ2MzR8MHwxfHNlYXJjaHwxfHxJbmRvbmVzaWFuJTIwZm9vZHxlbnwwfHx8fDE3NTg0NDMwODh8MA&ixlib=rb-4.1.0&q=85"
                                alt="Ayam Geprek + Nasi Uduk"
                                class="w-full h-56 object-cover transition-transform duration-700 group-hover:scale-110">
                            <div
                                class="absolute top-4 left-4 bg-gradient-to-r from-yellow-500 to-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                FAVORIT
                            </div>
                            <div class="absolute top-4 right-4">
                                <div class="glass p-3 rounded-xl">
                                    <div class="text-center text-white">
                                        <div class="text-lg font-bold">Rp24.000</div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <h3
                                class="text-xl font-bold text-gray-800 dark:text-white group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors duration-200">
                                Ayam Geprek + Nasi Uduk
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                Kombinasi ayam geprek dan nasi uduk gurih yang sempurna.
                            </p>
                            <button
                                class="w-full glass-strong px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-red-500/80 to-yellow-500/80 hover:from-red-600/90 hover:to-yellow-600/90 text-white hover:scale-105 transition-all duration-300">
                                🛒 Pesan Sekarang
                            </button>
                        </div>
                    </div>

                    <div class="glass group overflow-hidden p-0 opacity-0 hover:scale-105 rounded-2xl will-change-transform animate-fade-in-up"
                        style="animation-delay: 400ms;">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTY2NzR8MHwxfHNlYXJjaHwyfHxmcmllZCUyMGNoaWNrZW58ZW58MHx8fHwxNzU4NDQzMDkzfDA&ixlib=rb-4.1.0&q=85"
                                alt="Ayam Geprek Mozarella"
                                class="w-full h-56 object-cover transition-transform duration-700 group-hover:scale-110">
                            <div
                                class="absolute top-4 left-4 bg-gradient-to-r from-purple-500 to-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                PREMIUM
                            </div>
                            <div class="absolute top-4 right-4">
                                <div class="glass p-3 rounded-xl">
                                    <div class="text-center text-white">
                                        <div class="text-lg font-bold">Rp30.000</div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <h3
                                class="text-xl font-bold text-gray-800 dark:text-white group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors duration-200">
                                Ayam Geprek Mozarella
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                Ayam geprek dengan keju mozarella leleh yang creamy.
                            </p>
                            <button
                                class="w-full glass-strong px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-red-500/80 to-yellow-500/80 hover:from-red-600/90 hover:to-yellow-600/90 text-white hover:scale-105 transition-all duration-300">
                                🛒 Pesan Sekarang
                            </button>
                        </div>
                    </div>

                    <div class="glass group overflow-hidden p-0 opacity-0 hover:scale-105 rounded-2xl will-change-transform animate-fade-in-up"
                        style="animation-delay: 600ms;">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1539755530862-00f623c00f52?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NDQ2MzR8MHwxfHNlYXJjaHwyfHxJbmRvbmVzaWFuJTIwZm9vZHxlbnwwfHx8fDE3NTg0NDMwODh8MA&ixlib=rb-4.1.0&q=85"
                                alt="Es Teh Manis"
                                class="w-full h-56 object-cover transition-transform duration-700 group-hover:scale-110">
                            <div
                                class="absolute top-4 left-4 bg-gradient-to-r from-green-500 to-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                SEGAR
                            </div>
                            <div class="absolute top-4 right-4">
                                <div class="glass p-3 rounded-xl">
                                    <div class="text-center text-white">
                                        <div class="text-lg font-bold">Rp5.000</div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <h3
                                class="text-xl font-bold text-gray-800 dark:text-white group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors duration-200">
                                Es Teh Manis
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                Es teh manis segar, pas banget buat temenin pedas.
                            </p>
                            <button
                                class="w-full glass-strong px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-red-500/80 to-yellow-500/80 hover:from-red-600/90 hover:to-yellow-600/90 text-white hover:scale-105 transition-all duration-300">
                                🛒 Pesan Sekarang
                            </button>
                        </div>
                    </div>

                    <div class="glass group overflow-hidden p-0 opacity-0 hover:scale-105 rounded-2xl will-change-transform animate-fade-in-up"
                        style="animation-delay: 800ms;">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1681378128359-a5c2492a3535?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NDQ2MzR8MHwxfHNlYXJjaHwzfHxJbmRvbmVzaWFuJTIwZm9vZHxlbnwwfHx8fDE3NTg0NDMwODh8MA&ixlib=rb-4.1.0&q=85"
                                alt="Es Jeruk"
                                class="w-full h-56 object-cover transition-transform duration-700 group-hover:scale-110">
                            <div
                                class="absolute top-4 left-4 bg-gradient-to-r from-orange-500 to-yellow-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                SEHAT
                            </div>
                            <div class="absolute top-4 right-4">
                                <div class="glass p-3 rounded-xl">
                                    <div class="text-center text-white">
                                        <div class="text-lg font-bold">Rp7.000</div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <h3
                                class="text-xl font-bold text-gray-800 dark:text-white group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors duration-200">
                                Es Jeruk
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                Es jeruk segar dengan perasan asli, vitamin C tinggi.
                            </p>
                            <button
                                class="w-full glass-strong px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-red-500/80 to-yellow-500/80 hover:from-red-600/90 hover:to-yellow-600/90 text-white hover:scale-105 transition-all duration-300">
                                🛒 Pesan Sekarang
                            </button>
                        </div>
                    </div>
                </div>


            </div>
        </section>

        <!-- Testimonial Section -->
        <section id="testimonial" class="py-20 px-4 relative overflow-hidden" data-animate="fade-up">
            <div class="container mx-auto relative z-10">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-800 dark:text-white mb-6"
                        style="font-family: 'Poppins', sans-serif;">
                        Testimoni
                        <span class="bg-gradient-to-r from-red-500 to-yellow-500 bg-clip-text text-transparent">
                            Pelanggan
                        </span>
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                        Apa kata mereka yang sudah mencoba kelezatan Ayam Geprek Mantap?
                    </p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="glass p-8 rounded-2xl text-center hover:scale-105 transition-transform duration-300">
                        <img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="Pelanggan 1"
                            class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-yellow-400">
                        <p class="text-gray-600 dark:text-gray-300 mb-4">"Sambalnya juara! Pedasnya pas, bikin nagih. Ayamnya juga crispy banget. Pasti balik lagi!"</p>
                        <h4 class="font-bold text-lg text-gray-800 dark:text-white">Andi Saputra</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Food Blogger</p>
                    </div>
                    <div class="glass p-8 rounded-2xl text-center hover:scale-105 transition-transform duration-300">
                        <img src="https://i.pravatar.cc/150?u=a042581f4e29026704e" alt="Pelanggan 2"
                            class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-yellow-400">
                        <p class="text-gray-600 dark:text-gray-300 mb-4">"Tempatnya asik buat nongkrong, pelayanannya juga cepat. Ayam geprek mozarellanya the best!"</p>
                        <h4 class="font-bold text-lg text-gray-800 dark:text-white">Siti Aminah</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Mahasiswi</p>
                    </div>
                    <div class="glass p-8 rounded-2xl text-center hover:scale-105 transition-transform duration-300">
                        <img src="https://i.pravatar.cc/150?u=a042581f4e29026704f" alt="Pelanggan 3"
                            class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-yellow-400">
                        <p class="text-gray-600 dark:text-gray-300 mb-4">"Pesan lewat WhatsApp gampang banget, gratis ongkir pula. Makan enak gak perlu ribet."</p>
                        <h4 class="font-bold text-lg text-gray-800 dark:text-white">Budi Hartono</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Karyawan Swasta</p>
                    </div>
                </div>
            </div>
        </section>
        <section id="order"
            class="py-20 px-4 relative overflow-hidden bg-gradient-to-br from-red-50/20 to-yellow-50/20 dark:from-red-900/10 dark:to-yellow-900/10"
            data-animate="fade-up">
            <!-- Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-1/4 right-1/4 w-64 h-64 bg-red-400/5 rounded-full blur-3xl animate-float">
                </div>
                <div class="absolute bottom-1/4 left-1/4 w-80 h-80 bg-yellow-400/5 rounded-full blur-3xl animate-float"
                    style="animation-delay: 3s;"></div>
            </div>
            <div class="container mx-auto max-w-6xl relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-800 dark:text-white mb-6"
                        style="font-family: 'Poppins', sans-serif;">
                        Pesan
                        <span class="bg-gradient-to-r from-red-500 to-yellow-500 bg-clip-text text-transparent">
                            Sekarang
                        </span>
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                        Pilih cara pemesanan yang paling mudah untuk Anda.
                        <span class="font-bold text-red-500"> Delivery gratis</span> untuk pemesanan minimal Rp50.000!
                        🚀
                    </p>
                </div>
                <!-- Order Options -->
                <div class="grid lg:grid-cols-2 gap-8 mb-16">
                    <!-- WhatsApp Order -->
                    <div
                        class="glass p-8 rounded-2xl hover:scale-[1.02] transition-all duration-500 border-green-300/30">
                        <div class="text-center mb-8">
                            <div
                                class="w-20 h-20 mx-auto mb-6 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-transform duration-300">
                                <i data-lucide="message-square" class="w-10 h-10 text-white"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Order via WhatsApp</h3>
                            <p class="text-gray-600 dark:text-gray-300">Pesan langsung dan chat dengan kami 💬</p>
                        </div>
                        <form id="whatsapp-form" class="space-y-6">
                            <input type="text" placeholder="🧑 Nama Lengkap *" required
                                class="w-full glass border border-green-300/30 focus:border-green-500 text-lg p-4 rounded-xl bg-transparent">

                            <input type="tel" placeholder="📱 Nomor Telepon *" required
                                class="w-full glass border border-green-300/30 focus:border-green-500 text-lg p-4 rounded-xl bg-transparent">

                            <input type="text" placeholder="📍 Alamat Delivery (opsional)"
                                class="w-full glass border border-green-300/30 focus:border-green-500 text-lg p-4 rounded-xl bg-transparent">

                            <textarea placeholder="📝 Tulis pesanan Anda (contoh: 2x Ayam Geprek Original, 1x Es Teh) *" required
                                class="w-full glass border border-green-300/30 focus:border-green-500 text-lg p-4 rounded-xl bg-transparent min-h-[120px] resize-none"></textarea>

                            <select
                                class="w-full glass border border-green-300/30 focus:border-green-500 text-lg p-4 rounded-xl bg-transparent">
                                <option value="">🌶️ Pilih Level Pedas</option>
                                <option value="tidak-pedas">😊 Tidak Pedas</option>
                                <option value="pedas-ringan">🌶️ Pedas Ringan</option>
                                <option value="pedas-sedang">🔥 Pedas Sedang</option>
                                <option value="pedas-mantap">🌋 Pedas Mantap</option>
                                <option value="extra-pedas">💀 Extra Pedas</option>
                            </select>

                            <button type="button"
                                class="w-full glass-strong px-8 py-4 rounded-xl font-bold text-lg bg-gradient-to-r from-green-500/80 to-emerald-500/80 hover:from-green-600/90 hover:to-emerald-600/90 text-white hover:scale-105 hover-glow transition-all duration-300">
                                <i data-lucide="message-square" class="w-5 h-5 mr-2 inline"></i>
                                🚀 Pesan via WhatsApp
                            </button>
                        </form>
                    </div>
                    <!-- Form Order -->
                    <div
                        class="glass p-8 rounded-2xl hover:scale-[1.02] transition-all duration-500 border-red-300/30">
                        <div class="text-center mb-8">
                            <div
                                class="w-20 h-20 mx-auto mb-6 bg-gradient-to-r from-red-500 to-pink-500 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-transform duration-300">
                                <i data-lucide="shopping-cart" class="w-10 h-10 text-white"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Form Pemesanan</h3>
                            <p class="text-gray-600 dark:text-gray-300">Isi form dan kami akan hubungi Anda 📞</p>
                        </div>
                        <form id="contact-form" class="space-y-6">
                            <input type="text" placeholder="🧑 Nama Lengkap *" required
                                class="w-full glass border border-red-300/30 focus:border-red-500 text-lg p-4 rounded-xl bg-transparent">

                            <input type="tel" placeholder="📱 Nomor Telepon *" required
                                class="w-full glass border border-red-300/30 focus:border-red-500 text-lg p-4 rounded-xl bg-transparent">

                            <input type="text" placeholder="📍 Alamat Lengkap"
                                class="w-full glass border border-red-300/30 focus:border-red-500 text-lg p-4 rounded-xl bg-transparent">

                            <textarea placeholder="📝 Detail Pesanan *" required
                                class="w-full glass border border-red-300/30 focus:border-red-500 text-lg p-4 rounded-xl bg-transparent min-h-[100px] resize-none"></textarea>

                            <textarea placeholder="💭 Catatan Tambahan"
                                class="w-full glass border border-red-300/30 focus:border-red-500 text-lg p-4 rounded-xl bg-transparent resize-none"></textarea>

                            <button type="button"
                                class="w-full glass-strong px-8 py-4 rounded-xl font-bold text-lg bg-gradient-to-r from-red-500/80 to-pink-500/80 hover:from-red-600/90 hover:to-pink-600/90 text-white hover:scale-105 hover-glow transition-all duration-300">
                                <i data-lucide="shopping-cart" class="w-5 h-5 mr-2 inline"></i>
                                📨 Kirim Pesanan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="mt-12 text-center">
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                        💬 Ada pertanyaan? Hubungi kami langsung:
                    </p>
                    <div class="flex justify-center space-x-8">
                        <a href="tel:+6281234567890"
                            class="flex items-center space-x-3 glass p-4 rounded-xl hover:scale-110 transition-all duration-300 text-red-500 hover:text-red-600">
                            <i data-lucide="phone" class="w-6 h-6"></i>
                            <span class="font-semibold">+62 812-3456-7890</span>
                        </a>
                        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                            class="flex items-center space-x-3 glass p-4 rounded-xl hover:scale-110 transition-all duration-300 text-green-500 hover:text-green-600">
                            <i data-lucide="message-square" class="w-6 h-6"></i>
                            <span class="font-semibold">WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 px-4 relative overflow-hidden" data-animate="fade-up">
            <!-- Background -->
            <div
                class="absolute inset-0 bg-gradient-to-br from-red-50/20 to-yellow-50/20 dark:from-red-900/10 dark:to-yellow-900/10">
            </div>

            <!-- Floating Elements -->
            <div class="container mx-auto relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-800 dark:text-white mb-6"
                        style="font-family: 'Poppins', sans-serif;">
                        Lokasi &
                        <span class="bg-gradient-to-r from-red-500 to-yellow-500 bg-clip-text text-transparent">
                            Kontak
                        </span>
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                        Kunjungi warung kami atau hubungi untuk pemesanan dan delivery.
                        Kami siap melayani Anda dengan sepenuh hati!
                    </p>
                </div>
                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Map -->
                    <div class="relative">
                        <div class="glass p-4 rounded-2xl hover:scale-[1.02] transition-transform duration-500">
                            <div class="rounded-xl overflow-hidden h-96 relative">
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.0!2d106.8!3d-6.2!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTInMDAuMCJTIDEwNsKwNDgnMDAuMCJF!5e0!3m2!1sen!2sid!4v1234567890!5m2!1sen!2sid"
                                    width="100%" height="100%" style="border:0; border-radius:16px;"
                                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                    class="grayscale hover:grayscale-0 transition-all duration-700 rounded-xl">
                                </iframe>

                                <!-- Map Overlay -->
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="glass p-4 rounded-xl">
                                        <div class="flex items-center space-x-3 text-white">
                                            <i data-lucide="map-pin" class="w-5 h-5 text-red-400"></i>
                                            <div>
                                                <div class="font-semibold">Warung Ayam Geprek Mantap</div>
                                                <div class="text-sm opacity-80">Jl. Raya No. 123, Jakarta Selatan</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Contact Info -->
                    <div class="space-y-6" id="contact-cards">
                        <div
                            class="glass p-6 rounded-2xl hover:scale-105 transition-all duration-300 group border-green-300/30">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="p-4 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i data-lucide="message-square" class="w-8 h-8 text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-1">WhatsApp</h3>
                                    <p class="text-gray-600 dark:text-gray-300 mb-1">Chat langsung untuk pemesanan</p>
                                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">+62
                                        812-3456-7890</p>
                                </div>
                                <button
                                    onclick="window.open('https://wa.me/6281234567890?text=Halo! Saya ingin memesan makanan dari Warung Ayam Geprek Mantap.', '_blank')"
                                    class="glass-strong px-4 py-2 rounded-xl font-bold bg-gradient-to-r from-green-500/80 to-emerald-500/80 hover:from-green-600/90 hover:to-emerald-600/90 text-white hover:scale-105 transition-all duration-300">
                                    Chat
                                </button>
                            </div>
                        </div>

                        <div
                            class="glass p-6 rounded-2xl hover:scale-105 transition-all duration-300 group border-blue-300/30">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="p-4 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i data-lucide="phone" class="w-8 h-8 text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-1">Telepon</h3>
                                    <p class="text-gray-600 dark:text-gray-300 mb-1">Hubungi kami langsung</p>
                                    <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">+62 812-3456-7890
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="glass p-6 rounded-2xl hover:scale-105 transition-all duration-300 group border-orange-300/30">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="p-4 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-2xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i data-lucide="clock" class="w-8 h-8 text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-1">Jam Buka</h3>
                                    <p class="text-gray-600 dark:text-gray-300 mb-1">10.00 – 22.00 WIB</p>
                                    <p class="text-lg font-semibold text-orange-600 dark:text-orange-400">Setiap Hari
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="glass p-6 rounded-2xl hover:scale-105 transition-all duration-300 group border-purple-300/30">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="p-4 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i data-lucide="map-pin" class="w-8 h-8 text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-1">Alamat Lengkap
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-300 mb-1">Jl. Raya No. 123</p>
                                    <p class="text-lg font-semibold text-purple-600 dark:text-purple-400">Jakarta
                                        Selatan</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Dekat Stasiun MRT Blok M</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Quick Order CTA -->

            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-zinc-100 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 py-12 px-4">
            <div class="container mx-auto">
                <!-- Main Footer Content -->
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Brand & Description -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                                <span class="text-zinc-800 dark:text-zinc-200 font-bold text-xl">W</span>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">Warung Ayam Geprek Mantap</h3>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm">Pedasnya Bikin Nagih!</p>
                            </div>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-6">
                            Sajian ayam geprek terlezat dengan sambal khas yang bikin nagih sejak 2020.
                            Kami berkomitmen menyajikan makanan berkualitas dengan harga terjangkau.
                        </p>

                        <!-- Social Media -->
                        <div class="flex space-x-4">
                            <a href="https://instagram.com/warungayamgeprekmantap" target="_blank"
                                rel="noopener noreferrer"
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 p-3 rounded-full transition-all duration-300 transform hover:scale-110">
                                <i data-lucide="instagram" class="w-5 h-5"></i>
                            </a>
                            <a href="https://tiktok.com/@warungayamgeprekmantap" target="_blank"
                                rel="noopener noreferrer"
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 p-3 rounded-full transition-all duration-300 transform hover:scale-110">
                                <i data-lucide="message-square" class="w-5 h-5"></i>
                            </a>
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 p-3 rounded-full transition-all duration-300 transform hover:scale-110">
                                <i data-lucide="message-square" class="w-5 h-5"></i>
                            </a>
                        </div>
                    </div>
                    <!-- Contact Info -->
                    <div>
                        <h4 class="text-xl font-bold mb-6">Kontak Kami</h4>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="map-pin" class="w-5 h-5 text-zinc-500 dark:text-zinc-500 mt-1 flex-shrink-0"></i>
                                <div>
                                    <p class="text-zinc-600 dark:text-zinc-400">Jl. Raya No. 123</p>
                                    <p class="text-zinc-600 dark:text-zinc-400">Jakarta Selatan</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <i data-lucide="phone" class="w-5 h-5 text-zinc-500 dark:text-zinc-500"></i>
                                <p class="text-zinc-600 dark:text-zinc-400">+62 812-3456-7890</p>
                            </div>

                            <div class="flex items-start space-x-3">
                                <i data-lucide="clock" class="w-5 h-5 text-zinc-500 dark:text-zinc-500 mt-1"></i>
                                <div>
                                    <p class="text-zinc-600 dark:text-zinc-400">10.00 – 22.00 WIB</p>
                                    <p class="text-zinc-500 dark:text-zinc-500 text-sm">Setiap Hari</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-xl font-bold mb-6">Menu Populer</h4>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-1 h-1 bg-amber-500 rounded-full"></div>
                                <span
                                    class="text-zinc-600 dark:text-zinc-400 text-sm hover:text-white transition-colors duration-200 cursor-pointer">Ayam
                                    Geprek Original</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-1 h-1 bg-amber-500 rounded-full"></div>
                                <span
                                    class="text-zinc-600 dark:text-zinc-400 text-sm hover:text-white transition-colors duration-200 cursor-pointer">Ayam
                                    Geprek Mozarella</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-1 h-1 bg-amber-500 rounded-full"></div>
                                <span
                                    class="text-zinc-600 dark:text-zinc-400 text-sm hover:text-white transition-colors duration-200 cursor-pointer">Nasi
                                    Uduk Komplit</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-1 h-1 bg-amber-500 rounded-full"></div>
                                <span
                                    class="text-zinc-600 dark:text-zinc-400 text-sm hover:text-white transition-colors duration-200 cursor-pointer">Es
                                    Teh Manis</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-1 h-1 bg-amber-500 rounded-full"></div>
                                <span
                                    class="text-zinc-600 dark:text-zinc-400 text-sm hover:text-white transition-colors duration-200 cursor-pointer">Sambal
                                    Extra Pedas</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Divider -->
                <div class="border-t border-zinc-500 dark:border-zinc-700 my-8"></div>
                <!-- Bottom Footer -->
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="text-zinc-600 dark:text-zinc-400 text-sm">
                        © 2025 Warung Ayam Geprek Mantap. All rights reserved.
                    </div>

                    <div class="flex items-center space-x-1 text-zinc-600 dark:text-zinc-400 text-sm">
                        <span>Made with</span>
                        <i data-lucide="heart" class="w-4 h-4 text-amber-500 fill-current"></i>
                        <span>for food lovers</span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- JavaScript -->
        <script>
            // Initialize Lucide Icons
            lucide.createIcons();

            // Theme Management
            function initializeTheme() {
                const savedTheme = localStorage.getItem('theme');
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                    document.documentElement.classList.add('dark');
                    updateThemeIcons(true);
                } else {
                    document.documentElement.classList.remove('dark');
                    updateThemeIcons(false);
                }
            }

            function updateThemeIcons(isDark) {
                const lightIcons = document.querySelectorAll('.theme-icon-light');
                const darkIcons = document.querySelectorAll('.theme-icon-dark');

                if (isDark) {
                    lightIcons.forEach(icon => icon.classList.add('hidden'));
                    darkIcons.forEach(icon => icon.classList.remove('hidden'));
                } else {
                    lightIcons.forEach(icon => icon.classList.remove('hidden'));
                    darkIcons.forEach(icon => icon.classList.add('hidden'));
                }
            }

            function toggleTheme() {
                const isDark = document.documentElement.classList.contains('dark');

                if (isDark) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    updateThemeIcons(false);
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    updateThemeIcons(true);
                }
            }

            // Smooth Parallax
            function updateParallax() {
                const scrolled = window.pageYOffset;
                const parallaxElements = document.querySelectorAll('.parallax-element');

                // Only apply parallax on desktop
                if (window.innerWidth > 768) {
                    parallaxElements.forEach(element => {
                        const speed = element.dataset.speed || 0.5;
                        const yPos = -(scrolled * speed);
                        element.style.transform = `translate3d(0, ${yPos}px, 0)`;
                    });
                }
            }

            // Navbar Scroll Effect
            function updateNavbar() {
                const navbar = document.getElementById('navbar');
                const scrolled = window.pageYOffset > 50;

                if (scrolled) {
                    navbar.classList.add('glass-strong', 'py-3');
                    navbar.classList.remove('py-6');
                } else {
                    navbar.classList.remove('glass-strong', 'py-3');
                    navbar.classList.add('py-6');
                }
            }

            // Intersection Observer for Animations
            function initializeAnimations() {
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-fade-in-up');
                        }
                    });
                }, observerOptions);

                // Observe elements that need animation
                const animatedElements = document.querySelectorAll('[data-animate="fade-up"]');
                animatedElements.forEach(el => observer.observe(el));
            }

            // Hero Animation Sequence
            function animateHeroElements() {
                setTimeout(() => {
                    document.getElementById('hero-title').classList.add('animate-fade-in-up');
                }, 300);

                setTimeout(() => {
                    document.getElementById('hero-subtitle').classList.add('animate-fade-in-up');
                }, 600);

                setTimeout(() => {
                    document.getElementById('hero-buttons').classList.add('animate-fade-in-up');
                }, 900);
            }

            // Mobile Menu Toggle
            function initializeMobileMenu() {
                const toggleBtn = document.getElementById('mobile-menu-toggle');
                const mobileMenu = document.getElementById('mobile-menu');
                const menuIcon = toggleBtn.querySelector('.menu-icon');
                const closeIcon = toggleBtn.querySelector('.close-icon');
                let isOpen = false;

                toggleBtn.addEventListener('click', () => {
                    isOpen = !isOpen;

                    if (isOpen) {
                        mobileMenu.classList.remove('max-h-0', 'opacity-0');
                        mobileMenu.classList.add('max-h-80', 'opacity-100');
                        menuIcon.classList.add('hidden');
                        closeIcon.classList.remove('hidden');
                    } else {
                        mobileMenu.classList.add('max-h-0', 'opacity-0');
                        mobileMenu.classList.remove('max-h-80', 'opacity-100');
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    }
                });

                // Close menu when clicking on links
                const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
                mobileNavLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (isOpen) {
                            toggleBtn.click();
                        }
                    });
                });
            }

            // Smooth Scroll for Navigation
            function initializeSmoothScroll() {
                const navLinks = document.querySelectorAll('a[href^="#"]');

                navLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const targetId = link.getAttribute('href');
                        const targetElement = document.querySelector(targetId);

                        if (targetElement) {
                            const offsetTop = targetElement.offsetTop - 80; // Account for navbar

                            window.scrollTo({
                                top: offsetTop,
                                behavior: 'smooth'
                            });
                        }
                    });
                });

                // Add hover effects to nav links
                const navLinkElements = document.querySelectorAll('.nav-link');
                navLinkElements.forEach(link => {
                    const underline = link.querySelector('.nav-underline');

                    link.addEventListener('mouseenter', () => {
                        underline.style.width = '100%';
                    });

                    link.addEventListener('mouseleave', () => {
                        underline.style.width = '0';
                    });
                });
            }

            // Event Listeners
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize theme
                initializeTheme();

                // Initialize components
                initializeMobileMenu();
                initializeSmoothScroll();
                initializeAnimations();

                // Start hero animations
                animateHeroElements();

                // Enable scrolling after page load
                setTimeout(() => {
                    document.body.classList.remove('no-scroll');
                }, 100);

                // Theme toggle event listeners
                document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
                document.getElementById('theme-toggle-mobile').addEventListener('click', toggleTheme);
            });

            // Scroll event listener with throttling
            window.addEventListener('scroll', () => {
                updateParallax();
                updateNavbar();
            }, {
                passive: true
            });

            // Resize event listener
            window.addEventListener('resize', () => {
                // Reset parallax on resize
                if (window.innerWidth <= 768) {
                    const parallaxElements = document.querySelectorAll('.parallax-element');
                    parallaxElements.forEach(element => {
                        element.style.transform = 'translate3d(0, 0, 0)';
                    });
                }
            });

            // System theme change listener
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                        updateThemeIcons(true);
                    } else {
                        document.documentElement.classList.remove('dark');
                        updateThemeIcons(false);
                    }
                }
            });
        </script>
    </body>

</html>
