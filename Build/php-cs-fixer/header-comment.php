<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * This file adds header to php file which don't have any.
 *
 * Run it using runTests.sh, see 'runTests.sh -h' for more options.
 *
 * Fix entire extension:
 * > Build/Scripts/additionalTests.sh -p 8.3 -s composerInstallPackage -q "typo3/cms-core:[dev-main,13...]"
 * > Build/Scripts/runTests.sh -s cglHeader
 *
 * Fix your current patch:
 * > Build/Scripts/runTests.sh -s cglHeaderGit
 */
if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->in(__DIR__ . '/../../')
    ->exclude('Acceptance/Support/_generated') // EXT:core
    ->exclude('Build')
    // Configuration files do not need header comments
    ->exclude('Configuration')
    ->notName('*locallang*.php')
    ->notName('ext_localconf.php')
    ->notName('ext_tables.php')
    ->notName('ext_emconf.php')
    // ClassAliasMap files do not need header comments
    ->notName('ClassAliasMap.php')
    // CodeSnippets and Examples in Documentation do not need header comments
    ->exclude('Documentation')
    // Third-party inclusion files should not have a changed comment
    ->notName('Rfc822AddressesParser.php')
    ->notName('ClassMapGenerator.php')
;

$headerComment = <<<COMMENT
This file is developed by evoWeb.

It is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License, either version 2
of the License, or any later version.

For the full copyright and license information, please read the
LICENSE.txt file that was distributed with this source code.
COMMENT;

return (new \PhpCsFixer\Config())
    ->setParallelConfig(\PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(false)
    ->setRules([
        'no_extra_blank_lines' => true,
        'header_comment' => [
            'header' => $headerComment,
            'comment_type' => 'comment',
            'separate' => 'both',
            'location' => 'after_declare_strict',
        ],
    ])
    ->setFinder($finder);
