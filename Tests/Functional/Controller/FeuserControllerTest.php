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

namespace Evoweb\SfRegister\Tests\Functional\Controller;

use Evoweb\SfRegister\Property\TypeConverter\DateTimeConverter;
use Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use EvowebTests\TestClasses\Controller\FeuserCreateController;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/sys_file_storage.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();
    }

    /**
     * configurationManager is a shared object and will be a constructor parameter of the
     * controller (@see Bootstrap::initializeConfiguration); FileService (also a constructor
     * dependency, needed by getPropertyMappingConfiguration()/setTypeConverter()) reads its
     * configuration from the same configurationManager during construction.
     */
    protected function getSubject(string $pluginName = 'Create', string $controllerActionName = 'formAction'): FeuserCreateController
    {
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => $pluginName,
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        // @see RequestBuilder::build
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName($pluginName);
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserCreate');
        $extbaseAttribute->setControllerActionName($controllerActionName);

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserCreateController $subject */
        $subject = $this->get(FeuserCreateController::class);
        $subject->set('request', $request);
        $subject->set('actionMethodName', $controllerActionName);

        return $subject;
    }

    // -- getPropertyMappingConfiguration ---------------------------------------------------

    #[Test]
    public function getPropertyMappingConfigurationCreatesAndConfiguresNewConfigurationWhenNoneGiven(): void
    {
        $subject = $this->getSubject();
        $subject->set('settings', ['fields' => ['selected' => ['username', 'dateOfBirth']]]);

        $userData = ['dateOfBirth' => '2001-03-15'];
        $method = $this->getPrivateMethod($subject, 'getPropertyMappingConfiguration');
        $configuration = $method->invoke($subject, null, $userData);

        self::assertInstanceOf(PropertyMappingConfiguration::class, $configuration);

        // allowProperties(...settings.fields.selected)
        self::assertTrue($configuration->shouldMap('username'));
        self::assertTrue($configuration->shouldMap('dateOfBirth'));
        self::assertFalse($configuration->shouldMap('usergroup'));

        // PersistentObjectConverter is allowed to create new domain objects
        self::assertTrue($configuration->getConfigurationValue(
            PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
        ));

        /** @var FileService $fileService */
        $fileService = $this->get(FileService::class);
        $imageConfiguration = $configuration->forProperty('image.0');
        self::assertSame(
            $fileService->getTempFolder()->getCombinedIdentifier(),
            $imageConfiguration->getConfigurationValue(
                UploadedFileReferenceConverter::class,
                UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER,
            ),
        );
        $confVars = $GLOBALS['TYPO3_CONF_VARS'] ?? [];
        $imageFileExtensions = '';
        if (
            is_array($confVars)
            && is_array($confVars['GFX'] ?? null)
            && is_string($confVars['GFX']['imagefile_ext'] ?? null)
        ) {
            $imageFileExtensions = $confVars['GFX']['imagefile_ext'];
        }
        self::assertNotSame('', $imageFileExtensions);
        self::assertSame(
            $imageFileExtensions,
            $imageConfiguration->getConfigurationValue(
                UploadedFileReferenceConverter::class,
                UploadedFileReferenceConverter::CONFIGURATION_FILE_VALIDATORS,
            ),
        );

        $dateOfBirthConfiguration = $configuration->forProperty('dateOfBirth');
        self::assertSame(
            $userData,
            $dateOfBirthConfiguration->getConfigurationValue(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_USER_DATA,
            ),
        );
    }

    #[Test]
    public function getPropertyMappingConfigurationReusesGivenConfigurationInstance(): void
    {
        $subject = $this->getSubject();
        $subject->set('settings', ['fields' => ['selected' => []]]);

        $given = new PropertyMappingConfiguration();
        $method = $this->getPrivateMethod($subject, 'getPropertyMappingConfiguration');
        $result = $method->invoke($subject, $given, []);

        self::assertSame($given, $result);
    }

    // -- setTypeConverter -------------------------------------------------------------------

    #[Test]
    public function setTypeConverterRegistersConverterOnUserArgumentPropertyMappingConfiguration(): void
    {
        $subject = $this->getSubject();
        $subject->set('settings', ['fields' => ['selected' => ['username']]]);

        $userArgumentData = ['username' => 'newuser'];
        /** @var Request $request */
        $request = $subject->get('request');
        $subject->set('request', $request->withArgument('user', $userArgumentData));

        $subject->call('initializeActionMethodArguments');
        $subject->call('setTypeConverter');

        /** @var Arguments $arguments */
        $arguments = $subject->get('arguments');
        $userArgumentConfiguration = $arguments->getArgument('user')->getPropertyMappingConfiguration();

        self::assertTrue($userArgumentConfiguration->shouldMap('username'));
        self::assertTrue($userArgumentConfiguration->getConfigurationValue(
            PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
        ));
    }

    #[Test]
    public function setTypeConverterDoesNothingWhenUserArgumentIsMissingFromRequest(): void
    {
        $subject = $this->getSubject();
        $subject->set('settings', ['fields' => ['selected' => []]]);
        $subject->set('arguments', new Arguments());

        // Must not throw despite there being no 'user' request argument to configure.
        $subject->call('setTypeConverter');

        /** @var Arguments $arguments */
        $arguments = $subject->get('arguments');
        self::assertSame(0, $arguments->count());
    }

    // -- initializeActionMethodArguments ------------------------------------------------------

    #[Test]
    public function initializeActionMethodArgumentsAppliesSettingsReturnedByOverrideSettingsEventListeners(): void
    {
        $subject = $this->getSubject();
        $subject->set('settings', ['fields' => ['selected' => ['username']], 'original' => 'value']);

        // Simulate a listener modifying the event's settings; the dispatcher contract
        // (@see \TYPO3\CMS\Core\EventDispatcher\EventDispatcher::dispatch()) always returns
        // the very same event instance it received, so reading getSettings() off the original
        // $event variable is equivalent to reading it off dispatch()'s return value. This locks
        // in that the listener-applied settings are visible either way.
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(function (object $event) {
            if (method_exists($event, 'setSettings') && method_exists($event, 'getSettings')) {
                $event->setSettings(['injectedByListener' => true] + $event->getSettings());
            }
            return $event;
        });
        // The dispatcher is injected via ActionController::injectEventDispatcher() at
        // construction time, so it must be replaced directly on the already built
        // instance rather than through the container.
        $subject->set('eventDispatcher', $eventDispatcher);

        $subject->call('initializeActionMethodArguments');

        /** @var array<string, mixed> $settings */
        $settings = $subject->get('settings');
        self::assertTrue($settings['injectedByListener'] ?? false);
        self::assertSame('value', $settings['original']);
    }
}
