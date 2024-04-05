@echo off
call php artisan migrate:fresh --seed
pause