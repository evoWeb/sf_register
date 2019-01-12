#!/usr/bin/env bash

echo "Running functional tests";

IP="127.0.0.1";
export PHP_IDE_CONFIG="serverName=www.sf-register.lan";
export TYPO3_PATH_APP="${PWD}/Web/";
export TYPO3_PATH_ROOT="${PWD}/Web/";

export typo3DatabaseName="functional";
export typo3DatabaseDriver="pdo_sqlite";

/usr/bin/php \
    -dxdebug.remote_enable=1 \
    -dxdebug.remote_mode=req \
    -dxdebug.remote_port=9000 \
    -dxdebug.remote_autostart=1 \
    -dxdebug.remote_host=${IP} \
    ./bin/phpunit \
    --colors \
    -c ./Web/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml \
    ../Tests/Functional/Controller/FeuserPasswordControllerTest.php
