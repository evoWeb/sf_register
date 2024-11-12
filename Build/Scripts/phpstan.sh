#!/bin/bash

THIS_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
cd "$THIS_SCRIPT_DIR" || exit 1
cd ../../ || exit 1
CORE_ROOT="${PWD}"

Build/Scripts/runTests.sh -s composerInstall

Build/Scripts/runTests.sh -s phpstan

Build/Scripts/runTests.sh -s clean
Build/Scripts/additionalTests.sh -s clean
