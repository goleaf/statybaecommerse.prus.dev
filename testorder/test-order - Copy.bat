@echo off
setlocal EnableExtensions

REM Usage:
REM   test-order.bat [api_token]
REM If token is not passed as argument, the default below is used.
set "API_TOKEN=2399cd99-175e-4ec5-8800-75ce7e3651bb"
if not "%~1"=="" set "API_TOKEN=%~1"

if "%API_TOKEN%"=="" (
    echo [ERROR] Missing API token.
    echo Provide it as first argument: test-order.bat your-api-token
    exit /b 1
)

set "API_URL=https://saskaita.vercel.app/api/initiate"
set "OUT_DIR=%~dp0tmp"
set "PAYLOAD=%OUT_DIR%\order-17-initiate.json"
set "HEADERS_OUT=%OUT_DIR%\order-17-headers.txt"
set "PDF_OUT=%OUT_DIR%\order-17-invoice.pdf"

if not exist "%OUT_DIR%" mkdir "%OUT_DIR%"

> "%PAYLOAD%" (
    echo {
    echo   "api_token": "%API_TOKEN%",
    echo   "invoice_type": "sf",
    echo   "notes": "order_number:LT-69A060EB0A489;order_id:17",
    echo   "total_chipping": 0,
    echo   "total_discount": 0,
    echo   "total_amount": 66.42,
    echo   "products": [
    echo     {
    echo       "description": "4PRO - Cinkuoti sraigtai, gipsas/medis, PH (3.9 x 32-42 mm)",
    echo       "quantity": 11,
    echo       "price": 4.99
    echo     }
    echo   ],
    echo   "billing": {
    echo     "name": "Adena Blair",
    echo     "isJuridical": false,
    echo     "address": "V.Pietario 3-11",
    echo     "city": "Kaunas",
    echo     "post": "03122"
    echo   },
    echo   "delivery": {
    echo     "name": "Adena Blair",
    echo     "address": "V.Pietario 3-11",
    echo     "city": "Kaunas",
    echo     "post": "03122"
    echo   },
    echo   "payer": {
    echo     "name": "Adena Blair",
    echo     "email": "noqoqodoqy@mailinator.com"
    echo   }
    echo }
)

echo [INFO] Sending request to %API_URL%
curl.exe -sS -D "%HEADERS_OUT%" -o "%PDF_OUT%" -X POST "%API_URL%" ^
  -H "Content-Type: application/json" ^
  --data-binary "@%PAYLOAD%"

if errorlevel 1 (
    echo [ERROR] curl request failed.
    exit /b 1
)

findstr /I /C:"Content-Type: application/pdf" "%HEADERS_OUT%" >nul
if errorlevel 1 (
    echo [ERROR] API did not return PDF. Response body:
    type "%PDF_OUT%"
    exit /b 1
)

echo [OK] PDF saved to:
echo   %PDF_OUT%
echo [OK] Response headers saved to:
echo   %HEADERS_OUT%

exit /b 0
