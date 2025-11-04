# Étape 1 : Builder (avec Composer)
FROM composer:2 AS build
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

# Étape 2 : Runtime (PHP + Apache)
FROM php:8.2-apache

# Installe les extensions nécessaires
RUN apt-get update && apt-get install -y git unzip libicu-dev libzip-dev zip libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql opcache zip

# Active mod_rewrite (Symfony utilise le routing par .htaccess)
RUN a2enmod rewrite

# Copie le code Symfony
COPY --from=build /app /var/www/html

WORKDIR /var/www/html

# Prépare le cache Symfony en prod
RUN php bin/console cache:clear --env=prod && php bin/console cache:warmup --env=prod

EXPOSE 80

CMD ["apache2-foreground"]
