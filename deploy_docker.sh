#!/bin/bash

# Build & start the dev stack (compose.yaml + compose.override.yaml, picked up automatically)
docker compose up --wait --build

# Base de données de tests
docker compose exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction
exit_code=$?
if [ $exit_code -ne 0 ]; then
    docker compose exec php php bin/console doctrine:database:drop --force --env=test -q
    docker compose exec php php bin/console doctrine:database:create --env=test
    docker compose exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction
fi
docker compose exec php php bin/console doctrine:fixtures:load --env=test --no-interaction

# compilation des assets
docker compose exec php php bin/console importmap:install
docker compose exec php php bin/console sass:build
docker compose exec php php bin/console asset-map:compile

echo "Prêt : http://localhost:${HTTP_PORT:-8000}"
