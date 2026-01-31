# Rector Scripts

Script untuk mengecek dan menerapkan perubahan Rector secara otomatis.

## Files

| Script | Deskripsi |
|--------|-----------|
| `rector-check.sh` | Cek perubahan (dry-run) - Linux/Mac |
| `rector-check.bat` | Cek perubahan (dry-run) - Windows |
| `rector-fix.sh` | Terapkan perubahan - Linux/Mac |
| `rector-fix.bat` | Terapkan perubahan - Windows |

## Penggunaan

### Windows

```cmd
# Cek perubahan (dry-run)
cd scripts_test\rector
rector-check.bat

# Terapkan perubahan
rector-fix.bat
```

### Linux/Mac

```bash
# Cek perubahan (dry-run)
cd scripts_test/rector
./rector-check.sh

# Terapkan perubahan
./rector-fix.sh
```

## Alur Kerja

1. **Cek Perubahan** - Jalankan `rector-check` untuk melihat perubahan yang akan dilakukan
2. **Review** - Jika ada perubahan, review output untuk memastikan sesuai
3. **Terapkan** - Jalankan `rector-fix` untuk menerapkan perubahan
4. **Commit** - Review hasil dengan `git diff` lalu commit

## Output

### ✓ Tidak Ada Perubahan

```
==================================
  Rector Dry-Run Check
==================================

[OK] Rector is done!

==================================
✓ Tidak ada perubahan yang diperlukan
  Kode Anda sudah sesuai dengan standar Rector
==================================
```

### ⚠ Ada Perubahan

```
==================================
  Rector Dry-Run Check
==================================

24 files with changes
=====================

1) app/Services/FonnteService.php:10
   ---------- begin diff ----------
   - public function getAllDevices()
   + public function getAllDevices(): array
   ----------- end diff -----------

==================================
⚠ Ada perubahan yang akan dilakukan
  Jalankan 'rector-fix.bat' untuk menerapkan perubahan
==================================
```

## Tips

- Selalu jalankan `rector-check` terlebih dahulu sebelum `rector-fix`
- Commit kode sebelum menjalankan `rector-fix` untuk memudahkan rollback
- Gunakan `git diff` untuk review perubahan setelah `rector-fix`

## Composer Scripts

Anda juga dapat menggunakan composer scripts:

```bash
# Cek perubahan
composer rector

# Terapkan perubahan
composer rector-fix
```
