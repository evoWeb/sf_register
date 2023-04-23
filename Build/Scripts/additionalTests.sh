#!/usr/bin/env bash

#
# TYPO3 core test runner based on docker and docker-compose.
#

# Function to write a .env file in Build/testing-docker/local
# This is read by docker-compose and vars defined here are
# used in Build/testing-docker/local/docker-compose.yml
setUpDockerComposeDotEnv() {
    # Delete possibly existing local .env file if exists
    [ -e .env ] && rm .env
    # Set up a new .env file for docker-compose
    {
        echo "COMPOSE_PROJECT_NAME=local"
        # To prevent access rights of files created by the testing, the docker image later
        # runs with the same user that is currently executing the script. docker-compose can't
        # use $UID directly itself since it is a shell variable and not an env variable, so
        # we have to set it explicitly here.
        echo "HOST_UID=$(id -u)"
        # Your local user
        echo "CORE_ROOT=${CORE_ROOT}"
        echo "HOST_USER=${USER}"
        echo "TEST_FILE=${TEST_FILE}"
        echo "PHP_XDEBUG_ON=${PHP_XDEBUG_ON}"
        echo "PHP_XDEBUG_PORT=${PHP_XDEBUG_PORT}"
        echo "DOCKER_PHP_IMAGE=${DOCKER_PHP_IMAGE}"
        echo "EXTRA_TEST_OPTIONS=${EXTRA_TEST_OPTIONS}"
        echo "SCRIPT_VERBOSE=${SCRIPT_VERBOSE}"
        echo "PHPUNIT_RANDOM=${PHPUNIT_RANDOM}"
        echo "CGLCHECK_DRY_RUN=${CGLCHECK_DRY_RUN}"
        echo "DATABASE_DRIVER=${DATABASE_DRIVER}"
        echo "MARIADB_VERSION=${MARIADB_VERSION}"
        echo "MYSQL_VERSION=${MYSQL_VERSION}"
        echo "POSTGRES_VERSION=${POSTGRES_VERSION}"
        echo "PHP_VERSION=${PHP_VERSION}"
        echo "CHUNKS=${CHUNKS}"
        echo "THISCHUNK=${THISCHUNK}"
        echo "DOCKER_SELENIUM_IMAGE=${DOCKER_SELENIUM_IMAGE}"
        echo "IS_CORE_CI=${IS_CORE_CI}"
        echo "PHPSTAN_CONFIG_FILE=${PHPSTAN_CONFIG_FILE}"
        echo "PACKAGE=${PACKAGE}"
        echo "COMPOSER_PARAMETER=${COMPOSER_PARAMETER}"
    } > .env
}

cleanBuildFiles() {
    echo -n "Clean composer install files ... " ; rm -rf \
        ../../../bin/ \
        ../../../Build/phpunit \
        ../../../Build/testing-docker/additional/.env \
        ../../../Build/testing-docker/local/.env \
        ../../../public/ \
        ../../../typo3temp/ \
        ../../../vendor/ \
        ../../../composer.lock ; \
        echo "done"
}

cleanRenderedDocumentationFiles() {
    # > caches
    echo -n "Clean rendered documentation files ... " ; rm -rf \
        ../../../typo3/sysext/*/Documentation-GENERATED-temp ; \
        echo "done"
}

# Test if docker-compose exists, else exit out with error
if ! type "docker-compose" > /dev/null; then
    echo "This script relies on docker and docker-compose. Please install" >&2
    exit 1
fi

# Go to the directory this script is located, so everything else is relative
# to this dir, no matter from where this script is called.
THIS_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
cd "$THIS_SCRIPT_DIR" || exit 1

# Go to directory that contains the local docker-compose.yml file
cd ../testing-docker/additional || exit 1

# Set core root path by checking whether realpath exists
if ! command -v realpath &> /dev/null; then
    echo "Consider installing realpath for properly resolving symlinks" >&2
    CORE_ROOT="${PWD}/../../../"
else
    CORE_ROOT=$(realpath "${PWD}/../../../")
fi

# Option defaults
TEST_SUITE="unit"
DBMS="sqlite"
PHP_VERSION="8.1"
PHP_XDEBUG_ON=0
PHP_XDEBUG_PORT=9003
EXTRA_TEST_OPTIONS=""
SCRIPT_VERBOSE=0
PHPUNIT_RANDOM=""
CGLCHECK_DRY_RUN=""
DATABASE_DRIVER=""
MARIADB_VERSION="10.3"
MYSQL_VERSION="8.0"
POSTGRES_VERSION="10"
CHUNKS=0
THISCHUNK=0
DOCKER_SELENIUM_IMAGE="selenium/standalone-chrome:4.0.0-20211102"
IS_CORE_CI=0
PHPSTAN_CONFIG_FILE="phpstan.local.neon"
PACKAGE=""
COMPOSER_PARAMETER=""

# ENV var "CI" is set by gitlab-ci. We use it here to distinct 'local' and 'CI' environment.
if [ "$CI" == "true" ]; then
    IS_CORE_CI=1
    PHPSTAN_CONFIG_FILE="phpstan.ci.neon"
fi

# Detect arm64 and use a seleniarm image.
# In a perfect world selenium would have a arm64 integrated, but that is not on the horizon.
# So for the time being we have to use seleniarm image.
ARCH=$(uname -m)
if [ $ARCH = "arm64" ]; then
    DOCKER_SELENIUM_IMAGE="seleniarm/standalone-chromium:4.1.2-20220227"
    echo "Architecture" $ARCH "requires" $DOCKER_SELENIUM_IMAGE "to run acceptance tests."
fi

# Option parsing
# Reset in case getopts has been used previously in the shell
OPTIND=1
# Array for invalid options
INVALID_OPTIONS=();
# Simple option parsing based on getopts (! not getopt)
while getopts ":a:s:c:d:i:j:k:p:e:xy:q:o:nhuv" OPT; do
    case ${OPT} in
        s)
            TEST_SUITE=${OPTARG}
            ;;
        q)
            PACKAGE=${OPTARG}
            ;;
        o)
            COMPOSER_PARAMETER=${OPTARG}
            ;;
    esac
done

# Exit on invalid options
if [ ${#INVALID_OPTIONS[@]} -ne 0 ]; then
    echo "Invalid option(s):" >&2
    for I in "${INVALID_OPTIONS[@]}"; do
        echo "-"${I} >&2
    done
    echo >&2
    echo "call \".Build/Scripts/localTests.sh -h\" to display help and valid options"
    exit 1
fi

# Move "7.4" to "php74", the latter is the docker container name
DOCKER_PHP_IMAGE=$(echo "php${PHP_VERSION}" | sed -e 's/\.//')

# Set $1 to first mass argument, this is the optional test file or test directory to execute
shift $((OPTIND - 1))
TEST_FILE=${1}

if [ ${SCRIPT_VERBOSE} -eq 1 ]; then
    set -x
fi

# Suite execution
case ${TEST_SUITE} in
    buildDocumentation)
        setUpDockerComposeDotEnv
        docker-compose run makedoc
        SUITE_EXIT_CODE=$?
        docker-compose down
        ;;
    clean)
        setUpDockerComposeDotEnv
        docker-compose run removedoc
        docker-compose down
        cleanBuildFiles
        ;;
    composerInstallPackage)
        setUpDockerComposeDotEnv
        docker-compose run composer_require_package
        SUITE_EXIT_CODE=$?
        docker-compose down
        ;;
    lintPhp)
        setUpDockerComposeDotEnv
        docker-compose run lint_php
        SUITE_EXIT_CODE=$?
        docker-compose down
        ;;
    lintScss)
        setUpDockerComposeDotEnv
        docker-compose run lint_scss
        SUITE_EXIT_CODE=$?
        docker-compose down
        ;;
    lintTypescript)
        setUpDockerComposeDotEnv
        docker-compose run lint_typescript
        SUITE_EXIT_CODE=$?
        docker-compose down
        ;;
    lintXliff)
        setUpDockerComposeDotEnv
        docker-compose run lint_xliff
        SUITE_EXIT_CODE=$?
        docker-compose down
        ;;
esac


# Print summary
if [ ${SCRIPT_VERBOSE} -eq 1 ]; then
    # Turn off verbose mode for the script summary
    set +x
fi
echo "" >&2
echo "###########################################################################" >&2
echo "Result of ${TEST_SUITE}" >&2
if [[ ${IS_CORE_CI} -eq 1 ]]; then
    echo "Environment: CI" >&2
else
    echo "Environment: local" >&2
fi
echo "PHP: ${PHP_VERSION}" >&2
if [[ ${TEST_SUITE} =~ ^(functional|acceptance|acceptanceInstall)$ ]]; then
    echo "${DBMS_OUTPUT}" >&2
fi

if [[ "${PACKAGE}" != "" ]]; then
    echo "Package: ${PACKAGE}" >&2
fi

if [[ ${SUITE_EXIT_CODE} -eq 0 ]]; then
    echo "SUCCESS" >&2
else
    echo "FAILURE" >&2
fi
echo "###########################################################################" >&2
echo "" >&2

# Exit with code of test suite - This script return non-zero if the executed test failed.
exit $SUITE_EXIT_CODE
