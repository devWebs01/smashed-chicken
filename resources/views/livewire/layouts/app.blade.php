<!doctype html>
<html>

    <head>
        <meta charset="UTF-8" />
        <title>{{ $setting->name }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Wendy+One&display=swap');

            @layer base {
                body {
                    font-family: 'Lexend', sans-serif;
                }

                .font-wendy {
                    font-family: 'Wendy One', cursive;
                }
            }
        </style>
    </head>

    <body>
        <div id="top" class="min-h-screen bg-white overflow-hidden">
            <!-- Home Page Section -->
            <div class="bg-orange-50 w-full">
                <!-- Header -->
                <header class="flex items-center justify-between px-4 py-5 md:px-10 lg:px-24 relative">
                    <div class="flex flex-col items-center gap-2">
                        <p class="text-2xl font-wendy whitespace-nowrap">
                            <span class="text-orange-600">{{ $setting->name }} </span>
                        </p>
                        <div class="w-36 h-0.5 bg-orange-600"></div>
                    </div>

                    <!-- Hamburger Menu for Mobile -->
                    <div class="md:hidden flex items-center">
                        <button id="hamburger" class="text-zinc-800 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16m-4 6h4"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Navigation Links -->
                    <x-nav></x-nav>

                    <div class="hidden md:flex items-center gap-4">
                        <img class="w-12 h-12 object-cover rounded-full" src="{{ Storage::url($setting->logo) }}"
                            alt="Profile" />
                    </div>
                </header>
            </div>

            <main>
                {{ $slot }}
            </main>

            <!-- Footer Section -->
            <footer
                class="w-full bg-orange-50 py-10 md:py-20 px-4 md:px-10 lg:px-24 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 text-center md:text-left">
                <div class="flex flex-col items-center md:items-start gap-7">
                    <p class="text-2xl font-wendy whitespace-nowrap">
                        <span class="text-orange-600 text-wrap">{{ $setting->name }} </span>
                    </p>
                    <p class="text-neutral-400 text-base font-normal">Ayam Geprek Mother ©2023 Semua Hak Dilindungi</p>
                    <p class="text-neutral-400 text-base font-normal">Oleh - Piyush Prajapat</p>
                </div>

                <div class="flex flex-col items-center md:items-start gap-3">
                    <h4 class="text-orange-600 text-2xl font-semibold">Menu</h4>
                    <a href="#" class="text-neutral-400 text-base font-normal">Beranda</a>
                    <a href="#" class="text-neutral-400 text-base font-normal">Penawaran</a>
                    <a href="#" class="text-neutral-400 text-base font-normal">Layanan</a>
                    <a href="#" class="text-neutral-400 text-base font-normal">Tentang Kami</a>
                </div>

                <div class="flex flex-col items-center md:items-start gap-3">
                    <h4 class="text-orange-600 text-2xl font-semibold">Informasi</h4>
                    <a href="#" class="text-neutral-400 text-base font-normal">Menu</a>
                    <a href="#" class="text-neutral-400 text-base font-normal">Kualitas</a>
                    <a href="#" class="text-neutral-400 text-base font-normal">Buat Pilihan</a>
                    <a href="#" class="text-neutral-400 text-base font-normal">Pengiriman Cepat</a>
                </div>

                <div class="flex flex-col items-center md:items-start gap-3">
                    <h4 class="text-orange-600 text-2xl font-semibold">Kontak</h4>
                    <p class="text-neutral-400 text-base font-normal">
                        {{ $setting->phone }}
                    </p>
                    <p class="text-neutral-400 text-base font-normal">
                        {{ $setting->address }}
                    </p>
                    <p class="text-neutral-400 text-base font-normal">
                        <a href="/admin/login">Admin</a>
                    </p>

                </div>
            </footer>
        </div>

        <script>
            document.getElementById('hamburger').addEventListener('click', function() {
                document.getElementById('nav-links').classList.toggle('hidden');
                document.getElementById('nav-links').classList.toggle('flex');
            });
        </script>
    </body>

</html>
