#!/usr/bin/env bash

echo "Deploying $1..."

git pull origin master
composer install --no-dev --prefer-dist
