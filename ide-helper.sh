#!/usr/bin/env bash

composer dump-autoload

php artisan ide-helper:eloquent
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models -W

php artisan view:cache
php artisan route:cache
php artisan event:cache
php artisan config:cache

