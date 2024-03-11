@echo off
call php artisan blueprint:erase
call php artisan blueprint:build
echo Rebuild complete!
pause