# Dokumen Pengujian Sistem - Ayam Geprek

## 📋 Informasi Dokumen

| Item | Deskripsi |
|------|-----------|
| **Nama Sistem** | Sistem Penjualan Ayam Geprek via WhatsApp |
| **Versi** | 1.0.0 |
| **Tanggal Dibuat** | 2026-01-21 |
| **Platform** | Laravel 12 + Filament 4 + Fonnte API |
| **Tester** | - |
| **Tanggal Pengujian** | - |

---

## 1️⃣ Modul Autentikasi & Authorization

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| Login Admin Filament | 1. Buka URL `/admin`<br>2. Masukkan email & password valid<br>3. Klik tombol Login | Email: `admin@example.com`<br>Password: `password` | - Berhasil login<br>- Diarahkan ke dashboard<br>- Tampilan admin panel muncul | | |
| Login dengan Credential Salah | 1. Buka URL `/admin`<br>2. Masukkan email/password salah<br>3. Klik tombol Login | Email: `wrong@example.com`<br>Password: `wrongpass` | - Gagal login<br>- Pesan error muncul<br>- Tetap di halaman login | | |
| Logout | 1. Login sebagai admin<br>2. Klik tombol logout<br>3. Konfirmasi logout | - | - Berhasil logout<br>- Diarahkan ke halaman login | | |
| Role-Based Access Control | 1. Login sebagai user tanpa role admin<br>2. Coba akses `/admin` | User biasa | - Akses ditolak<br>- Pesan "Unauthorized" atau redirect ke home | | |
| Permission Check | 1. Login sebagai user dengan role tertentu<br>2. Coba akses resource tanpa permission | User tanpa permission | - Akses ditolak<br>- Error 403 Forbidden | | |

---

## 2️⃣ Modul Manajemen Produk

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| Tambah Produk Baru | 1. Menuju halaman Products<br>2. Klik "Create"<br>3. Isi form produk<br>4. Klik "Create" | Name: "Paha Geprek"<br>Description: "Paha ayam geprek pedas"<br>Price: 15000<br>Image: [upload file] | - Produk berhasil dibuat<br>- Muncul di daftar produk<br>- Notifikasi sukses | | |
| Edit Produk | 1. Buka daftar produk<br>2. Klik edit pada produk<br>3. Update data<br>4. Klik "Save" | Update harga jadi 18000 | - Data produk terupdate<br>- Perubahan tersimpan di database | | |
| Hapus Produk | 1. Buka daftar produk<br>2. Klik delete pada produk<br>3. Konfirmasi delete | - | - Produk terhapus dari database<br>- Tidak muncul di daftar produk | | |
| Validasi Form Produk - Kosong | 1. Coba tambah produk tanpa isi form<br>2. Klik "Create" | Form kosong | - Error validation muncul<br>- Produk tidak tersimpan<br>- Pesan error field required | | |
| Validasi Form Produk - Harga Negatif | 1. Isi form dengan harga negatif<br>2. Klik "Create" | Price: -5000 | - Error validation muncul<br>- Pesan "harga must be positive" | | |
| Upload Gambar Produk | 1. Tambah produk dengan upload gambar<br>2. Pilih file gambar valid<br>3. Submit | File: JPG/PNG max 2MB | - Gambar berhasil diupload<br>- Path tersimpan di database<br>- Gambar tampil di daftar | | |
| Upload Gambar - Format Salah | 1. Upload file bukan gambar<br>2. Submit | File: .exe/.pdf | - Error validation<br>- Pesan "file must be image" | | |
| Search & Filter Produk | 1. Buka daftar produk<br>2. Gunakan search bar<br>3. Ketik keyword | Keyword: "geprek" | - Daftar terfilter<br>- Hanya produk yang cocok muncul | | |
| Pagination Produk | 1. Buka daftar produk<br>2. Scroll ke bawah<br>3. Klik halaman berikutnya | - | - Pagination berfungsi<br>- Data per halaman sesuai config | | |

---

## 3️⃣ Modul Manajemen Pesanan

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| Buat Order via Admin | 1. Menuju Orders<br>2. Klik "Create"<br>3. Isi data pelanggan & produk<br>4. Submit | Customer: "Budi"<br>Phone: "08123456789"<br>Items: Produk 1 x 2 | - Order berhasil dibuat<br>- Status: PENDING<br>- Order items tersimpan | | |
| Update Status Order | 1. Buka detail order<br>2. Klik tombol update status<br>3. Pilih status baru | Status: PENDING → PROCESSING | - Status berhasil diupdate<br>- Timestamp tercatat<br>- Notifikasi muncul | | |
| Update Status - Invalid Transition | 1. Coba ubah status dari COMPLETED ke PENDING | Status: COMPLETED → PENDING | - Error/Warning<br>- Transisi ditolak jika tidak valid | | |
| View Detail Order | 1. Buka daftar orders<br>2. Klik view pada order | - | - Detail order muncul lengkap<br>- Items terdisplay<br>- Customer info tampil | | |
| Filter Order by Status | 1. Buka daftar orders<br>2. Gunakan filter status<br>3. Pilih "PENDING" | Filter: status = PENDING | - Hanya order PENDING muncul<br>- Filter berfungsi | | |
| Filter Order by Date Range | 1. Gunakan filter tanggal<br>2. Pilih rentang waktu | Dari: 2026-01-01<br>Sampai: 2026-01-31 | - Order dalam rentang muncul<br>- Order diluar rentang terfilter | | |
| Search Order by Customer | 1. Gunakan search customer<br>2. Ketik nama pelanggan | Keyword: "Budi" | - Order dengan customer terkait muncul | | |
| Hapus Order | 1. Klik delete pada order<br>2. Konfirmasi delete | - | - Order terhapus<br>- Order items ikut terhapus (cascade) | | |
| Hitung Total Order | 1. Buat order dengan beberapa item<br>2. Cek total otomatis | Item 1: 15000 x 2<br>Item 2: 10000 x 1 | - Total = 40000<br>- Perhitungan otomatis benar | | |
| Update Item Order | 1. Edit order<br>2. Update quantity item<br>3. Save | Quantity: 2 → 5 | - Subtotal terupdate<br>- Total order terupdate | | |

---

## 4️⃣ Modul WhatsApp & Device Management

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| Tambah Device Baru | 1. Menuju Devices<br>2. Klik "Create"<br>3. Isi nama & token<br>4. Submit | Name: "Device Utama"<br>Token: "Token_fonnte" | - Device berhasil dibuat<br>- Tersimpan di database | | |
| Sinkronisasi Device dengan Fonnte | 1. Buka detail device<br>2. Klik "Sync"<br>3. Cek response | Device ID yang valid | - Data device dari Fonnte didapat<br>- Info device terupdate | | |
| Generate QR Code Device | 1. Buka detail device<br>2. Klik "Get QR"<br>3. Tampilkan QR | - | - QR code muncul<br>- Bisa dipindai untuk connect WhatsApp | | |
| Cek Status Device | 1. Buka detail device<br>2. Cek status via Fonnte API | - | - Status device terdisplay (connected/disconnected) | | |
| Deactivate Device | 1. Edit device<br>2. Set is_active = false<br>3. Save | is_active: false | - Device tidak aktif<br>- Tidak dipakai untuk order baru | | |
| Kirim Pesan WhatsApp | 1. Gunakan FonnteService<br>2. Kirim pesan test | Target: "6281234567890"<br>Message: "Test pesan" | - Pesan terkirim<br>- Response success dari Fonnte | | |
| Kirim Gambar WhatsApp | 1. Gunakan FonnteService<br>2. Kirim gambar | Target: "6281234567890"<br>Image: URL gambar | - Gambar terkirim<br>- Caption terkirim bersama gambar | | |
| Handle Webhook WhatsApp | 1. Kirim pesan ke nomor device<br>2. Webhook menerima data | Webhook payload | - Webhook diproses<br>- Response 200 OK | | |
| Deduplikasi Pesan Webhook | 1. Kirim pesan sama 2x<br>2. Cek cache dedup | Message ID sama | - Pesan kedua diabaikan<br>- Cache dedup berfungsi | | |
| Parse Pesan Menu | 1. Kirim pesan "menu"<br>2. Webhook memproses | Message: "menu" | - Balasan daftar produk<br>- Format pesan benar | | |

---

## 5️⃣ Modul Order via WhatsApp

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| Command "Menu" | 1. Pelanggan kirim "menu"<br>2. Webhook memproses | Message: "menu" | - Daftar produk terkirim<br>- Format: No. Nama - Harga | | |
| Pilih Produk - Single | 1. Balas dengan nomor produk<br>2. Webhook memproses | Message: "1" | - Order dibuat (status: DRAFT)<br>- Konfirmasi pesanan muncul | | |
| Pilih Produk dengan Quantity | 1. Balas dengan nomor & quantity<br>2. Webhook memproses | Message: "1=2" | - Order dibuat dengan qty 2<br>- Subtotal terhitung | | |
| Pilih Beberapa Produk | 1. Balas dengan multiple selection<br>2. Webhook memproses | Message: "1=2, 3=1" | - Order dengan 2 items<br>- Total terhitung | | |
| Command "Add" (Tambah Item) | 1. Ada order aktif<br>2. Kirim "add [produk]" | Message: "add 2=1" | - Item ditambah ke order<br>- Total terupdate | | |
| Command "Cancel" | 1. Ada order aktif<br>2. Kirim "cancel" | Message: "cancel" | - Order dibatalkan<br>- Status: CANCELLED | | |
| Konfirmasi Pesanan | 1. Order dalam status DRAFT<br>2. Balas "ya"/"ok" | Message: "ya" | - Status berubah ke CONFIRM<br>- Notifikasi ke penjual | | |
| Simpan Customer Info | 1. Kirim nama & alamat<br>2. Webhook simpan cache | Message: "Nama Budi, Alamat Jl. ABC" | - Info tersimpan di cache (1 jam) | | |
| Handle Message Tidak Dikenal | 1. Kirim pesan acak<br>2. Webhook memproses | Message: "xyz123" | - Pesan help/invalid command muncul | | |
| Parse Message Format Salah | 1. Kirim format salah<br>2. Webhook memproses | Message: "abc=xyz" | - Pesan error format muncul<br>- Instruksi yang benar diberikan | | |

---

## 6️⃣ Modul Laporan & Dashboard

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| Dashboard Overview | 1. Login sebagai admin<br>2. Buka dashboard | - | - Statistik order muncul<br>- Chart pendapatan tampil<br>- Recent orders list | | |
| Chart Order per Day | 1. Buka widget "Orders Per Day"<br>2. Pilih periode | Periode: 7 hari terakhir | - Chart tampil<br>- Data benar per hari | | |
| Chart Revenue | 1. Buka widget "Revenue Chart"<br>2. Filter tanggal | Range: Bulan ini | - Grafik pendapatan muncul<br>- Total revenue terdisplay | | |
| Popular Products Chart | 1. Buka widget "Popular Products"<br>2. Lihat ranking | - | - Produk terlaris tampil<br>- Urutan berdasarkan quantity | | |
| Order Status Overview | 1. Buka widget "Order Status"<br>2. Lihat distribusi | - | - Count per status muncul<br>- Visual chart/bar | | |
| Export Laporan ke PDF | 1. Buka halaman Reports<br>2. Generate laporan<br>3. Export PDF | - | - PDF terdownload<br>- Format laporan benar | | |
| Export Laporan ke Excel | 1. Buka halaman Reports<br>2. Generate laporan<br>3. Export Excel | - | - Excel terdownload<br>- Data bisa dibuka di Excel | | |
| Filter Laporan by Date | 1. Generate laporan<br>2. Set filter tanggal | Dari: 2026-01-01<br>Sampai: 2026-01-31 | - Laporan hanya dalam range tanggal | | |
| Filter Laporan by Status | 1. Generate laporan<br>2. Filter status order | Status: COMPLETED | - Laporan hanya order completed | | |
| Hitung Total Pendapatan | 1. Generate laporan penjualan<br>2. Cek total revenue | - | - Total pendapatan benar<br>- Sesuai sum(order.completed) | | |

---

## 7️⃣ Modul API Endpoints

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| GET /api/v1/products | 1. Request endpoint products<br>2. Cek response | Header: Authorization Bearer | - Response 200 OK<br>- JSON array products | | |
| GET /api/v1/products/{id} | 1. Request detail produk<br>2. Cek response | ID: 1 | - Response 200 OK<br>- JSON detail produk | | |
| GET /api/v1/products - Search | 1. Request dengan query search<br>2. Cek response | Query: ?search=geprek | - Filtered products<br>- Hanya yang cocok keyword | | |
| GET /api/v1/orders | 1. Request endpoint orders<br>2. Cek response | Header: Authorization | - Response 200 OK<br>- JSON array orders dengan items | | |
| GET /api/v1/orders/{id} | 1. Request detail order<br>2. Cek response | ID: 1 | - Response 200 OK<br>- JSON detail order lengkap | | |
| GET /api/v1/orders - Filter Status | 1. Request dengan filter status<br>2. Cek response | Query: ?status=pending | - Filtered orders by status | | |
| GET /api/v1/orders - Filter Date | 1. Request dengan filter date<br>2. Cek response | Query: ?start=2026-01-01&end=2026-01-31 | - Filtered by date range | | |
| API Pagination | 1. Request dengan page & per_page<br>2. Cek response | Query: ?page=2&per_page=10 | - Response dengan pagination meta<br>- Data halaman 2 | | |
| API - Unauthorized Access | 1. Request tanpa token<br>2. Cek response | No Authorization header | - Response 401 Unauthorized<br>- Error message | | |
| API - Invalid Token | 1. Request dengan token invalid<br>2. Cek response | Token: "invalid_token" | - Response 401<br>- Token invalid message | | |

---

## 8️⃣ Modul Pengaturan

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| View Pengaturan Toko | 1. Login admin<br>2. Buka halaman Settings | - | - Data toko terdisplay<br>- Nama, logo, alamat, phone | | |
| Update Nama Toko | 1. Edit nama toko<br>2. Simpan perubahan | Name: "Geprek Juara" | - Nama toko terupdate<br>- Tersimpan di database | | |
| Upload Logo Toko | 1. Upload file logo<br>2. Simpan | File: logo.png | - Logo terupload<br>- Path tersimpan<br>- Tampil di sistem | | |
| Update Alamat Toko | 1. Edit alamat<br>2. Simpan | Address: "Jl. Merdeka No. 1" | - Alamat terupdate | | |
| Update Telepon Toko | 1. Edit nomor telepon<br>2. Simpan | Phone: "021-12345678" | - Telepon terupdate | | |
| Reset Pengaturan Default | 1. Klik reset to default<br>2. Konfirmasi | - | - Pengaturan kembali default | | |

---

## 9️⃣ Modul User Management

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| Tambah User Baru | 1. Buka halaman Users<br>2. Klik "Create"<br>3. Isi data user | Name, Email, Password, Role | - User berhasil dibuat<br>- Role assigned | | |
| Edit User | 1. Edit user<br>2. Update data | Update name/email | - Data user terupdate | | |
| Assign Role ke User | 1. Edit user<br>2. Pilih role<br>3. Simpan | Role: "kasir" | - Role terassign ke user<br>- Permission sesuai role | | |
| Hapus User | 1. Delete user<br>2. Konfirmasi | - | - User terhapus<br>- Data terhapus dari database | | |
| Reset Password User | 1. Edit user<br>2. Set password baru<br>3. Simpan | New password | - Password terupdate<br>- User bisa login dengan password baru | | |
| Deactivate User | 1. Edit user<br>2. Set inactive | Active: false | - User tidak bisa login | | |

---

## 🔟 Modul Performance & Load Testing

| Modul yang diuji | Prosedur Pengujian | Masukan | Keluaran yang Diharapkan | Hasil yang Didapat | Kesimpulan |
|------------------|-------------------|---------|-------------------------|-------------------|------------|
| Response Time Dashboard | 1. Load dashboard<br>2. Cek load time | - | - Load time < 2 detik | | |
| Response Time API Products | 1. Request API products<br>2. Cek response time | - | - Response time < 500ms | | |
| Handle Multiple Webhook Requests | 1. Kirim 100 webhook request simultan | 100 concurrent requests | - Semua request terproses<br>- No error/crash | | |
| Cache Performance | 1. Request data produk<br>2. Cek hit cache | - | - Cache hit terjadi<br>- Response lebih cepat | | |
| Database Query Optimization | 1. Cek query log untuk load orders | Load 1000 orders | - N+1 problem tidak terjadi<br>- Query efisien | | |

---

## 📊 Summary Pengujian

### Rekapitulasi Hasil (Isi setelah pengujian selesai)

| Modul | Total Test | Pass | Fail | N/A | Blocked | % Pass |
|-------|------------|------|------|-----|---------|--------|
| Autentikasi & Authorization | 5 | | | | | |
| Manajemen Produk | 9 | | | | | |
| Manajemen Pesanan | 10 | | | | | |
| WhatsApp & Device | 10 | | | | | |
| Order via WhatsApp | 10 | | | | | |
| Laporan & Dashboard | 10 | | | | | |
| API Endpoints | 10 | | | | | |
| Pengaturan | 6 | | | | | |
| User Management | 6 | | | | | |
| Performance Testing | 5 | | | | | |
| **TOTAL** | **81** | | | | | |

---

## 📝 Keterangan Kolom

| Kolom | Deskripsi |
|-------|-----------|
| **Modul yang diuji** | Fitur atau modul sistem yang sedang diuji |
| **Prosedur Pengujian** | Langkah-langkah yang dilakukan untuk melakukan pengujian |
| **Masukan** | Data atau parameter yang dimasukkan saat pengujian |
| **Keluaran yang Diharapkan** | Hasil yang seharusnya muncul jika sistem berfungsi dengan benar |
| **Hasil yang Didapat** | Hasil aktual saat pengujian dilakukan (Isi: ✅ PASS / ❌ FAIL) |
| **Kesimpulan** | Catatan tambahan atau temuan selama pengujian |

---

## ✅ Kriteria Kelulusan

| Simbol | Keterangan |
|--------|-----------|
| ✅ **PASS** | Hasil pengujian sesuai dengan keluaran yang diharapkan |
| ❌ **FAIL** | Hasil pengujian tidak sesuai dengan keluaran yang diharapkan |
| ⏸️ **N/A** | Pengujian tidak applicable atau belum bisa dilakukan |
| 🚫 **BLOCKED** | Pengujian terblokir oleh issue lain |

---

## 📌 Informasi Tambahan

### Environment Testing
- **Local Development**: `http://localhost:8000`
- **Staging**: `[Isi URL staging]`
- **Production**: `[Isi URL production]`

### Data Testing
- **Database**: `[Isi nama database]`
- **WhatsApp Number**: `[Isi nomor test]`

### Tools yang Digunakan
- Postman/Insomnia (API testing)
- Browser Chrome/Firefox (Manual testing)
- WhatsApp Business (Webhook testing)
- Laravel Telescope (Debugging)

---

*Dokumen ini berisi template pengujian sistem yang siap digunakan. Isi kolom "Hasil yang Didapat" dan "Kesimpulan" saat melakukan pengujian aktual.*
