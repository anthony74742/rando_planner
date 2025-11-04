# Utiliser PHP avec Apache
FROM php:8.2-apache

# Installer les dépendances système et extensions PHP nécessaires à Symfony
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql opcache zip

# Activer le module rewrite d'Apache
RUN a2enmod rewrite

# Copier le projet dans le conteneur
WORKDIR /var/www/html
COPY . .

# Installer les dépendances PHP (sans les dev, optimisé pour la prod)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Nettoyer le cache et le préparer pour la prod
RUN php bin/console cache:clear --env=prod && php bin/console cache:warmup --env=prod

# Donner les droits à Apache
RUN chown -R www-data:www-data /var/www/html

# Exposer le port HTTP
EXPOSE 80

# Lancer Apache
CMD ["apache2-foreground"]
