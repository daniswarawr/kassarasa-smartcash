# ============================================
# Start Script - Kasir Digital UMKM
# ============================================
# Jalankan script ini dengan PowerShell:
# .\start-app.ps1

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   KASIR DIGITAL UMKM - Laravel App" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Cek apakah mysqld sudah berjalan
$mysqlRunning = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if ($mysqlRunning) {
    Write-Host "[OK] MySQL sudah berjalan." -ForegroundColor Green
} else {
    Write-Host "[INFO] Memulai MySQL (XAMPP)..." -ForegroundColor Yellow
    Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini" -NoNewWindow -PassThru | Out-Null
    Write-Host "[INFO] Menunggu MySQL siap (8 detik)..." -ForegroundColor Yellow
    Start-Sleep 8
    Write-Host "[OK] MySQL berhasil dijalankan." -ForegroundColor Green
}

Write-Host ""
Write-Host "[INFO] Pindah ke direktori project..." -ForegroundColor Yellow
Set-Location "c:\WebProjectKasirDigital"

Write-Host "[INFO] Membersihkan cache Laravel..." -ForegroundColor Yellow
php artisan config:clear 2>$null
php artisan view:clear 2>$null
php artisan route:clear 2>$null
Write-Host "[OK] Cache dibersihkan." -ForegroundColor Green

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   Aplikasi siap! Buka browser:" -ForegroundColor Cyan
Write-Host "   http://localhost:8000/admin" -ForegroundColor White
Write-Host ""
Write-Host "   Email : admin@kasirdigital.com" -ForegroundColor Yellow
Write-Host "   Password: admin123" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "[INFO] Memulai Laravel development server..." -ForegroundColor Yellow
Write-Host "[INFO] Tekan Ctrl+C untuk menghentikan server." -ForegroundColor Gray
Write-Host ""

php artisan serve
