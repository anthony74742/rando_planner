FROM dunglas/frankenphp

# Copier le code source de l'application dans le dossier public
COPY . /app/public

# (Optionnel) Installer des extensions PHP nécessaires
RUN install-php-extensions pdo_mysql gd intl zip opcache

# Définir la configuration du serveur FrankenPHP : point d'entrée PHP
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# Exposer le port 80 (HTTP)
EXPOSE 80
