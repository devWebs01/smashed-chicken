@echo off
REM Script untuk test Auto-Reply Webhook
REM Mengirim berbagai jenis pesan dan memverifikasi balasan

setlocal enabledelayedexpansion

REM Warna untuk output (Windows)
for /f "tokens=2 delims=#" %%a in ('"prompt #$H#$E# & echo on & for %%b in (1) do rem"') do set "ansi_code=%%a"
set ESC=%ansi_code%

REM Default URL
if "%1"=="" (
    set WEBHOOK_URL=https://local.systemwebsite.my.id/webhook/autoreply
) else (
    set WEBHOOK_URL=%1
)

echo %ESC%[34m========================================%ESC%[0m
echo %ESC%[34m🤖 Testing Auto-Reply Webhook%ESC%[0m
echo %ESC%[34m========================================%ESC%[0m
echo %ESC%[34m📡 URL: %WEBHOOK_URL%%ESC%[0m
echo.

REM Clear cache
echo %ESC%[33m🧹 Clearing cache...%ESC%[0m
php artisan cache:clear >nul 2>&1
timeout /t 1 /nobreak >nul
echo.

REM Test 1: Greeting Message
echo %ESC%[33m📨 Test 1: Greeting (Halo)%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"Halo\",\"timestamp\":\"2026-01-31T12:00:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 2: Menu Request
echo %ESC%[33m📨 Test 2: Menu Request%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"menu\",\"timestamp\":\"2026-01-31T12:01:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 3: Help Request
echo %ESC%[33m📨 Test 3: Help Request%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"help\",\"timestamp\":\"2026-01-31T12:02:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 4: Random Message
echo %ESC%[33m📨 Test 4: Random Message%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"Apa kabar?\",\"timestamp\":\"2026-01-31T12:03:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 5: Order Inquiry
echo %ESC%[33m📨 Test 5: Order Inquiry%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"Saya mau order\",\"timestamp\":\"2026-01-31T12:04:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 6: Empty Message
echo %ESC%[33m📨 Test 6: Empty Message%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"\",\"timestamp\":\"2026-01-31T12:05:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 7: Long Message
echo %ESC%[33m📨 Test 7: Long Message%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"Halo saya ingin pesan ayam geprek original dengan level pedas sedikit, tambah keju, dan order untuk delivery ke alamat Jl. Sudirman No. 456, Jakarta Selatan. Berapa total harganya ya?\",\"timestamp\":\"2026-01-31T12:06:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

echo %ESC%[32m========================================%ESC%[0m
echo %ESC%[32m✅ All auto-reply tests completed!%ESC%[0m
echo %ESC%[34m========================================%ESC%[0m
echo.
echo %ESC%[33m📊 Check logs untuk melihat hasil:%ESC%[0m
echo    - storage/logs/laravel.log
echo    - Cek WhatsApp untuk balasan dari bot
echo.
echo %ESC%[33m💡 Tips:%ESC%[0m
echo    - Pastikan Fonnte device aktif
echo    - Cek quota Fonnte
echo    - Verifikasi balasan diterima di WhatsApp
echo.

pause