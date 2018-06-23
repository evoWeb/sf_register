#!/usr/bin/env bash

echo;
echo "Running functional tests";
export typo3DatabaseName="typo3";
export typo3DatabaseHost="localhost";
export typo3DatabaseUsername="typo3";
export typo3DatabasePassword="t_dev";
php ../../../../../vendor/bin/phpunit --colors \
    -c ../../../../../vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml \
    ../Tests/Functional/;
