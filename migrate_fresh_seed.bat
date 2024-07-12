@echo off
call php artisan migrate:fresh --seed
call php artisan shield:install --fresh