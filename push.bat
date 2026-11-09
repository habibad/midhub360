@echo off
echo ======================================================
echo Pushing Midhub360 to GitHub repository (branch: main)...
echo ======================================================
"C:\Program Files\Git\cmd\git.exe" push -u origin main
if %errorlevel% neq 0 (
    echo.
    echo If GitHub rejected due to remote conflicts, trying force push...
    echo.
    "C:\Program Files\Git\cmd\git.exe" push -u origin main --force
)
echo.
pause
