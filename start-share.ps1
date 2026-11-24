# ============================================
# Start & Share Online - Kassarasa SmartCash
# ============================================
# Jalankan script ini dengan PowerShell:
# .\start-share.ps1

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   SMARTCASH KASSARASA - Mode Share Online" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan

# 1. Pastikan MySQL berjalan
$mysqlRunning = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if (-not $mysqlRunning) {
    Write-Host "[INFO] Memulai MySQL (XAMPP)..." -ForegroundColor Yellow
    Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini" -NoNewWindow -PassThru | Out-Null
    Start-Sleep 8
}
Write-Host "[OK] MySQL aktif." -ForegroundColor Green

# 2. Jalankan Laravel di jendela terpisah
Set-Location "c:\WebProjectKasirDigital"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd c:\WebProjectKasirDigital; php artisan serve --host=127.0.0.1 --port=8000"
Write-Host "[OK] Server Laravel berjalan di http://127.0.0.1:8000" -ForegroundColor Green
Start-Sleep 3

# 3. Jalankan Cloudflare Tunnel
$cf = "C:\Users\ACER\.local\bin\cloudflared.exe"
if (-not (Test-Path $cf)) {
    $cf = "cloudflared"
}

Write-Host "[INFO] Membuka Cloudflare Tunnel... Salin link https://....trycloudflare.com + /admin untuk tim Anda!" -ForegroundColor Yellow
Write-Host ""
& $cf tunnel --url http://127.0.0.1:8000
