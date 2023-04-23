#!/usr/bin/env bash

cd ../
composer install
cd ./.Build
Web/vendor/bin/phpstan analyse -c phpstan/phpstan.neon --memory-limit 4G --no-progress