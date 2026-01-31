# 🚀 Cara Menjalankan Aplikasi

Panduan menjalankan aplikasi Laravel dengan atau tanpa Docker Sail.

## 📋 Daftar Isi

1. [Development Normal (Tanpa Docker)](#development-normal-tanpa-docker)
2. [Development dengan Docker Sail](#development-dengan-docker-sail)
3. [Troubleshooting](#troubleshooting)

---

## 💻 Development Normal (Tanpa Docker)

### Prerequisites:
- PHP 8.2+
- Composer
- SQLite extension (`php-sqlite3`)

### Langkah-langkah:

#### 1. Pastikan Menggunakan SQLite

File `.env` harus seperti ini:
```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

#### 2. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

#### 3. Jalankan Migrasi (Jika Perlu)

```bash
php artisan migrate
```

#### 4. Jalankan Laravel Server

```bash
php artisan serve --port=8000
```

#### 5. Jalankan Cloudflare Tunnel (Terminal Baru)

```bash
./cloudflare-tunnel.sh 8000
```

### ✅ Aplikasi Ready!

- **Web:** http://localhost:8000
- **Admin:** http://localhost:8000/admin
- **Webhook:** https://local.testingbae0000.my.id/webhook/whatsapp

---

## 🐳 Development dengan Docker Sail

### Prerequisites:
- Docker Desktop
- Docker Compose

### Langkah-langkah:

#### 1. Pastikan Menggunakan MySQL (untuk Sail)

File `.env` harus seperti ini:
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

#### 2. Start Sail

```bash
./vendor/bin/sail up -d
```

#### 3. Jalankan Migrasi (Jika Perlu)

```bash
./vendor/bin/sail artisan migrate
```

#### 4. Aplikasi Berjalan di Docker

Sail akan menjalankan:
- Laravel app di port 80
- MySQL di port 3306
- Redis di port 6379

### ✅ Aplikasi Ready!

- **Web:** http://localhost
- **Admin:** http://localhost/admin

---

## 🔄 Switching Antara Normal dan Sail

### Dari Normal ke Sail:

1. **Update `.env` ke MySQL:**
```bash
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=mysql/' .env
sed -i 's/# DB_PORT=3306/DB_PORT=3306/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=laravel/' .env
sed -i 's/# DB_USERNAME=root/DB_USERNAME=sail/' .env
sed -i 's/# DB_PASSWORD=/DB_PASSWORD=password/' .env
```

2. **Start Sail:**
```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

### Dari Sail ke Normal:

1. **Stop Sail:**
```bash
./vendor/bin/sail down
```

2. **Update `.env` ke SQLite:**
```bash
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_HOST=mysql/# DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=3306/# DB_PORT=3306/' .env
sed -i 's/DB_DATABASE=laravel/# DB_DATABASE=laravel/' .env
sed -i 's/DB_USERNAME=sail/# DB_USERNAME=root/' .env
sed -i 's/DB_PASSWORD=password/# DB_PASSWORD=/' .env
```

3. **Clear Cache:**
```bash
php artisan config:clear
php artisan cache:clear
```

4. **Run Normal:**
```bash
php artisan serve --port=8000
```

---

## 🆘 Troubleshooting

### Error: "could not find driver (Connection: mysql)"

**Penyebab:**
File `.env` menggunakan MySQL tapi PHP lokal tidak punya MySQL driver atau Sail tidak running.

**Solusi:**

**Option 1: Kembali ke SQLite (Recommended untuk development normal)**
```bash
# Update .env
DB_CONNECTION=sqlite
# Kemudian comment semua DB_* lainnya

# Clear cache
php artisan config:clear
php artisan cache:clear

# Test
php artisan migrate:status
```

**Option 2: Install MySQL Driver**
```bash
# Ubuntu/Debian
sudo apt-get install php8.2-mysql

# Arch Linux
sudo pacman -S php-mysql

# Restart PHP
sudo systemctl restart php-fpm
```

**Option 3: Gunakan Sail**
```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

---

### Error: "SQLSTATE[HY000]: General error: 1 no such table"

**Penyebab:**
Database SQLite kosong atau migrasi belum dijalankan.

**Solusi:**
```bash
# Jalankan migrasi
php artisan migrate

# Atau fresh migrate dengan seeding
php artisan migrate:fresh --seed
```

---

### Error: "Database file not found"

**Penyebab:**
File `database/database.sqlite` tidak ada.

**Solusi:**
```bash
# Buat file database
touch database/database.sqlite

# Set permission
chmod 664 database/database.sqlite

# Jalankan migrasi
php artisan migrate
```

---

### Sail Container Tidak Start

**Penyebab:**
Port sudah digunakan atau Docker tidak running.

**Solusi:**
```bash
# Cek port yang digunakan
sudo lsof -i :80
sudo lsof -i :3306

# Stop container lain
docker ps
docker stop <container_id>

# Atau ubah port Sail di docker-compose.yml
```

---

## 📝 Quick Commands

### Development Normal:
```bash
# Start
php artisan serve --port=8000
./cloudflare-tunnel.sh 8000

# Stop
Ctrl+C di kedua terminal
```

### Development dengan Sail:
```bash
# Start
./vendor/bin/sail up -d

# Stop
./vendor/bin/sail down

# Artisan commands
./vendor/bin/sail artisan <command>

# Composer
./vendor/bin/sail composer <command>

# NPM
./vendor/bin/sail npm <command>
```

---

## 💡 Best Practices

### 1. Gunakan SQLite untuk Development Normal
- Lebih simple, tidak perlu MySQL server
- Portable, bisa langsung clone dan run
- Cukup untuk testing dan development

### 2. Gunakan Sail untuk:
- Development tim yang kompleks
- Butuh konsistensi environment
- Testing dengan MySQL production-like
- Development dengan Redis, Meilisearch, dll

### 3. Backup Database Sebelum Switch
```bash
# Backup SQLite
cp database/database.sqlite database/database.sqlite.backup

# Backup MySQL (dari Sail)
./vendor/bin/sail exec mysql mysqldump -u sail -ppassword laravel > backup.sql
```

### 4. Git Ignore Database Files
File `.gitignore` sudah include:
```
database/database.sqlite
database/*.sqlite
```

Jadi database lokal tidak ter-commit ke git.

---

## 🔗 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sail Documentation](https://laravel.com/docs/sail)
- [Docker Documentation](https://docs.docker.com/)
- [SQLite Documentation](https://www.sqlite.org/docs.html)

---

**Happy Coding! 🚀**
