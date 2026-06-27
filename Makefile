MAKEFLAGS += --warn-undefined-variables
SHELL := /bin/bash
.EXPORT_ALL_VARIABLES:
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c
.SILENT:

# use the rest as arguments for "run"
_ARGS := $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))
# ...and turn them into do-nothing targets
$(eval $(_ARGS):;@:)

PHP_VERSION := 8.4


##@
##@ Commands for local task
##@


.PHONY: install
install: ##@ Composer install
	echo "Installed build tools started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s composerInstall
	echo "Installed build tools finished"


.PHONY: cleanup
cleanup: ##@ Cleanup
	echo "Cleanup started"
	Build/Scripts/runTests.sh -s clean
	echo "Cleanup finished";


.PHONY: cleanTests
cleanTests: ##@ Clean test files but leave cache files
	echo "cleanTests started"
	Build/Scripts/runTests.sh -s cleanTests
	echo "cleanTests finished";


.PHONY: phpstan
phpstan: ##@ Run functional tests
	echo "Checking with phpstan started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s phpstan -- $(_ARGS)
	echo "Checking with phpstan finished"


.PHONY: cgl
cgl: ##@ Coding guideline check with
	echo "Coding guideline check with php-cs-fixer started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s cgl -n
	echo "Coding guideline check with php-cs-fixer finished"
	echo "Checking with phpstan finished"

.PHONY: functional-test
functional-test: ##@ Run functional tests
	echo "Functional tests started"
	Build/Scripts/runTests.sh -x -p ${PHP_VERSION} -d sqlite -s functional Tests/Functional
	echo "Functional tests finished"

.PHONY: npm-update
npm-update:
	echo "Npm update started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s npm update
	echo "Npm update finished"


.PHONY: npm-install
npm-install:
	echo "Npm install started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s npm install
	echo "Npm install finished"


.PHONY: npm-build
npm-build:
	echo "Npm build started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s npm run build
	echo "Npm build finished"
