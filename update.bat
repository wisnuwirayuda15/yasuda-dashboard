@echo off
call composer install 
echo =======================================================================
call npm install 
echo =======================================================================
call migrate_fresh_seed.bat
echo =======================================================================
call npm run build
echo =======================================================================

set /p i=Update successful. Would you like to run the app now? (y/n) [n] 
if "%i%"=="y" (
    call cls
    call php artisan serve
)  else (
    exit
)

echo
