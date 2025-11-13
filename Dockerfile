#syntax=docker/dockerfile:1

# Version spécifique de FrankenPHP avec PHP 8.4
FROM dunglas/frankenphp:1-php8.4 AS frankenphp_upstream

# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

# Volume pour les données persistantes
VOLUME /app/var/

# Dépendances système nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
	file \
	git \
	&& rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP nécessaires
RUN set -eux; \
	install-php-extensions \
		@composer \
		pdo_pgsql \
		gd \
		apcu \
		intl \
		opcache \
		zip \
		iconv \
	;

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

# Créer le répertoire pour les configurations PHP personnalisées
RUN mkdir -p $PHP_INI_DIR/app.conf.d

# Configuration PHP de base
RUN echo "opcache.enable=1" > $PHP_INI_DIR/app.conf.d/10-opcache.ini && \
	echo "opcache.memory_consumption=256" >> $PHP_INI_DIR/app.conf.d/10-opcache.ini && \
	echo "opcache.max_accelerated_files=20000" >> $PHP_INI_DIR/app.conf.d/10-opcache.ini && \
	echo "opcache.validate_timestamps=0" >> $PHP_INI_DIR/app.conf.d/10-opcache.ini && \
	echo "opcache.revalidate_freq=0" >> $PHP_INI_DIR/app.conf.d/10-opcache.ini

# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod

# Utiliser la configuration PHP de production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copier les fichiers de dépendances d'abord (pour le cache Docker)
COPY --link composer.* symfony.* ./

# Installer les dépendances (sans autoloader ni scripts pour l'instant)
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# Copier le reste du code source
COPY --link --exclude=frankenphp/ . ./

# Créer les répertoires nécessaires et exécuter les scripts post-installation
RUN set -eux; \
	mkdir -p var/cache var/log public/uploads/avatars; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; \
	chown -R www-data:www-data /app; \
	chmod -R 755 /app; \
	chmod -R 775 public/uploads var; \
	sync

# Configurer FrankenPHP
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV SERVER_NAME=":${PORT:-80}"

# Exposer le port 80
EXPOSE 80

# Utiliser www-data comme utilisateur
USER www-data

# Point d'entrée par défaut de FrankenPHP
CMD ["frankenphp", "run"]
