#!/usr/bin/env bash

IP="127.0.0.1";
DEBUG=1;

runPhpunit ()
{
    local configurationFile=$1
    local testFolder=$2

    /usr/bin/php \
        -dxdebug.remote_enable=1 \
        -dxdebug.remote_mode=req \
        -dxdebug.remote_port=9000 \
        -dxdebug.remote_autostart=${DEBUG} \
        -dxdebug.remote_host=${IP} \
        .Build/bin/phpunit \
        --colors \
        -c ${configurationFile} \
        ${testFolder}
}

runUnitTests ()
{
    echo "Running unit tests";
    runPhpunit ".Build/Web/vendor/typo3/testing-framework/Resources/Core/Build/UnitTests.xml" "./Tests/Unit/";
}

runFunctionalTests ()
{
    export PHP_IDE_CONFIG="serverName=www.sf-register.lan";
    export TYPO3_PATH_APP="${PWD}/.Build/Web/";
    export TYPO3_PATH_ROOT="${PWD}/.Build/Web/";

    export typo3DatabaseName="functional";
    export typo3DatabaseDriver="pdo_sqlite";

    echo "Running functional tests";
    runPhpunit ".Build/Web/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml" "./Tests/Functional/";
}

cd ../;
composer update;

runUnitTests;
runFunctionalTests;
