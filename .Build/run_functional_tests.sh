#!/usr/bin/env bash

echo "Running functional tests";

export PHP_IDE_CONFIG="serverName=www.dev8.lan";
export TYPO3_PATH_ROOT="/home/www/dev8/www/.Build/Web/";

export typo3DatabaseName="typo3";
export typo3DatabaseHost="localhost";
export typo3DatabaseUsername="typo3";
export typo3DatabasePassword="t_dev";

/usr/bin/php \
    -dxdebug.remote_autostart=1 \
    -dxdebug.remote_host=127.0.0.1 \
    ../../../../../bin/phpunit \
    --colors \
    -c ../../../../../vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml \
    ../Tests/Functional/Controller/FeuserPasswordControllerTest.php
