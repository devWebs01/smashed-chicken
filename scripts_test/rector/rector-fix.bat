@echo off
REM Rector Fix Script for Windows
REM Script untuk menerapkan perubahan Rector

echo ==================================
echo   Rector Fix - Apply Changes
echo ==================================
echo.

REM Konfirmasi sebelum menerapkan
set /p CONFIRM="Apakah Anda yakin ingin menerapkan perubahan Rector? (y/N): "

if /i not "%CONFIRM%"=="y" (
    echo.
    echo ✓ Dibatalkan
    exit /b 0
)

echo.
echo 🔧 Menerapkan perubahan Rector...
echo.

REM Jalankan rector
php vendor/bin/rector process

REM Cek exit code
set EXIT_CODE=%ERRORLEVEL%

echo.
echo ==================================

if %EXIT_CODE%==0 (
    echo ✓ Perubahan berhasil diterapkan
    echo.
    echo 💾 Tips: Review perubahan dengan 'git diff' lalu commit
) else (
    echo ✗ Terjadi error saat menerapkan perubahan
)

echo ==================================

exit /b %EXIT_CODE%
