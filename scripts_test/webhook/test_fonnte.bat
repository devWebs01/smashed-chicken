@echo off
echo Testing Fonnte Direct API Call...
echo.

curl -s -X POST "https://api.fonnte.com/send" ^
  -H "Authorization: 8uk2mrFcmnCd3doPn7DkUgbhAfjqT" ^
  -H "Content-Type: application/json" ^
  -d "{\"target\":\"628978301766\",\"message\":\"Test direct dari curl - %date% %time%\"}"

echo.
echo Test completed. Check WhatsApp for message.
pause
