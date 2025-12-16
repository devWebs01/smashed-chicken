<?php
use function Livewire\Volt\{state, mount, usesPagination, with, on, computed};
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderNotificationService;

usesPagination();

state(['favoriteProducts', 'cart' => [], 'showCart' => false, 'showCheckout' => false, 'customerName' => '', 'customerPhone' => '', 'customerAddress' => '', 'deliveryMethod' => 'delivery', 'paymentMethod' => 'cod', 'orderSuccess' => false, 'orderNumber' => null]);

mount(function () {
    $this->favoriteProducts = Product::withCount('orderItems')->orderByDesc('order_items_count')->limit(3)->get();
    $this->cart = session('cart', []);
});

$allProducts = computed(function () {
    return Product::inRandomOrder()->paginate(6);
});

$addToCart = function ($productId, $quantity = 1) {
    $product = Product::find($productId);
    if (!$product) {
        return;
    }

    $productKey = (string) $productId;

    if (isset($this->cart[$productKey])) {
        $this->cart[$productKey]['quantity'] += $quantity;
    } else {
        $this->cart[$productKey] = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'image' => $product->image,
            'quantity' => $quantity,
        ];
    }

    session(['cart' => $this->cart]);
    $this->dispatch('cart-updated', count: $this->getCartItemCount());
};

$removeFromCart = function ($productId) {
    $productKey = (string) $productId;
    unset($this->cart[$productKey]);
    session(['cart' => $this->cart]);
    $this->dispatch('cart-updated', count: $this->getCartItemCount());
};

$updateCartQuantity = function ($productId, $quantity) {
    if ($quantity <= 0) {
        $this->removeFromCart($productId);
        return;
    }

    $productKey = (string) $productId;
    if (isset($this->cart[$productKey])) {
        $this->cart[$productKey]['quantity'] = $quantity;
        session(['cart' => $this->cart]);
        $this->dispatch('cart-updated', count: $this->getCartItemCount());
    }
};

$getCartItemCount = function () {
    return array_sum(array_column($this->cart, 'quantity'));
};

$getCartTotal = function () {
    $total = 0;
    foreach ($this->cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
};

$toggleCart = function () {
    $this->showCart = !$this->showCart;
};

$proceedToCheckout = function () {
    if (empty($this->cart)) {
        session()->flash('error', 'Keranjang masih kosong');
        return;
    }
    $this->showCart = false;
    $this->showCheckout = true;
};

$submitOrder = function () {
    $this->validate(
        [
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:20',
            'customerAddress' => 'required_if:deliveryMethod,delivery|string|max:500',
            'deliveryMethod' => 'required|in:delivery,takeaway',
            'paymentMethod' => 'required|in:cod,cash',
        ],
        [
            'customerName.required' => 'Nama harus diisi',
            'customerPhone.required' => 'Nomor HP harus diisi',
            'customerAddress.required_if' => 'Alamat harus diisi untuk pengiriman',
        ],
    );

    if (empty($this->cart)) {
        session()->flash('error', 'Keranjang masih kosong');
        return;
    }

    try {
        // Create order
        $order = Order::create([
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_address' => $this->deliveryMethod === 'delivery' ? $this->customerAddress : null,
            'status' => Order::STATUS_PENDING,
            'order_date_time' => now(),
            'payment_method' => $this->paymentMethod,
            'total_price' => $this->getCartTotal(),
            'delivery_method' => $this->deliveryMethod,
            'device_id' => null, // Web order
        ]);

        // Create order items
        foreach ($this->cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
        }

        // Send notification (if WhatsApp service is available)
        try {
            app(OrderNotificationService::class)->notifyNewOrder($order);
        } catch (\Exception $e) {
            // Log error but don't fail the order
            \Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
        }

        // Clear cart and show success
        $this->cart = [];
        session()->forget('cart');
        $this->orderNumber = $order->id;
        $this->orderSuccess = true;
        $this->showCheckout = false;

        // Reset form
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerAddress = '';
        $this->deliveryMethod = 'delivery';
        $this->paymentMethod = 'cod';

        $this->dispatch('cart-updated', count: 0);
    } catch (\Exception $e) {
        session()->flash('error', 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
        \Log::error('Order creation failed: ' . $e->getMessage());
    }
};

$closeSuccessModal = function () {
    $this->orderSuccess = false;
    $this->orderNumber = null;
};

?>
<x-app>
    @volt
        <div>
            <!-- Order form -->
            @include('welcome-pages.order-form')

            <!-- Checkout model -->
            @include('welcome-pages.checkout-modal')

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
                            <a href="#menus">
                                <button
                                    class="w-64 h-12 bg-orange-500 rounded-full shadow-md text-white text-base font-bold tracking-tight hover:bg-orange-600">
                                    Pesan Sekarang
                                </button>
                            </a>
                        </div>
                    </div>
                    <div class="flex justify-center items-start">
                        <img class="w-full max-w-md lg:max-w-lg h-auto" src="{{ asset('assets/images/hero.png') }}"
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
                        Kami hadir dengan komitmen menyajikan ayam geprek pedas nikmat yang tak terlupakan.
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
                        <h3 class="text-zinc-800 text-2xl font-bold leading-tight">Komunitas Ayam Geprek </h3>
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
                    @foreach ($favoriteProducts as $product)
                        <div class="flex flex-col items-center gap-4">
                            <div class="relative w-64 h-64">
                                <img class="absolute inset-0 m-auto w-48 h-48 object-cover rounded-full shadow-md"
                                    src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" />
                                <div
                                    class="absolute inset-0 m-auto w-60 h-60 rounded-full border-4 border-dashed border-orange-600">
                                </div>
                            </div>
                            <h3 class="text-zinc-800 text-xl font-semibold text-center">{{ $product->name }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Menu Page Section -->
            <div id="menus" class="w-full bg-white py-10 md:py-20 px-4 md:px-10 lg:px-24">
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
                    <button
                        class="px-6 py-3 bg-orange-500 rounded-full text-white text-lg font-semibold shadow-md">HOT!!!</button>
                </div>

                <!-- Menu Items -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-16">

                    @foreach ($this->allProducts as $product)
                        <!-- Item 1 -->
                        <div class="relative bg-orange-50 rounded-2xl shadow-lg p-5 pt-20 flex flex-col items-center">
                            <img class="absolute -top-10 w-36 h-36 object-cover rounded-full shadow-md"
                                src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" />

                            <div class="mt-10 text-center">
                                <h3 class="text-zinc-800 text-xl font-medium leading-tight">
                                    <span class="text-orange-600"> {{ $product->name }} </span>
                                </h3>
                                <div class="flex items-center justify-center gap-1 my-2">
                                    <p class="text-zinc-800 text-3xl font-medium">{{ formatRupiah($product->price) }}
                                    </p>
                                </div>
                                <div class="flex mx-auto items-center justify-between w-full mt-5">
                                    <button wire:click="addToCart({{ $product->id }})"
                                        class="px-5 mx-auto py-2 bg-orange-500 rounded-full text-white text-sm font-semibold hover:bg-orange-600">Beli
                                        Sekarang</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mx-auto w-full mt-8">
                    {{ $this->allProducts->links(data: ['scrollTo' => false]) }}

                </div>
            </div>

            <!-- Offer Page Section -->
            <div
                class="max-w-5xl mb-10 py-10 md:py-20 px-4 md:px-10 lg:px-24 md:w-full mx-2 md:mx-auto flex flex-col items-center justify-center text-center bg-gradient-to-b from-orange-500 to-red-600 rounded-3xl p-10 text-white">
                <h2 class="text-4xl md:text-5xl md:leading-[60px] font-semibold max-w-xl mt-5">
                    Dapatkan Penawaran Spesial Ayam Geprek Sekarang!
                </h2>
                <p class="text-lg md:text-xl mt-4 max-w-2xl">
                    Jangan lewatkan diskon dan promo menarik untuk hidangan ayam geprek favoritmu. Pedasnya bikin nagih,
                    harganya bikin senyum!
                </p>

            </div>
        </div>
    @endvolt
</x-app>
