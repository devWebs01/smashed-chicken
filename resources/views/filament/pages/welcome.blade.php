<x-app>

    @volt
        <div>
            <div class="bg-orange-50 w-full">

                <!-- Hero Section -->
                <div
                    class="flex flex-col lg:flex-row items-center justify-center lg:justify-between px-4 py-10 md:px-10 lg:px-24 gap-10 lg:gap-20">
                    <div class="flex flex-col items-center lg:items-start gap-7 text-center lg:text-left">
                        <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
                            <span class="text-orange-600">Ayam Geprek Pedas </span>
                            <span class="text-zinc-800">Nikmat, <br />Diantar Cepat </span>
                            <span class="text-orange-600">ke Rumah </span>
                            <span class="text-zinc-800">Anda!</span>
                        </h1>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button
                                class="w-64 h-12 bg-orange-500 rounded-full shadow-md text-white text-base font-bold tracking-tight">
                                Pesan Sekarang
                            </button>
                            <button
                                class="w-64 h-12 rounded-full shadow-md border-2 border-orange-600 text-orange-600 text-base font-bold tracking-tight">
                                Lacak Pesanan
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-center items-start">
                        <img class="w-full max-w-md lg:max-w-lg h-auto" src="https://placehold.co/500x500"
                            alt="Ayam Geprek" />
                    </div>
                </div>

                <!-- Feature Boxes -->
                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-8 px-4 py-6 md:px-10 lg:px-24 bg-white rounded-3xl shadow-lg mx-4 md:mx-10 lg:mx-24 my-10">
                    <div class="flex flex-col items-center text-center gap-4">
                        <img class="w-20 h-20 object-cover mx-auto" src="{{ asset('assets/images/fast-delivery.png') }}"
                            alt="Pengiriman Cepat" />
                        <div>
                            <h3 class="text-zinc-800 text-2xl font-bold leading-tight">Pengiriman Cepat</h3>
                            <p class="text-neutral-400 text-base font-medium leading-tight">
                                Janji Pengiriman Dalam 30 Menit.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col items-center text-center gap-4">
                        <img class="w-20 h-20 object-cover mx-auto" src="{{ asset('assets/images/fresh.png') }}"
                            alt="Ayam Segar Pilihan" />
                        <div>
                            <h3 class="text-zinc-800 text-2xl font-bold leading-tight">Ayam Segar Pilihan</h3>
                            <p class="text-neutral-400 text-base font-medium leading-tight">
                                Ayam Pilihan Terbaik, Diolah Segar.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col items-center text-center gap-4">
                        <img class="w-20 h-20 object-cover mx-auto" src="{{ asset('assets/images/box.png') }}"
                            alt="Gratis Pengiriman" />
                        <div>
                            <h3 class="text-zinc-800 text-2xl font-bold leading-tight">Gratis Pengiriman</h3>
                            <p class="text-neutral-400 text-base font-medium leading-tight">
                                Pengiriman Makanan Anda Sepenuhnya Gratis.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Why Ayam Geprek  is Special in  Section -->
            <div class="w-full bg-white py-10 md:py-20 px-4 md:px-10 lg:px-24">
                <div class="flex flex-col lg:flex-row items-center lg:justify-between gap-5 mb-10 text-center lg:text-left">
                    <h2 class="text-4xl md:text-5xl font-bold leading-tight">
                        <span class="text-zinc-800">Mengapa </span>
                        <span class="text-orange-600">Jadi <br />Pilihan Terbaik </span>
                    </h2>
                    <p class="text-neutral-400 text-lg md:text-xl font-normal max-w-md">
                        Kami hadir  dengan komitmen menyajikan ayam geprek pedas nikmat yang tak terlupakan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div class="flex flex-col items-center text-center gap-4">
                        <img class="w-20 h-20 object-cover" src="{{ asset('assets/images/map-pointer.png') }}"
                            alt="Rasa Khas " />
                        <h3 class="text-zinc-800 text-2xl font-bold leading-tight">Rasa Khas </h3>
                        <p class="text-neutral-400 text-base font-medium leading-tight">
                            Resep sambal turun-temurun dengan sentuhan rempah lokal .
                        </p>
                    </div>
                    <div class="flex flex-col items-center text-center gap-4">
                        <img class="w-20 h-20 object-cover" src="{{ asset('assets/images/chili-pepper.png') }}"
                            alt="Pedasnya Pas" />
                        <h3 class="text-zinc-800 text-2xl font-bold leading-tight">Pedasnya Pas untuk Lidah </h3>
                        <p class="text-neutral-400 text-base font-medium leading-tight">
                            Pilih level pedasmu, dari biasa hingga nampol!
                        </p>
                    </div>
                    <div class="flex flex-col items-center text-center gap-4">
                        <img class="w-20 h-20 object-cover" src="{{ asset('assets/images/drumsticks.png') }}"
                            alt="Komunitas" />
                        <h3 class="text-zinc-800 text-2xl font-bold leading-tight">Komunitas Ayam Geprek  </h3>
                        <p class="text-neutral-400 text-base font-medium leading-tight">
                            Bagian dari keluarga besar pecinta ayam geprek .
                        </p>
                    </div>
                </div>

            </div>

            <!-- Delivered Page Section -->
            <div class="w-full bg-white py-10 md:py-20 px-4 md:px-10 lg:px-24">

                <!-- Our Best Delivered Indian Dish -->
                <div class="flex flex-col lg:flex-row items-center lg:justify-between gap-5 mb-10 text-center lg:text-left">
                    <h2 class="text-4xl md:text-5xl font-bold leading-tight">
                        <span class="text-zinc-800">Ayam Geprek </span>
                        <span class="text-orange-600">Favorit <br />Pelanggan </span>
                        <span class="text-zinc-800">Kami</span>
                    </h2>
                    <p class="text-neutral-400 text-lg md:text-xl font-normal max-w-md">
                        Kami Tidak Hanya Mengantar Ayam Geprek, Kami Memberikan Pengalaman Rasa Terbaik.
                    </p>
                </div>

                <!-- Food Items -->
                <div class="flex flex-col md:flex-row justify-center lg:justify-between items-center gap-10">
                    <div class="flex flex-col items-center gap-4">
                        <div class="relative w-64 h-64">
                            <img class="absolute inset-0 m-auto w-48 h-48 object-cover" src="https://placehold.co/250x250"
                                alt="Ayam Geprek Original" />
                            <div
                                class="absolute inset-0 m-auto w-60 h-60 rounded-full border-4 border-dashed border-orange-600">
                            </div>
                        </div>
                        <h3 class="text-zinc-800 text-xl font-semibold text-center">Ayam Geprek Original</h3>
                        <p class="text-orange-600 text-xl font-medium text-center">Pesan Sekarang &gt;</p>
                    </div>
                    <div class="flex flex-col items-center gap-4">
                        <div class="relative w-64 h-64">
                            <img class="absolute inset-0 m-auto w-48 h-48 object-cover" src="https://placehold.co/250x250"
                                alt="Ayam Geprek Mozzarella" />
                            <div
                                class="absolute inset-0 m-auto w-60 h-60 rounded-full border-4 border-dashed border-orange-600">
                            </div>
                        </div>
                        <h3 class="text-zinc-800 text-xl font-semibold text-center">Ayam Geprek Mozzarella</h3>
                        <p class="text-orange-600 text-xl font-medium text-center">Pesan Sekarang &gt;</p>
                    </div>
                    <div class="flex flex-col items-center gap-4">
                        <div class="relative w-64 h-64">
                            <img class="absolute inset-0 m-auto w-48 h-48 object-cover" src="https://placehold.co/250x250"
                                alt="Ayam Geprek Sambal Matah" />
                            <div
                                class="absolute inset-0 m-auto w-60 h-60 rounded-full border-4 border-dashed border-orange-600">
                            </div>
                        </div>
                        <h3 class="text-zinc-800 text-xl font-semibold text-center">Ayam Geprek Sambal Matah</h3>
                        <p class="text-orange-600 text-xl font-medium text-center">Pesan Sekarang &gt;</p>
                    </div>
                </div>
            </div>

            <!-- Menu Page Section -->
            <div class="w-full bg-white py-10 md:py-20 px-4 md:px-10 lg:px-24">
                <div class="flex flex-col md:flex-row items-center justify-between gap-5 mb-20 text-center md:text-left">
                    <div>
                        <h2 class="text-4xl md:text-5xl font-bold leading-tight">
                            <span class="text-zinc-800">Menu </span>
                            <span class="text-orange-600"> Tersedia </span>

                        </h2>
                        <p class="text-neutral-400 text-base font-semibold">
                            Pilih Menu Ayam Geprek Favoritmu, Pedasnya Bikin Nagih!
                        </p>
                    </div>
                    <button class="px-6 py-3 bg-orange-500 rounded-full text-white text-lg font-semibold shadow-md">Lihat
                        Semua</button>
                </div>

                <!-- Menu Items -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-16">

                    <!-- Item 1 -->
                    <div class="relative bg-orange-50 rounded-2xl shadow-lg p-5 pt-20 flex flex-col items-center">
                        <img class="absolute -top-10 w-36 h-36 object-cover rounded-full shadow-md"
                            src="https://placehold.co/200x200" alt="Ayam Geprek Original" />
                        <div class="mt-10 text-center">
                            <h3 class="text-zinc-800 text-xl font-medium leading-tight">
                                <span class="text-orange-600">Ayam Geprek <br /></span>
                                <span>Original</span>
                            </h3>
                            <div class="flex items-center justify-center gap-1 my-2">
                                <p class="text-zinc-800 text-3xl font-medium">₹250</p>
                            </div>
                            <div class="flex items-center justify-between w-full mt-5">

                                <button class="px-5 py-2 bg-orange-500 rounded-full text-white text-sm font-semibold">Beli
                                    Sekarang</button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 1 -->
                    <div class="relative bg-orange-50 rounded-2xl shadow-lg p-5 pt-20 flex flex-col items-center">
                        <img class="absolute -top-10 w-36 h-36 object-cover rounded-full shadow-md"
                            src="https://placehold.co/200x200" alt="Ayam Geprek Original" />
                        <div class="mt-10 text-center">
                            <h3 class="text-zinc-800 text-xl font-medium leading-tight">
                                <span class="text-orange-600">Ayam Geprek <br /></span>
                                <span>Original</span>
                            </h3>
                            <div class="flex items-center justify-center gap-1 my-2">
                                <p class="text-zinc-800 text-3xl font-medium">₹250</p>
                            </div>
                            <div class="flex items-center justify-between w-full mt-5">

                                <button class="px-5 py-2 bg-orange-500 rounded-full text-white text-sm font-semibold">Beli
                                    Sekarang</button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 1 -->
                    <div class="relative bg-orange-50 rounded-2xl shadow-lg p-5 pt-20 flex flex-col items-center">
                        <img class="absolute -top-10 w-36 h-36 object-cover rounded-full shadow-md"
                            src="https://placehold.co/200x200" alt="Ayam Geprek Original" />
                        <div class="mt-10 text-center">
                            <h3 class="text-zinc-800 text-xl font-medium leading-tight">
                                <span class="text-orange-600">Ayam Geprek <br /></span>
                                <span>Original</span>
                            </h3>
                            <div class="flex items-center justify-center gap-1 my-2">
                                <p class="text-zinc-800 text-3xl font-medium">₹250</p>
                            </div>
                            <div class="flex items-center justify-between w-full mt-5">

                                <button class="px-5 py-2 bg-orange-500 rounded-full text-white text-sm font-semibold">Beli
                                    Sekarang</button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 1 -->
                    <div class="relative bg-orange-50 rounded-2xl shadow-lg p-5 pt-20 flex flex-col items-center">
                        <img class="absolute -top-10 w-36 h-36 object-cover rounded-full shadow-md"
                            src="https://placehold.co/200x200" alt="Ayam Geprek Original" />
                        <div class="mt-10 text-center">
                            <h3 class="text-zinc-800 text-xl font-medium leading-tight">
                                <span class="text-orange-600">Ayam Geprek <br /></span>
                                <span>Original</span>
                            </h3>
                            <div class="flex items-center justify-center gap-1 my-2">
                                <p class="text-zinc-800 text-3xl font-medium">₹250</p>
                            </div>
                            <div class="flex items-center justify-between w-full mt-5">

                                <button class="px-5 py-2 bg-orange-500 rounded-full text-white text-sm font-semibold">Beli
                                    Sekarang</button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 1 -->
                    <div class="relative bg-orange-50 rounded-2xl shadow-lg p-5 pt-20 flex flex-col items-center">
                        <img class="absolute -top-10 w-36 h-36 object-cover rounded-full shadow-md"
                            src="https://placehold.co/200x200" alt="Ayam Geprek Original" />
                        <div class="mt-10 text-center">
                            <h3 class="text-zinc-800 text-xl font-medium leading-tight">
                                <span class="text-orange-600">Ayam Geprek <br /></span>
                                <span>Original</span>
                            </h3>
                            <div class="flex items-center justify-center gap-1 my-2">
                                <p class="text-zinc-800 text-3xl font-medium">₹250</p>
                            </div>
                            <div class="flex items-center justify-between w-full mt-5">

                                <button class="px-5 py-2 bg-orange-500 rounded-full text-white text-sm font-semibold">Beli
                                    Sekarang</button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 1 -->
                    <div class="relative bg-orange-50 rounded-2xl shadow-lg p-5 pt-20 flex flex-col items-center">
                        <img class="absolute -top-10 w-36 h-36 object-cover rounded-full shadow-md"
                            src="https://placehold.co/200x200" alt="Ayam Geprek Original" />
                        <div class="mt-10 text-center">
                            <h3 class="text-zinc-800 text-xl font-medium leading-tight">
                                <span class="text-orange-600">Ayam Geprek <br /></span>
                                <span>Original</span>
                            </h3>
                            <div class="flex items-center justify-center gap-1 my-2">
                                <p class="text-zinc-800 text-3xl font-medium">₹250</p>
                            </div>
                            <div class="flex items-center justify-between w-full mt-5">

                                <button class="px-5 py-2 bg-orange-500 rounded-full text-white text-sm font-semibold">Beli
                                    Sekarang</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Offer Page Section -->
            <div
                class="max-w-5xl mb-10 py-10 md:py-20 px-4 md:px-10 lg:px-24 md:w-full mx-2 md:mx-auto flex flex-col items-center justify-center text-center bg-gradient-to-b from-orange-500 to-red-600 rounded-3xl p-10 text-white">
                <h2 class="text-4xl md:text-5xl md:leading-[60px] font-semibold max-w-xl mt-5">
                    Dapatkan Penawaran Spesial Ayam Geprek  Sekarang!
                </h2>
                <p class="text-lg md:text-xl mt-4 max-w-2xl">
                    Jangan lewatkan diskon dan promo menarik untuk hidangan ayam geprek favoritmu. Pedasnya bikin nagih,
                    harganya bikin senyum!
                </p>

            </div>
        </div>
    @endvolt
</x-app>
