#!/bin/bash

export NC='\e[0m'
export RED='\e[0;31m'
export GREEN='\e[0;32m'

THIS_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
cd "$THIS_SCRIPT_DIR" || exit 1

#################################################
# Run resource tests.
# Arguments:
#   none
#################################################
checkResources () {
    echo "#################################################################" >&2
    echo "Checking documentation, TypeScript and Scss files" >&2
    echo "#################################################################" >&2

    ./additionalTests.sh -s lintScss
    EXIT_CODE_SCSS=$?

    ./additionalTests.sh -s lintTypescript
    EXIT_CODE_TYPESCRIPT=$?

    ./additionalTests.sh -s lintXliff
    EXIT_CODE_XLIFF=$?

    ./additionalTests.sh -s buildDocumentation
    EXIT_CODE_DOCUMENTATION=$?

    echo "#################################################################" >&2
    echo "Checked documentation, TypeScript and Scss files" >&2
    if [[ ${EXIT_CODE_SCSS} -eq 0 ]] && \
        [[ ${EXIT_CODE_TYPESCRIPT} -eq 0 ]] && \
        [[ ${EXIT_CODE_XLIFF} -eq 0 ]] && \
        [[ ${EXIT_CODE_DOCUMENTATION} -eq 0 ]]
    then
        echo -e "${GREEN}Resources valid${NC}" >&2
    else
        echo -e "${RED}Resources invalid${NC}" >&2
    fi
    echo "#################################################################" >&2
    echo "" >&2
}

#################################################
# Run test matrix.
# Arguments:
#   php version
#   typo3 version
#   testing framework version
#   test path
#   prefer lowest
#################################################
runFunctionalTests () {
    local PHP_VERSION="${1}"
    local TYPO3_VERSION=${2}
    local TESTING_FRAMEWORK=${3}
    local TEST_PATH=${4}
    local PREFER_LOWEST=${5}

    echo "#################################################################" >&2
    echo "Run unit and/or functional tests on TYPO3 ${TYPO3_VERSION}" >&2
    echo "with PHP ${PHP_VERSION} and testing framework ${TESTING_FRAMEWORK}">&2
    echo "#################################################################" >&2

    echo -n "Restore composer.json state  ... " ; \
        rm ../../composer.lock ; \
        git checkout ../../composer.json ; \
        echo "done"

    ./runTests.sh -s cleanTests

    ./additionalTests.sh -p ${PHP_VERSION} -s lintPhp || exit 1 ; \
        EXIT_CODE_LINT=$?

    ./runTests.sh -p ${PHP_VERSION} -s composerInstall || exit 1 ; \
        EXIT_CODE_INSTALL=$?

    ./additionalTests.sh -p ${PHP_VERSION} \
        -s composerInstallPackage \
        -q "typo3/cms-core:${TYPO3_VERSION}" || exit 1 ; \
        EXIT_CODE_CORE=$?

    ./additionalTests.sh -p ${PHP_VERSION} \
        -s composerInstallPackage \
        -q "typo3/testing-framework:${TESTING_FRAMEWORK}" \
        -o " --dev ${PREFER_LOWEST}" || exit 1 ; \
        EXIT_CODE_FRAMEWORK=$?

    ./runTests.sh -p ${PHP_VERSION} -s composerValidate || exit 1 ; \
        EXIT_CODE_VALIDATE=$?

    ./runTests.sh -p ${PHP_VERSION} -d sqlite -s functional ${TEST_PATH} || exit 1 ; \
        EXIT_CODE_FUNCTIONAL=$?

    echo "#################################################################" >&2
    echo "Run unit and/or functional tests on TYPO3 ${TYPO3_VERSION}" >&2
    echo "with PHP ${PHP_VERSION} and testing framework ${TESTING_FRAMEWORK}">&2
    if [[ ${EXIT_CODE_LINT} -eq 0 ]] && \
        [[ ${EXIT_CODE_INSTALL} -eq 0 ]] && \
        [[ ${EXIT_CODE_CORE} -eq 0 ]] && \
        [[ ${EXIT_CODE_FRAMEWORK} -eq 0 ]] && \
        [[ ${EXIT_CODE_VALIDATE} -eq 0 ]] && \
        [[ ${EXIT_CODE_FUNCTIONAL} -eq 0 ]]
    then
        echo -e "${GREEN}SUCCESS${NC}" >&2
    else
        echo -e "${RED}FAILURE${NC}" >&2
        exit 1
    fi
    echo "#################################################################" >&2
    echo "" >&2
}

#################################################
# Removes all files created by tests.
# Arguments:
#   none
#################################################
cleanup () {
    ./runTests.sh -s clean
    ./additionalTests.sh -s clean
    git checkout ../../composer.json
}

checkResources

runFunctionalTests "8.2" "^12.0" "dev-main" "Tests/Functional" || exit 1
runFunctionalTests "8.2" "^12.0" "dev-main" "Tests/Functional" "--prefer-lowest" || exit 1
# runFunctionalTests "8.1" "dev-main" "dev-main" "Tests/Functional" || exit 1

cleanup
