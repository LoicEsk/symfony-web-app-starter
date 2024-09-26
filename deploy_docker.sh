#!/bin/bash


#  Install et build Synfony
docker compose run --rm sf composer install


# Base de données de tests
docker compose run --rm sf php bin/console doctrine:migrations:migrate --env=test --no-interaction
exit_code=$?
if [ $exit_code -ne 0 ]; then
    docker compose run --rm sf php bin/console doctrine:database:drop --force --env=test -q
    docker compose run --rm sf php bin/console doctrine:database:create --env=test
    docker compose run --rm sf php bin/console doctrine:migrations:migrate --env=test --no-interaction
# else
#     echo "Base de données de tests à jour"
fi
docker compose run --rm sf php bin/console doctrine:fixtures:load --env=test --no-interaction


# Liste des migrations Doctrine
docker compose run --rm sf php bin/console doctrine:migrations:list