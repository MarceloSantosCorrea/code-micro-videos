#!/bin/bash

## yarn create react-app frontend --template typescript
#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
### FRONTEND
#cd /var/www/frontend && yarn install && cd ..

### BACKEND
if [ -d "backend" ]; then
  cd backend

  if [ ! -f ".env" ]; then
    cp .env.example .env
  fi

  if [ ! -f ".env.testing" ]; then
    cp .env.testing.example .env.testing
  fi

  chown -R www-data:www-data .
#  composer install
#  php artisan key:generate
#  php artisan migrate
fi

php-fpm
