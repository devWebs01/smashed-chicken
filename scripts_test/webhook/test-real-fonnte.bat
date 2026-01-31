@echo off
REM Script untuk test dengan REAL Fonnte Payload
REM Update 12 Januari 2026
REM Compatible Windows dan Linux

setlocal enabledelayedexpansion

REM Warna untuk output (Windows)
for /f "tokens=2 delims=#" %%a in ('"prompt #$H#$E# & echo on & for %%b in (1) do rem"') do set "ansi_code=%%a"
set ESC=%ansi_code%

REM Default URL
if "%1"=="" (
    set WEBHOOK_URL=https://local.systemwebsite.my.id/webhook/whatsapp
) else (
    set WEBHOOK_URL=%1
)

echo %ESC%[34m========================================%ESC%[0m
echo %ESC%[34m^🔍 Testing dengan REAL Fonnte Payload%ESC%[0m
echo %ESC%[34m   ^(Update 12 Januari 2026^)%ESC%[0m
echo %ESC%[34m========================================%ESC%[0m
echo %ESC%[34m📡 URL: %WEBHOOK_URL%%ESC%[0m
echo.

REM Clear cache
echo %ESC%[33m🧹 Clearing cache...%ESC%[0m
php artisan cache:clear >nul 2>&1
timeout /t 1 /nobreak >nul
echo.

echo %ESC%[34m────────────────────────────────────────%ESC%[0m

REM Test 1: Basic Text Message
echo %ESC%[33m📨 Test 1: Basic Text Message%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"Halo, saya ingin order geprek\",\"timestamp\":\"2026-01-31T12:00:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 2: Message with Name Field
echo %ESC%[33m📨 Test 2: Message with Name Field%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"name\":\"Budi Santoso\",\"message\":\"Nama saya Budi\",\"timestamp\":\"2026-01-31T12:01:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 3: Order Message (1=2, 2=1)
echo %ESC%[33m📨 Test 3: Order Message (1=2, 2=1)%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"1=2, 2=1\",\"timestamp\":\"2026-01-31T12:02:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 4: Reply Test (selesai)
echo %ESC%[33m📨 Test 4: Reply Test (selesai)%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"selesai\",\"timestamp\":\"2026-01-31T12:03:00Z\",\"inboxId\":\"true\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 5: Reset Command
echo %ESC%[33m📨 Test 5: Reset Command%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"reset\",\"timestamp\":\"2026-01-31T12:04:00Z\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

REM Test 6: Error Handling (Missing sender)
echo %ESC%[33m📨 Test 6: Error Handling (Missing sender)%ESC%[0m
echo %ESC%[33m────────────────────────────────────────%ESC%[0m
curl -s -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"device\":\"6285951572182\",\"message\":\"No sender field\"}" >nul 2>&1
echo %ESC%[32m✓ Sent%ESC%[0m
timeout /t 2 /nobreak >nul
echo.

echo %ESC%[32m========================================%ESC%[0m
echo %ESC%[32m✅ All tests completed!%ESC%[0m
echo %ESC%[34m========================================%ESC%[0m
echo.
echo %ESC%[33m📊 Check logs untuk melihat hasil:%ESC%[0m
echo    - storage/logs/laravel.log
echo    - Slack channel
echo.
echo %ESC%[33m💡 Tips:%ESC%[0m
echo    - Cek database untuk order baru
echo    - Verify Fonnte API mengirim pesan ke customer
echo.

pause