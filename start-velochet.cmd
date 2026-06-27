@echo off
setlocal
cd /d "%~dp0"

powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -Command ^
  "$port = 8000; " ^
  "$listener = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue; " ^
  "if (-not $listener) { " ^
  "  Start-Process -FilePath 'php' -ArgumentList 'artisan','serve','--host=127.0.0.1','--port=8000' -WorkingDirectory '%~dp0' -WindowStyle Hidden; " ^
  "  Start-Sleep -Seconds 2 " ^
  "}; " ^
  "Start-Process 'http://127.0.0.1:8000'"

endlocal
