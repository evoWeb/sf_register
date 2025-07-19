MAKEFLAGS += --warn-undefined-variables
SHELL := /bin/bash
.EXPORT_ALL_VARIABLES:
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c
.SILENT:

PHP_VERSION := 8.3

.PHONY: functional-test
functional-test: ##@ Run functional tests
	Build/Scripts/runTests.sh -x -p ${PHP_VERSION} -d sqlite -s functional Tests/Functional

.PHONY: install-build
install-build: ##@ Composer install
	echo "Installed build tools started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s composerInstall;
	echo "Installed build tools finished";

.PHONY: cleanup
cleanup:
	echo "Cleanup started"
	Build/Scripts/runTests.sh -s clean
	Build/Scripts/additionalTests.sh -s clean
	echo "Cleanup finished";
