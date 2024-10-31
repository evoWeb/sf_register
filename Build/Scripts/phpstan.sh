#!/bin/bash

THIS_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
cd "$THIS_SCRIPT_DIR" || exit 1
cd ../../ || exit 1
CORE_ROOT="${PWD}"

PHP_VERSION="8.2";
HOST_UID=$(id -u)
HOST_PID=$(id -g)
USERSET=""

Build/Scripts/runTests.sh -p $PHP_VERSION -s composerInstall || exit 1;

getPhpImageVersion() {
    case ${1} in
        8.2)
            echo -n "1.12"
            ;;
        8.3)
            echo -n "1.13"
            ;;
        8.4)
            echo -n "1.2"
            ;;
    esac
}

if type "podman" >/dev/null 2>&1; then
    CONTAINER_BIN="podman"
elif type "docker" >/dev/null 2>&1; then
    CONTAINER_BIN="docker"
fi

if [ $(uname) != "Darwin" ] && [ ${CONTAINER_BIN} = "docker" ]; then
    # Run docker jobs as current user to prevent permission issues. Not needed with podman.
    USERSET="--user $HOST_UID"
fi

if ! type ${CONTAINER_BIN} >/dev/null 2>&1; then
    echo "Selected container environment \"${CONTAINER_BIN}\" not found. Please install or use -b option to select one." >&2
    exit 1
fi

IMAGE_PHP="ghcr.io/typo3/core-testing-$(echo "php${PHP_VERSION}" | sed -e 's/\.//'):$(getPhpImageVersion $PHP_VERSION)"

if [ ${CONTAINER_BIN} = "docker" ]; then
    CONTAINER_HOST="host.docker.internal"
    # docker needs the add-host for xdebug remote debugging. podman has host.container.internal built in
    CONTAINER_COMMON_PARAMS=" --rm --add-host "${CONTAINER_HOST}:host-gateway" ${USERSET} -v ${CORE_ROOT}:${CORE_ROOT} -w ${CORE_ROOT}"
else
    # podman
    CONTAINER_HOST="host.containers.internal"
    CONTAINER_COMMON_PARAMS=" --rm -v ${CORE_ROOT}:${CORE_ROOT} -w ${CORE_ROOT}"
fi

COMMAND="./bin/phpstan analyse -c ./Build/phpstan/phpstan.neon --memory-limit 4G --no-progress"

${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name phpstan -e HOME=${CORE_ROOT}/.cache ${IMAGE_PHP} /bin/sh -c "${COMMAND}"

Build/Scripts/runTests.sh -s clean
Build/Scripts/additionalTests.sh -s clean
