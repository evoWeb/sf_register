#!/usr/bin/env bash

export PACKAGE="evoWeb/sf-register";
export T3EXTENSION="sf_register";

runUnitTests () {
    local PHP=${1};
    local TYPO3_VERSION=${2};
    local TESTING_FRAMEWORK=${3};
    local UNIT_TESTING_FOLDER=${4};
    local FUNCTIONAL_TESTING_FOLDER=${5};
    local COMPOSER="/usr/local/bin/composer";

    ${PHP} --version
    ${PHP} ${COMPOSER} --version

    export TYPO3_PATH_WEB=${PWD}/.Build/Web;
    ${PHP} ${COMPOSER} require typo3/cms-core="${TYPO3_VERSION}" $PREFER_LOWEST;
    if [ ! -z "${TESTING_FRAMEWORK}" ]; then ${PHP} ${COMPOSER} require --dev typo3/testing-framework="${TESTING_FRAMEWORK}"; fi;
    git checkout composer.json;

    mkdir -p .Build/Web/typo3conf/ext/
    [ -L ".Build/Web/typo3conf/ext/${T3EXTENSION}" ] || ln -snvf ../../../../. ".Build/Web/typo3conf/ext/${T3EXTENSION}"

    echo "Running php lint in ${PWD}";
    errors=$(find . -name \*.php ! -path "./.Build/*" ! -path "./var/*" -exec ${PHP} -d display_errors=stderr -l {} 2>&1 >/dev/null \;) && echo "$errors" && test -z "$errors"

    echo "Running ${TYPO3_VERSION} unit tests";
    ${PHP} .Build/bin/phpunit --colors -c .Build/vendor/typo3/testing-framework/Resources/Core/Build/UnitTests.xml Tests/${UNIT_TESTING_FOLDER}/;

    echo "Running ${TYPO3_VERSION} functional tests";
    export typo3DatabaseDriver="pdo_sqlite";
    ${PHP} .Build/bin/phpunit --colors -c .Build/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml Tests/${FUNCTIONAL_TESTING_FOLDER}/;

    rm composer.lock
    rm -rf .Build/Web/
    rm -rf .Build/bin/
    rm -rf .Build/vendor/
    rm -rf var/
}

cd ../;

#runUnitTests "/usr/bin/php7.2" "^9.5.0" "~4.10.0" "Unit9" "Functional9";
runUnitTests "/usr/bin/php7.2" "^10.0.0" "~5.0.11" "Unit" "Functional";
#runUnitTests "/usr/bin/php7.2" "dev-master as 10.0.0" "~5.0.11" "Unit" "Functional";
