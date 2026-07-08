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

namespace Evoweb\SfRegister\Tests\Unit\Services\Setup;

use Evoweb\SfRegister\Services\Setup\AutologinCheck;
use Evoweb\SfRegister\Services\Setup\CheckFactory;
use Evoweb\SfRegister\Services\Setup\UserGroupCheck;
use Evoweb\SfRegister\Services\Setup\UsernameCheck;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CheckFactoryTest extends UnitTestCase
{
    protected ContainerInterface $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    #[Test]
    public function getCheckInstancesWithDefaultCheckClassnamesReturnsDefaultCheckInstancesInOrder(): void
    {
        $subject = new CheckFactory($this->container);

        $checks = $subject->getCheckInstances();

        self::assertCount(3, $checks);
        self::assertInstanceOf(UserGroupCheck::class, $checks[0]);
        self::assertInstanceOf(AutologinCheck::class, $checks[1]);
        self::assertInstanceOf(UsernameCheck::class, $checks[2]);
    }

    #[Test]
    public function getCheckInstancesWithCustomCheckClassnamesReturnsInstancesOfConfiguredClasses(): void
    {
        $subject = new CheckFactory($this->container, [
            UsernameCheck::class,
        ]);

        $checks = $subject->getCheckInstances();

        self::assertCount(1, $checks);
        self::assertInstanceOf(UsernameCheck::class, $checks[0]);
    }

    #[Test]
    public function getCheckInstancesWithEmptyCheckClassnamesReturnsEmptyArray(): void
    {
        $subject = new CheckFactory($this->container, []);

        self::assertSame([], $subject->getCheckInstances());
    }

    #[Test]
    public function getCheckInstancesWithUnknownCheckClassnameThrowsError(): void
    {
        $subject = new CheckFactory($this->container, [
            'Evoweb\\SfRegister\\Services\\Setup\\NonExistentCheck',
        ]);

        $this->expectException(\Error::class);

        $subject->getCheckInstances();
    }
}
