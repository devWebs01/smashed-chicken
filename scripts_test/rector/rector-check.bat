@echo off
REM Rector Check Script for Windows
REM Script untuk mengecek perubahan yang akan dilakukan Rector

echo ==================================
echo   Rector Dry-Run Check
echo ==================================
echo.

REM Jalankan rector dalam mode dry-run
php vendor/bin/rector process --dry-run

REM Cek exit code
set EXIT_CODE=%ERRORLEVEL%

echo.
echo ==================================

if %EXIT_CODE%==0 (
    echo ✓ Tidak ada perubahan yang diperlukan
    echo   Kode Anda sudah sesuai dengan standar Rector
) else if %EXIT_CODE%==2 (
    echo ⚠ Ada perubahan yang akan dilakukan
    echo   Jalankan 'rector-fix.bat' untuk menerapkan perubahan
) else (
    echo ✗ Terjadi error saat menjalankan Rector
)

echo ==================================

exit /b %EXIT_CODE%
