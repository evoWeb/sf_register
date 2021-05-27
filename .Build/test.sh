#!/usr/bin/env bash

export PACKAGE="evoWeb/sf-register";
export T3EXTENSION="sf_register";

runFunctionalTests () {
    local PHP=${1};
    local TYPO3_VERSION=${2};
    local TESTING_FRAMEWORK=${3};
    local PREFER_LOWEST=${4};
    local COMPOSER="/usr/local/bin/composer";

    ${PHP} --version
    ${PHP} ${COMPOSER} --version

    echo "Running php lint"
    errors=$(find . -name \*.php ! -path "./.Build/*" -exec php -d display_errors=stderr -l "{}" 2>&1 >/dev/null \;) && echo "${errors}" && test -z "${errors}"

    ${PHP} ${COMPOSER} validate

    ${PHP} ${COMPOSER} require -n -q typo3/cms-core="${TYPO3_VERSION}" ${PREFER_LOWEST};

    if [ ! -z "${TESTING_FRAMEWORK}" ]; then ${PHP} ${COMPOSER} require --dev typo3/testing-framework="${TESTING_FRAMEWORK}"; fi;

    mkdir -p .Build/Web/typo3conf/ext/
    [ -L ".Build/Web/typo3conf/ext/${T3EXTENSION}" ] || ln -snvf ../../../../. ".Build/Web/typo3conf/ext/${T3EXTENSION}"

    ${PHP} ${COMPOSER} require --dev typo3/cms-extensionmanager="${TYPO3_VERSION}";

    echo "Running ${TYPO3_VERSION} functional tests with $(which php)";
    export TYPO3_PATH_WEB=$PWD/.Build/Web;
    export typo3DatabaseDriver="pdo_sqlite";
    ${PHP} .Build/Web/vendor/bin/phpunit ${FILTER} --colors -c .Build/Web/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml Tests/Functional/;

    git checkout composer.json;
    rm composer.lock
    rm -rf .Build/Web/
}

cd ../;

runFunctionalTests "/usr/bin/php7.4" "^11.2.0" "^6.6.2";
#runFunctionalTests "/usr/bin/php7.4" "^11.2.0" "^6.6.2" "--prefer-lowest";
#runFunctionalTests "/usr/bin/php7.4" "dev-master as 11.3.0" "^6.6.2";

git checkout composer.json
