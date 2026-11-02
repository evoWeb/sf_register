<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\Property\TypeConverter;

use Evoweb\SfRegister\Property\TypeConverter\ObjectStorageConverter;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ObjectStorageConverterTest extends AbstractTestBase
{
    protected function getSubject(): ObjectStorageConverter
    {
        /** @var ObjectStorageConverter $subject */
        $subject = $this->get(ObjectStorageConverter::class);
        return $subject;
    }

    /**
     * @return array<string, array{0: array<array-key, mixed>, 1: array<array-key, mixed>}>
     */
    public static function sourceDataProvider(): array
    {
        return [
            'empty source returns empty array' => [
                [],
                [],
            ],
            // Regression guard: scalar children must be preserved. This converter is registered
            // (Configuration/Services.yaml) for target ObjectStorage / sources array at priority
            // 21 -- ABOVE TYPO3 core's own ObjectStorageConverter (priority 10) -- so it
            // intercepts EVERY array->ObjectStorage<T> conversion in the instance. Extbase's
            // PersistentObjectConverter accepts is_string($source) || is_int($source) children
            // (fetch-by-UID), i.e. the standard multi-select-of-persisted-objects idiom (e.g.
            // FrontendUser::$usergroup, an ObjectStorage<FrontendUserGroup>, submitting bare UID
            // strings ['0'=>'3','1'=>'7']). getSourceChildPropertiesToBeConverted() calls
            // isUploadType($value) on every raw child; isUploadType() guards with
            // `is_array($propertyValue) &&` first, so a scalar child falls through the
            // else-branch unchanged instead of raising a TypeError. Scalar children are
            // REACHABLE in production, not dead code - a stricter isUploadType(array) signature
            // without this guard would silently break bare-UID ObjectStorage conversions.
            'scalar child (bare uid reference) is kept as-is' => [
                ['0' => '3', '1' => '7'],
                ['0' => '3', '1' => '7'],
            ],
            'regular child properties are kept unchanged' => [
                [
                    '0' => ['uid' => 1, 'title' => 'First'],
                    '1' => ['uid' => 2, 'title' => 'Second'],
                ],
                [
                    '0' => ['uid' => 1, 'title' => 'First'],
                    '1' => ['uid' => 2, 'title' => 'Second'],
                ],
            ],
            'upload with an actual file is kept' => [
                [
                    '0' => ['tmp_name' => '/tmp/php123', 'error' => \UPLOAD_ERR_OK, 'name' => 'test.jpg'],
                ],
                [
                    '0' => ['tmp_name' => '/tmp/php123', 'error' => \UPLOAD_ERR_OK, 'name' => 'test.jpg'],
                ],
            ],
            'empty upload without submitted file is filtered out' => [
                [
                    '0' => ['tmp_name' => '', 'error' => \UPLOAD_ERR_NO_FILE, 'name' => ''],
                ],
                [],
            ],
            'empty upload with a submitted file resource pointer is kept' => [
                [
                    '0' => [
                        'tmp_name' => '',
                        'error' => \UPLOAD_ERR_NO_FILE,
                        'submittedFile' => ['resourcePointer' => '1234'],
                    ],
                ],
                [
                    '0' => [
                        'tmp_name' => '',
                        'error' => \UPLOAD_ERR_NO_FILE,
                        'submittedFile' => ['resourcePointer' => '1234'],
                    ],
                ],
            ],
            'mixture of regular property, kept upload and filtered empty upload' => [
                [
                    'existingChild' => ['uid' => 5],
                    'newUpload' => ['tmp_name' => '/tmp/php456', 'error' => \UPLOAD_ERR_OK],
                    'emptyUpload' => ['tmp_name' => '', 'error' => \UPLOAD_ERR_NO_FILE],
                ],
                [
                    'existingChild' => ['uid' => 5],
                    'newUpload' => ['tmp_name' => '/tmp/php456', 'error' => \UPLOAD_ERR_OK],
                ],
            ],
            'array with tmp_name but without error key is not detected as upload and kept' => [
                [
                    '0' => ['tmp_name' => '/tmp/php789'],
                ],
                [
                    '0' => ['tmp_name' => '/tmp/php789'],
                ],
            ],
            'array with error but without tmp_name key is not detected as upload and kept' => [
                [
                    '0' => ['error' => \UPLOAD_ERR_OK],
                ],
                [
                    '0' => ['error' => \UPLOAD_ERR_OK],
                ],
            ],
        ];
    }

    /**
     * @param array<array-key, mixed> $source
     * @param array<array-key, mixed> $expected
     */
    #[DataProvider('sourceDataProvider')]
    #[Test]
    public function getSourceChildPropertiesToBeConvertedReturnsExpectedProperties(array $source, array $expected): void
    {
        $subject = $this->getSubject();

        // @phpstan-ignore argument.type (the scalar-child case uses a real bare-uid-reference shape; the @param is narrower)
        self::assertSame($expected, $subject->getSourceChildPropertiesToBeConverted($source));
    }
}
