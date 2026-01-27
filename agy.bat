@echo off
setlocal

if "%1"=="" (
    echo Antigravity Kit CLI
    echo.
    echo Usage: agy [command]
    echo.
    echo Available commands:
    echo   checklist [args]   Run the master validation checklist
    echo   verify [args]      Run the comprehensive verification suite
    echo   help               Show this help message
    echo.
    exit /b 0
)

if "%1"=="checklist" (
    shift
    python .agent\scripts\checklist.py %*
    exit /b %ERRORLEVEL%
)

if "%1"=="verify" (
    shift
    python .agent\scripts\verify_all.py %*
    exit /b %ERRORLEVEL%
)

if "%1"=="help" (
    echo Antigravity Kit CLI
    echo.
    echo Commands:
    echo   checklist
    echo     Runs: python .agent\scripts\checklist.py
    echo     Use: agy checklist .
    echo.
    echo   verify
    echo     Runs: python .agent\scripts\verify_all.py
    echo     Use: agy verify . --url http://localhost:8000
    exit /b 0
)

echo Unknown command: %1
echo Try 'agy help'
exit /b 1
