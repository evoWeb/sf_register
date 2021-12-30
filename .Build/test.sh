#!/usr/bin/env bash

export PACKAGE='evoWeb/sf-register';
export T3EXTENSION='sf_register';

runFunctionalTests () {
    local PHP=${1};
    local TYPO3_VERSION=${2};
    local TESTING_FRAMEWORK=${3};
    local PREFER_LOWEST=${4};
    local COMPOSER='/usr/local/bin/composer';

    /usr/bin/php${PHP} --version;
    /usr/bin/php${PHP} ${COMPOSER} --version;

    echo "Lint PHP ${PHP}"
    errors=$(find . -name \*.php ! -path "./.Build/*" -exec /usr/bin/php${PHP} -d display_errors=stderr -l "{}" 2>&1 >/dev/null \;) && echo "${errors}" && test -z "${errors}";

    /usr/bin/php${PHP} ${COMPOSER} validate;

    if [ "${TYPO3_VERSION}" = "dev-main" ]; then
        /usr/bin/php${PHP} ${COMPOSER} config minimum-stability dev;
        /usr/bin/php${PHP} ${COMPOSER} config prefer-stable true;
    fi

    /usr/bin/php${PHP} ${COMPOSER} require typo3/cms-core="${TYPO3_VERSION}" ${PREFER_LOWEST};

    if [ ! -z "${TESTING_FRAMEWORK}" ]; then
        /usr/bin/php${PHP} ${COMPOSER} require --dev typo3/testing-framework="${TESTING_FRAMEWORK}";
    fi

    echo "Run ${TYPO3_VERSION} unit tests with ${PHP}";
    /usr/bin/php${PHP} .Build/Web/vendor/bin/phpunit --colors -c .Build/Web/vendor/typo3/testing-framework/Resources/Core/Build/UnitTests.xml Tests/Unit/;

    echo "Run ${TYPO3_VERSION} functional tests with ${PHP} and testing framework ${TESTING_FRAMEWORK}";
    TYPO3_PATH_WEB="$PWD/.Build/Web" typo3DatabaseDriver="pdo_sqlite" /usr/bin/php${PHP} .Build/Web/vendor/bin/phpunit --colors -c .Build/Web/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml Tests/Functional/;

    git checkout composer.json;
    rm composer.lock
    rm -rf .Build/Web/
}

cd ../;

runFunctionalTests "7.4" "^11.5" "^6.6.2";
#runFunctionalTests "7.4" "^11.5.2" "^6.6.2" "--prefer-lowest";
#runFunctionalTests "7.4" "dev-main" "dev-main";
