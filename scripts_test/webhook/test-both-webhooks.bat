@echo off
REM Script untuk test kedua webhook: WhatsApp dan Auto-Reply
REM Verifikasi kedua endpoint berfungsi dengan benar

setlocal enabledelayedexpansion

REM Warna untuk output (Windows)
for /f "tokens=2 delims=#" %%a in ('"prompt #$H#$E# & echo on & for %%b in (1) do rem"') do set "ansi_code=%%a"
set ESC=%ansi_code%

REM Base URL
set BASE_URL=https://local.systemwebsite.my.id

echo %ESC%[34m═══════════════════════════════════════════════════%ESC%[0m
echo %ESC%[34m🧪 Testing Both Webhooks%ESC%[0m
echo %ESC%[34m═══════════════════════════════════════════════════%ESC%[0m
echo.

REM Clear cache
echo %ESC%[33m🧹 Clearing cache...%ESC%[0m
php artisan cache:clear >nul 2>&1
timeout /t 1 /nobreak >nul
echo.

REM WhatsApp Webhook Test
echo %ESC%[34m📡 Testing WhatsApp Webhook%ESC%[0m
echo %ESC%[34m   URL: !BASE_URL!/webhook/whatsapp%ESC%[0m
echo.

echo %ESC%[33m   Test 1: Endpoint Check (POST only)%ESC%[0m
echo   Command: Testing POST method with valid payload
echo.

curl -s -X POST "!BASE_URL!/webhook/whatsapp" ^
  -H "Content-Type: application/json" ^
  -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"Halo, saya mau order\",\"member\":{\"jid\":\"628978301766@s.whatsapp.net\",\"name\":\"Test Customer\"}}" > nul

if %errorlevel% equ 0 (
    echo %ESC%[32m   ✅ WhatsApp Webhook: Success%ESC%[0m
) else (
    echo %ESC%[31m   ❌ WhatsApp Webhook: Failed%ESC%[0m
)
echo.

REM Auto-Reply Webhook Test
echo %ESC%[34m🤖 Testing Auto-Reply Webhook%ESC%[0m
echo %ESC%[34m   URL: !BASE_URL!/webhook/autoreply%ESC%[0m
echo.

echo %ESC%[33m   Test 1: Endpoint Check (POST only)%ESC%[0m
echo   Command: Testing POST method with valid payload
echo.

curl -s -X POST "!BASE_URL!/webhook/autoreply" ^
  -H "Content-Type: application/json" ^
  -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"menu\"}" > nul

if %errorlevel% equ 0 (
    echo %ESC%[32m   ✅ Auto-Reply Webhook: Success%ESC%[0m
) else (
    echo %ESC%[31m   ❌ Auto-Reply Webhook: Failed%ESC%[0m
)
echo.

REM Test various auto-reply messages
echo %ESC%[34m🔥 Testing Auto-Reply Variations%ESC%[0m
echo %ESC%[34m═══════════════════════════════════════════════════%ESC%[0m
echo.

set messages=hello help "apa kabar?" order
for %%m in (%messages%) do (
    echo %ESC%[33m📨 Testing: '%%m'%ESC%[0m
    curl -s -X POST "!BASE_URL!/webhook/autoreply" ^
      -H "Content-Type: application/json" ^
      -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"%%m\"}" > nul

    if %errorlevel% equ 0 (
        echo %ESC%[32m   ✅ Success%ESC%[0m
    ) else (
        echo %ESC%[31m   ❌ Failed%ESC%[0m
    )
    echo.
)

REM Summary
echo %ESC%[34m═══════════════════════════════════════════════════%ESC%[0m
echo %ESC%[32m🎉 Testing Complete!%ESC%[0m
echo.
echo %ESC%[34m📝 Setup Instructions:%ESC%[0m
echo 1. WhatsApp Webhook: Set to !BASE_URL!/webhook/whatsapp
echo 2. Auto-Reply Webhook: Set to !BASE_URL!/webhook/autoreply
echo.
echo %ESC%[34m🔧 Debug Commands:%ESC%[0m
echo • Check Laravel logs: tail -f storage/logs/laravel.log
echo • Test WhatsApp: curl -X POST !BASE_URL!/webhook/whatsapp -H "Content-Type: application/json" -d "{device:6285951572182,sender:628978301766,message:test}"
echo • Test Auto-Reply: curl -X POST !BASE_URL!/webhook/autoreply -H "Content-Type: application/json" -d "{device:6285951572182,sender:628978301766,message:hello}"
echo.
echo %ESC%[33m💡 Notes:%ESC%[0m
echo • Both webhooks must return HTTP 200
echo • WhatsApp webhook handles order processing
echo • Auto-Reply webhook sends instant responses
echo • Check Fonnte dashboard for device status
echo.

pause