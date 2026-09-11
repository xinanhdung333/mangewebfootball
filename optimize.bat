@echo off
echo ================================
echo   Football Hub - Optimize
echo ================================
echo.

echo [1/5] Caching configuration...
php artisan config:cache

echo [2/5] Caching routes...
php artisan route:cache

echo [3/5] Caching views...
php artisan view:cache

echo [4/5] Caching events...
php artisan event:cache

echo [5/5] Optimizing autoloader...
composer dump-autoload --optimize

echo.
echo ================================
echo   Optimization complete!
echo ================================
pause
