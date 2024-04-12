@echo off
call composer install 
echo =======================================================================
call npm install 
echo =======================================================================
call copy .env.example .env 
echo =======================================================================
call php artisan key:generate 
echo =======================================================================
call php artisan filament:upgrade 
echo =======================================================================
call php artisan storage:link 
echo =======================================================================
call php artisan migrate
echo =======================================================================
call php artisan db:seed
echo =======================================================================
call php artisan icon:cache
echo =======================================================================
call npm run build
echo =======================================================================

set /p i=Installation successful. Would you like to run the app now? (y/n) [n] 
if "%i%"=="y" (
    call cls
    call php artisan serve
)  else (
    exit
)

echo