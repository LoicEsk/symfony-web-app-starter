#!/bin/bash


#  Install des packet php
docker compose run --rm sf composer Install


# Install des packet JS
docker compose run --rm encore npm ci
docker compose run --rm encore npm run dev

# Liste des migrations Doctrine
docker compose run --rm sf php bin/console doctrine:migrations:list