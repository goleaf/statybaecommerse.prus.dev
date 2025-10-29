@echo off
setlocal enabledelayedexpansion

:: Colors for Windows (using ANSI escape codes)
set "BLUE=[94m"
set "GREEN=[92m"
set "RED=[91m"
set "YELLOW=[93m"
set "NC=[0m"

:: Default sort order
set "SORT_ORDER=asc"

:: Parse command line arguments
:parse_args
if "%~1"=="" goto end_parse
if /i "%~1"=="--sort=asc" (
    set "SORT_ORDER=asc"
    shift
    goto parse_args
)
if /i "%~1"=="--sort=desc" (
    set "SORT_ORDER=desc"
    shift
    goto parse_args
)
echo %RED%Unknown argument: %~1%NC%
echo %YELLOW%Usage: %~nx0 [--sort=asc^|--sort=desc]%NC%
exit /b 1

:end_parse

echo %BLUE%========================================%NC%
echo %BLUE%  Collecting and Running Tests%NC%
echo %BLUE%========================================%NC%
echo.

:: Initialize counters
set /a total_tests=0
set /a current_test=0
set /a passed_tests=0
set /a failed_tests=0

:: Temporary file to store test list
set "temp_test_list=%TEMP%\test_list_%RANDOM%.txt"
set "temp_failed_list=%TEMP%\failed_list_%RANDOM%.txt"

:: Collect all test files
echo %YELLOW%Collecting test files...%NC%
if /i "%SORT_ORDER%"=="desc" (
    echo %YELLOW%Sort order: Descending%NC%
    dir /b /s /o-n tests\*Test.php > "%temp_test_list%"
) else (
    echo %YELLOW%Sort order: Ascending%NC%
    dir /b /s /on tests\*Test.php > "%temp_test_list%"
)

:: Count total tests
for /f %%a in ('type "%temp_test_list%" ^| find /c /v ""') do set total_tests=%%a

echo %GREEN%Found %total_tests% test files%NC%
echo.

:: Clear failed test list
if exist "%temp_failed_list%" del "%temp_failed_list%"

:: Run each test individually
for /f "delims=" %%f in (%temp_test_list%) do (
    set /a current_test+=1
    
    :: Extract test name without .php extension
    set "test_file=%%f"
    set "test_name=%%~nf"
    
    :: Extract relative path for display
    set "relative_path=!test_file:%CD%\=!"
    set "relative_path=!relative_path:tests\=!"
    
    echo %BLUE%========================================%NC%
    echo %BLUE%[!current_test!/%total_tests%] Running: !relative_path!%NC%
    echo %BLUE%========================================%NC%
    
    :: Run the test
    php artisan test --filter="!test_name!" >nul 2>&1
    
    if !errorlevel! equ 0 (
        set /a passed_tests+=1
        echo %GREEN%[32m✓ PASSED: !test_name!%NC%
    ) else (
        set /a failed_tests+=1
        echo !test_name! ^(!relative_path!^) >> "%temp_failed_list%"
        echo %RED%✗ FAILED: !test_name!%NC%
    )
    
    echo.
)

:: Summary
echo %BLUE%========================================%NC%
echo %BLUE%  Test Summary%NC%
echo %BLUE%========================================%NC%
echo Total Tests:  %total_tests%
echo %GREEN%Passed:       %passed_tests%%NC%
echo %RED%Failed:       %failed_tests%%NC%
echo.

:: List failed tests if any
if %failed_tests% gtr 0 (
    echo %RED%Failed Tests:%NC%
    for /f "delims=" %%l in (%temp_failed_list%) do (
        echo %RED%  - %%l%NC%
    )
    echo.
    
    :: Cleanup
    if exist "%temp_test_list%" del "%temp_test_list%"
    if exist "%temp_failed_list%" del "%temp_failed_list%"
    
    exit /b 1
) else (
    echo %GREEN%All tests passed! 🎉%NC%
    
    :: Cleanup
    if exist "%temp_test_list%" del "%temp_test_list%"
    if exist "%temp_failed_list%" del "%temp_failed_list%"
    
    exit /b 0
)

