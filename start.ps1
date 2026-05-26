$php = "C:\xampp\php\php.exe"

Write-Host "Starting Laravel server + queue worker..." -ForegroundColor Cyan

Start-Process powershell -ArgumentList "-NoExit", "-Command", "& '$php' artisan serve --host=127.0.0.1 --port=8000" -WindowStyle Normal
Start-Process powershell -ArgumentList "-NoExit", "-Command", "& '$php' artisan queue:work --timeout=300 --tries=3" -WindowStyle Normal

Write-Host "Done! Two windows opened:" -ForegroundColor Green
Write-Host "  - Server : http://127.0.0.1:8000" -ForegroundColor Green
Write-Host "  - Queue  : processing background jobs" -ForegroundColor Green
