#!/usr/bin/env python3

import os
import subprocess
import sys

_SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))

RED = '\033[0;31m'
GREEN = '\033[0;32m'
NC = '\033[0m'

PHP_VERSIONS = ['8.2', '8.3', '8.4', '8.5']
PREFER_OPTIONS = ['', '--prefer-lowest']
PACKAGES = [
    {'core': '^14.3', 'framework': '^9.5.0'},
]

matrix = [
    [php, prefer, pkg]
    for php in PHP_VERSIONS
    for prefer in PREFER_OPTIONS
    for pkg in PACKAGES
]


def run(cmd: str) -> None:
    result = subprocess.run(cmd, shell=True)
    if result.returncode != 0:
        sys.exit(result.returncode)


def cleanup() -> None:
    php = '8.4'
    run(f'./runTests.sh -p {php} -s cleanTests')


def check_resources() -> None:
    php = '8.4'
    print('################################################################')
    print(' Checking documentation files')
    print('################################################################')
    run(f'./runTests.sh -p {php} -s composerInstall')
    run(f'./runTests.sh -p {php} -s lintScss')
    run(f'./runTests.sh -p {php} -s lintTypescript')
    run(f'./runTests.sh -p {php} -s lintYaml')
    run(f'./runTests.sh -p {php} -s lintServicesYaml')
    run(f'./runTests.sh -p {php} -s phpstan')
    run(f'./runTests.sh -p {php} -s cgl -n')
    run(f'./runTests.sh -p {php} -s checkIntegrityXliff')
    run(f'./runTests.sh -p {php} -s checkRstRenderingSingle')
    print(f'{GREEN}Resources valid{NC}')


def run_functional_tests(php: str, core: str, framework: str, prefer: str = '') -> None:
    prefer_arg = f' {prefer}' if prefer else ' '
    print('###########################################################################')
    print(f' Run functional tests with PHP {php}, TYPO3 {core}, framework {framework}')
    if prefer:
        print(f' Additional: {prefer}')
    print('###########################################################################')
    cleanup()
    run(f'./runTests.sh -p {php} -s lintPhp')
    run(f'./runTests.sh -p {php} -s composer -- require {prefer_arg} "typo3/cms-core:{core}"')
    run(f'./runTests.sh -p {php} -s composer -- require --dev {prefer_arg} "typo3/testing-framework:{framework}"')
    run(f'./runTests.sh -p {php} -s composerValidate')
    run(f'./runTests.sh -p {php} -s functional Tests/Functional')
    run(f'./runTests.sh -p {php} -s unit Tests/Unit')
    print(f'{GREEN}SUCCESS{NC}')


def main() -> None:
    check_resources()

    debug = '--debug' in sys.argv
    if debug:
        run_functional_tests('8.2', '^14.3', '^9.5.0', '--prefer-lowest')
    else:
        for php, prefer, pkg in matrix:
            run_functional_tests(php, pkg['core'], pkg['framework'], prefer)
    cleanup()

if __name__ == '__main__':
    os.chdir(_SCRIPT_DIR)
    main()
