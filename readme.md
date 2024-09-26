# Symfony web app starter

Préconfigration d'une application type Website Symfony avec les fonctionnalités courantes et un environnement de développement Docker

## Installation

### Avec Apache (production)

1. Cloner le projet sur le serveur
2. Créer les vhosts vers ./public
3. Installer un Process MAnager pour les workers (le messager)
    - Documentation Symfony : https://symfony.com/doc/current/messenger.html#deploying-to-production
4. Lancer le bash de deployement avec `bash deploy.sh`

Pour le deployement de mises à jour, il n'y aura plus que cette dernière ligne à lancer.

### Avec Docker (dev)

1. Cloner le projet
2. Lancer le bash de deployement avec `bash deploy_docker.sh`

Pour deployer les mises à jour :
`docker compose up --build -d && bash deploy_docker.sh`

## Utilisation du serveur Docker

Installation et déployement pour developement en local

1. `bash deploy_docker.sh

## Les tests

La base de données de tests crée et maintenue à jour par le script *docker_deploy.sh*