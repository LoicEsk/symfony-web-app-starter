#!/bin/bash

################################################################################
# Title: Untitled-1
# Description: A Bash script
# Author: Your Name
# Date: YYYY-MM-DD
# Usage: ./Untitled-1
################################################################################

# Your code goes here

php bin/console messenger:stop-workers

composer install

# Mise à jour de la base de données
php bin/console doctrine:migrations:list
php bin/console doctrine:migrations:migrate

# compilation des assets
php bin/console importmap:install
php bin/console sass:build
php bin/console asset-map:compile