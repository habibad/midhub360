@echo off
set GIT="C:\Program Files\Git\cmd\git.exe"
echo ======================================================
echo Pushing Midhub360 to GitHub repository (branch: main)...
echo ======================================================
rem Secret guard: refuses commits/pushes containing passwords, API keys, .env files or .sql dumps.
%GIT% config core.hooksPath .githooks
%GIT% push -u origin main
if %errorlevel% neq 0 (
    echo.
    echo PUSH FAILED - nothing was forced.
    echo  - "secret-looking content found": remove that secret from the code, commit again, push again.
    echo  - GitHub has newer commits: run pull.bat first, then push.bat again.
    echo  - Never force-push main: it can delete work and re-upload removed secrets.
)
echo.
pause
