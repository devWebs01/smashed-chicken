<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp bot keywords, messages, and settings
    |
    */

    'keywords' => [
        'menu' => ['menu', 'produk', 'pesan', 'geprek', 'makan', 'order'],
        'confirm' => ['ya', 'y', 'yes', 'iya', 'ok', 'konfirmasi'],
        'edit' => ['edit', 'ubah', 'change', 'ganti'],
        'tambah' => ['tambah'],
        'selesai' => ['selesai'],
        'reset' => ['reset'],
        'batal' => ['batal'],
    ],

    'messages' => [
        'welcome' => "Selamat datang! Silakan perkenalkan diri Anda.\n\nKetik nama lengkap Anda:",
        'name_empty' => 'Nama tidak boleh kosong. Silakan ketik nama lengkap Anda:',
        'address_prompt' => "Terima kasih, {name}!\n\nSilakan kirim alamat lengkap Anda untuk pengiriman (jika diperlukan):",
        'info_complete' => "Terima kasih! Data Anda telah tersimpan.\n\nSekarang Anda bisa mulai memesan.\nKetik *menu* untuk melihat daftar produk.",
        'invalid_product' => "Maaf, nomor produk *{number}* tidak valid atau tidak tersedia.\nKetik *menu* untuk melihat daftar produk yang tersedia.",
        'no_valid_products' => "Maaf, tidak ada produk valid dalam pesanan Anda.\nNomor produk mungkin tidak tersedia atau format salah.\n\nKetik *menu* untuk melihat daftar produk yang tersedia.",
        'no_pending_order' => 'Tidak ada pesanan yang pending. Ketik *menu* untuk mulai pesan.',
        'order_expired' => 'Pesanan expired karena tidak ada aktivitas. Silakan mulai pesan baru dengan ketik *menu*.',
        'invalid_delivery' => 'Metode pengiriman tidak valid. Ketik *takeaway* atau *delivery*.',
        'invalid_payment' => 'Metode pembayaran tidak valid. Ketik *cash* atau *transfer*.',
        'order_error' => "Maaf, terjadi kesalahan saat memproses pesanan Anda.\nSilakan coba lagi dalam beberapa saat atau hubungi admin untuk bantuan.",
        'system_error' => "Maaf, terjadi kesalahan sistem.\nSilakan coba lagi atau ketik *menu* untuk mulai pesan.",
        'order_cancelled' => "Pesanan dibatalkan. Silakan pilih produk lagi.\nKetik *menu* untuk melihat daftar produk.",
        'order_not_found' => 'Pesanan tidak ditemukan.',
        'no_pending_orders' => 'Anda tidak memiliki pesanan pending.',
        'invalid_order_index' => 'Nomor pesanan tidak valid.',
        'order_cancelled_success' => 'Pesanan #{id} telah dibatalkan.',
        'products_added' => "*Produk Berhasil Ditambahkan!*\n\n{items}\n\n*Total Sekarang: Rp {total}*\n\n📝 *Cara Menambah Lagi:*\n• Kirim nomor produk (contoh: *1* untuk 1 porsi)\n• Atau nomor=jumlah (contoh: *1=3* untuk 3 porsi)\n• Multiple produk: *1=2, 2=1*\n\n✅ Ketik *selesai* untuk lanjut ke pembayaran\n🔄 Ketik *menu* untuk lihat menu lagi",
        'order_complete' => "Order selesai. Terima kasih!\nKetik *menu* jika ingin pesan lagi.",
        'reset_done' => "Data cache direset. Silakan mulai dari awal.\nKetik *halo* untuk perkenalan.",
        'delivery_prompt' => "Pilih metode pengiriman:\n- Ketik *takeaway* untuk ambil sendiri\n- Ketik *delivery* untuk diantar",
        'address_prompt_delivery' => 'Silakan kirim alamat lengkap pengiriman.',
        'payment_prompt' => "Pilih metode pembayaran:\n- Ketik *cash* untuk bayar tunai\n- Ketik *transfer* untuk transfer bank",
        'final_review' => "*Review Pesanan Akhir:*\n\n{items}\nPengiriman: {delivery}\nAlamat: {address}\nPembayaran: {payment}\n*Total: Rp {total}*\n\nKetik *ya*, *y*, *yes*, *ok* untuk konfirmasi akhir\nKetik *edit*, *ubah* untuk mengubah pesanan.",
        'review' => "*Review Pesanan Anda:*\n\n{items}\n*Total Keseluruhan: Rp {total}*\n\nApakah sudah benar?\n- Ketik *ya*, *y*, *yes*, *ok* untuk konfirmasi pesanan\n- Ketik *edit*, *ubah* untuk ubah pesanan\n- Ketik *menu* untuk lihat menu lagi",
        'default_reply' => "🤔 Maaf, pesan Anda tidak dapat dipahami.\n\n📋 Ketik *menu* untuk melihat daftar produk kami.\n\n📝 *CARA PEMESANAN:*\n\n• *1 produk:* Ketik nomor produk\n   Contoh: *1* (1 porsi Ayam Geprek Original)\n\n• *Jumlah lebih:* nomor=jumlah\n   Contoh: *1=3* (3 porsi Ayam Geprek Original)\n\n• *Multiple produk:* pisahkan dengan koma\n   Contoh: *1=2, 2=1* (2 porsi produk 1 + 1 porsi produk 2)\n\n• *Qty berbeda:* pisahkan dengan spasi\n   Contoh: *1=2 3=1 5=2* (2 porsi produk 1, 1 porsi produk 3, 2 porsi produk 5)\n\n🔧 *PERINTAH LAIN:*\n• *menu* - Lihat daftar produk\n• *ya* - Konfirmasi pesanan\n• *edit* - Batalkan dan mulai ulang\n• *batal* - Lihat pesanan pending\n\nSilakan coba lagi! 😊",
        'pending_orders' => "*Pesanan Pending Anda:*\n\n{orders}\nKetik *batal [nomor]* untuk membatalkan pesanan.\nContoh: *batal 1*",
        'last_order' => "*Pesanan Terakhir Anda:*\n\nOrder #{id} - Total: Rp {total}\n{items}\n\nKirim produk tambahan dengan format yang sama.\nContoh: *3=1* untuk tambah 1 porsi produk 3",
    ],

    'cache_ttl' => [
        'customer_info' => 3600, // 1 hour
        'order' => 600, // 10 minutes
        'add_to_order' => 1800, // 30 minutes
        'dedup' => 300, // 5 minutes
    ],
];
