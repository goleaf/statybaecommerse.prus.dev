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
set "CURL_STDERR=%OUT_DIR%\order-17-curl-stderr.txt"
set "CURL_META=%OUT_DIR%\order-17-curl-meta.txt"

if not exist "%OUT_DIR%" mkdir "%OUT_DIR%"

> "%PAYLOAD%" (
   echo {
   echo   "api_token": "%API_TOKEN%",
   echo     "invoice_type": "sf",
   echo     "notes": "Please handle the package with care. Fragile items inside.",
   echo     "total_chipping": 15.50,
   echo     "total_discount": 5.00,
   echo     "total_amount": 110.50,
   echo     "products": [{
   echo             "description": "Wireless Mechanical Keyboard",
   echo             "quantity": 1,
   echo             "price": 85.00
   echo         },
   echo         {
   echo             "description": "USB-C Braided Cable",
   echo             "quantity": 2,
   echo             "price": 15.00
   echo         }
   echo     ],
   echo     "billing": {
   echo         "name": "Tech Solutions UAB",
   echo         "isJuridical": true,
   echo         "company_code": "300123456",
   echo         "vat_code": "LT100001234512",
   echo         "address": "Gedimino pr. 1",
   echo         "city": "Vilnius",
   echo         "post": "01103",
   echo         "isVatPayer": true
   echo     },
   echo     "delivery": {
   echo         "name": "Mantas T",
   echo         "address": "Ozo g. 25",
   echo         "city": "Vilnius",
   echo         "post": "07150"
   echo     },
   echo     "payer": {
   echo         "name": "Mantas T",
   echo         "email": "info@egisstatyba.lt",
   echo         "phone": "+37060000000"
   echo     }
   echo }

)

echo [INFO] Sending request to %API_URL%
curl.exe -sS -D "%HEADERS_OUT%" -o "%PDF_OUT%" -X POST "%API_URL%" ^
  -H "Content-Type: application/json" ^
  --data-binary "@%PAYLOAD%" ^
  -w "HTTP_STATUS=%%{http_code}\nCONTENT_TYPE=%%{content_type}\n" ^
  > "%CURL_META%" 2> "%CURL_STDERR%"

if errorlevel 1 (
    echo [ERROR] curl request failed.
    if exist "%CURL_STDERR%" (
        echo [INFO] curl stderr:
        type "%CURL_STDERR%"
    )
    exit /b 1
)

if exist "%CURL_STDERR%" (
    echo [INFO] curl stderr:
    type "%CURL_STDERR%"
)
if exist "%CURL_META%" (
    echo [INFO] curl summary:
    type "%CURL_META%"
)
if exist "%HEADERS_OUT%" (
    echo [INFO] Response headers:
    type "%HEADERS_OUT%"
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
