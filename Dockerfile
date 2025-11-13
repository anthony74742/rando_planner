FROM dunglas/frankenphp:latest

# Installer les extensions PHP nécessaires
# Note: pdo_pgsql pour PostgreSQL (pas pdo_mysql)
RUN install-php-extensions \
    pdo_pgsql \
    gd \
    intl \
    zip \
    opcache \
    iconv

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de dépendances d'abord (pour le cache Docker)
COPY composer.json composer.lock ./
COPY symfony.lock* ./

# Installer Composer si nécessaire
RUN set -eux; \
    if [ ! -f /usr/local/bin/composer ]; then \
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    fi

# Installer les dépendances (sans scripts pour l'instant)
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# Copier le reste du code source
COPY . .

# Exécuter les scripts post-installation de Composer et optimiser l'autoloader
# Note: Les scripts peuvent échouer sans variables d'env, donc on utilise || true
RUN APP_ENV=prod composer dump-autoload --optimize --classmap-authoritative --no-dev && \
    APP_ENV=prod php bin/console cache:clear --no-debug || true && \
    APP_ENV=prod php bin/console assets:install public || true && \
    APP_ENV=prod php bin/console importmap:install || true

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p public/uploads/avatars var/cache var/log && \
    chown -R www-data:www-data /app && \
    chmod -R 755 /app && \
    chmod -R 775 public/uploads var

# Configurer PHP pour la production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Configurer FrankenPHP
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV SERVER_NAME=":80"

# Variables d'environnement par défaut (seront surchargées par Coolify)
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Exposer le port 80
EXPOSE 80

# Utiliser www-data comme utilisateur
USER www-data

# Point d'entrée par défaut de FrankenPHP
CMD ["frankenphp"]
