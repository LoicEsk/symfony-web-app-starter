#!/bin/bash


#  Install des packet php
docker compose run --rm sf composer install




# Base de données de tests
docker compose run --rm sf php bin/console doctrine:migrations:migrate --env=test --no-interaction
exit_code=$?
if [ $exit_code -ne 0 ]; then
    docker compose run --rm sf php bin/console doctrine:database:create --env=test
    docker compose run --rm sf php bin/console doctrine:migrations:migrate --env=test --no-interaction
# else
#     echo "Base de données de tests à jour"
fi
docker compose run --rm sf php bin/console doctrine:fixtures:load --env=test --no-interaction

# docker compose run --rm sf php bin/console doctrine:migrations:migrate --env=test -y

# # Install des packet JS
# docker compose run --rm encore npm i
# docker compose run --rm encore npm run dev

# compilation des assets
docker compose run --rm sf php bin/console importmap:install
docker compose run --rm sf php bin/console sass:build
docker compose run --rm sf php bin/console asset-map:compile

# Liste des migrations Doctrine
docker compose run --rm sf php bin/console doctrine:migrations:list