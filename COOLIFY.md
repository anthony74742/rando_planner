# Configuration Coolify

Ce document explique comment déployer l'application sur Coolify.

## Prérequis

- Une instance Coolify configurée
- Une base de données PostgreSQL déjà créée sur Coolify avec son URL de connexion

## Configuration dans Coolify

### 1. Créer une nouvelle application

1. Dans Coolify, créez une nouvelle application
2. Sélectionnez "Dockerfile" comme méthode de build
3. Pointez vers votre repository Git

### 2. Variables d'environnement

Configurez les variables d'environnement suivantes dans Coolify :

#### Obligatoires

- `APP_ENV=prod`
- `APP_DEBUG=0`
- `APP_SECRET` : Générez une clé secrète (ex: `php -r "echo bin2hex(random_bytes(32));"`)
- `DATABASE_URL` : L'URL de connexion PostgreSQL fournie par Coolify
  - Format: `postgresql://user:password@host:port/database?serverVersion=16&charset=utf8`

#### Optionnelles

- `MAILER_DSN` : Configuration du service d'email (si nécessaire)
  - Exemple pour SMTP: `smtp://user:password@smtp.example.com:587`
  - Exemple pour Mailgun: `mailgun+api://key@default?domain=example.com`

### 3. Port

Coolify détectera automatiquement le port 80 exposé par le Dockerfile.

### 4. Volumes persistants (optionnel)

Si vous souhaitez persister les uploads de fichiers, vous pouvez ajouter un volume :

- Chemin dans le conteneur: `/app/public/uploads`
- Montage: Volume persistant Coolify

### 5. Commandes de build (optionnel)

Coolify utilisera automatiquement le Dockerfile. Aucune commande de build supplémentaire n'est nécessaire.

### 6. Commandes de démarrage

Aucune commande de démarrage supplémentaire n'est nécessaire. FrankenPHP démarre automatiquement.

### 7. Migrations de base de données

Pour exécuter les migrations après le déploiement, vous pouvez ajouter une commande post-deploy dans Coolify :

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

## Notes importantes

- La base de données doit être accessible depuis le conteneur de l'application
- Assurez-vous que les permissions des volumes persistants sont correctes (www-data:www-data)
- En production, `APP_DEBUG` doit être à `0` pour des raisons de sécurité
- Les logs sont disponibles dans `/app/var/log` dans le conteneur

## Vérification du déploiement

Après le déploiement, vérifiez que :

1. L'application répond correctement
2. Les migrations ont été exécutées
3. Les uploads de fichiers fonctionnent (si configuré)
4. Les emails sont envoyés correctement (si configuré)

