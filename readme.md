# Symfony web app starter

Préconfiguration d'une application type Website Symfony avec les fonctionnalités courantes, propulsée en développement comme en production par [Docker](https://www.docker.com/) et [FrankenPHP](https://frankenphp.dev)/[Caddy](https://caddyserver.com/) (basé sur [dunglas/symfony-docker](https://github.com/dunglas/symfony-docker)).

## Installation (développement)

1. [Installer Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Cloner le projet
3. Lancer `bash deploy_docker.sh` (build les images, démarre la stack, migre/charge la base de tests, compile les assets)
4. Ouvrir `http://localhost:8000`

Pour déployer des mises à jour en local :
`docker compose up --build -d && bash deploy_docker.sh`

Services disponibles en développement (`compose.override.yaml`) :

- `php` : l'application (FrankenPHP), `http://localhost:8000`
- `database` : MariaDB, exposée sur `localhost:3307`
- `mailer` : Mailhog (capture des emails), UI sur `http://localhost:8025`

## Déploiement en production

1. Cloner le projet sur le serveur
2. Renseigner les secrets requis (`APP_SECRET`, `MYSQL_*`, `MAILER_DSN`, `SERVER_NAME=votredomaine.tld`, ...) via un fichier `.env.local` ou de vraies variables d'environnement. Par défaut les ports publiés sont `8000`/`8443` (comme en dev) ; définir `HTTP_PORT=80` et `HTTPS_PORT=443` pour un vrai déploiement (Caddy gère alors automatiquement le certificat TLS via `SERVER_NAME`).
3. `docker compose -f compose.yaml -f compose.prod.yaml up --build -d`
4. Les migrations Doctrine sont exécutées automatiquement au démarrage du conteneur `php` (voir `frankenphp/docker-entrypoint.sh`)

L'ancien déploiement Apache (sans Docker, via `deploy.sh`) reste disponible mais n'est plus le chemin recommandé.

## Les tests

En local, la base de données de tests est créée/maintenue par `deploy_docker.sh`. Pour rejouer uniquement les tests :

```
docker compose exec php php bin/phpunit
```

La CI (`.github/workflows/ci.yml`) build les images, démarre la stack Docker et exécute la même suite de tests dans le conteneur `php`.
