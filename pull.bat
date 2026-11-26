@echo off
set GIT="C:\Program Files\Git\cmd\git.exe"
echo ======================================================
echo Pulling latest updates from GitHub (branch: main)...
echo ======================================================
rem Secret guard: refuses commits/pushes containing passwords, API keys, .env files or .sql dumps.
%GIT% config core.hooksPath .githooks
%GIT% pull origin main
echo.
pause
