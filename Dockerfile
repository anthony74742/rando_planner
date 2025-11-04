# Étape 1 : Builder
FROM composer:2 AS build
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

# Étape 2 : Runtime
FROM php:8.2-apache

# Installe les extensions nécessaires pour Symfony
RUN apt-get update && apt-get install -y git unzip libicu-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_mysql opcache zip

# Active mod_rewrite pour Symfony
RUN a2enmod rewrite

# Copie le code depuis le builder
COPY --from=build /app /var/www/html

# Définit le répertoire de travail
WORKDIR /var/www/html

# Supprime les caches dev
RUN php bin/console cache:clear --env=prod && php bin/console cache:warmup --env=prod

# Expose le port Apache
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"]
