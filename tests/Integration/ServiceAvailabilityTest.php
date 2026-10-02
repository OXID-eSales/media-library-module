<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration;

use OxidEsales\EshopCommunity\Internal\Container\ContainerBuilderFactory;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ServiceAvailabilityTest extends IntegrationTestCase
{
    private static $cachedContainer;
    private static $decorations;

    public static function setUpBeforeClass(): void
    {
        $containerBuilder = (new ContainerBuilderFactory())->create();
        $container = $containerBuilder->getContainer();
        foreach ($container->getDefinitions() as $id => $definition) {
            $definition->setPublic(true);
            if ($decorated = $definition->getDecoratedService()) {
                self::$decorations[reset($decorated)][] = $id;
            }
        }
        $container->compile(true);

        self::$cachedContainer = $container;
    }

    #[DataProvider('serviceAvailabilityDataProvider')]
    #[Test]
    public function servicesAvailable(string $serviceName): void
    {
        $service = self::$cachedContainer->get($serviceName);
        $this->assertInstanceOf($serviceName, $service);
    }

    #[DataProvider('serviceDecorationProvider')]
    #[Test]
    public function servicesDecorated(string $serviceName, array $expectedDecorations): void
    {
        $decorations = self::$decorations[$serviceName];
        foreach ($expectedDecorations as $oneExpectedDecoration) {
            $this->assertContains($oneExpectedDecoration, $decorations);
        }
    }

    public static function serviceDecorationProvider(): \Generator
    {
        yield [\OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface::class, [
            \OxidEsales\MediaLibrary\Media\Facade\FallbackMediaFacadeDecorator::class,
        ]];
    }

    //todo: list all services here
    public static function serviceAvailabilityDataProvider(): array
    {
        return [
            // Breadcrumb
            [\OxidEsales\MediaLibrary\Breadcrumb\Service\BreadcrumbServiceInterface::class],

            // Compatibility
            [\OxidEsales\MediaLibrary\Compatibility\Facade\MediaIdByPathFacadeInterface::class],
            [\OxidEsales\MediaLibrary\Compatibility\Repository\PathMappingRepositoryInterface::class],
            [\OxidEsales\MediaLibrary\Compatibility\Factory\MediaFileInformationFactoryInterface::class],

            // Image
            [\OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregateInterface::class],
            [\OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface::class],
            [\OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface::class],
            [\OxidEsales\MediaLibrary\Image\ThumbnailGenerator\SvgDriver::class],
            [\OxidEsales\MediaLibrary\Image\ThumbnailGenerator\InterventionDriver::class],
            [\OxidEsales\MediaLibrary\Image\ThumbnailGenerator\DefaultDriver::class],

            // Language
            [\OxidEsales\MediaLibrary\Language\Core\LanguageInterface::class],

            // Media
            [\OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface::class],
            [\OxidEsales\MediaLibrary\Media\Facade\FallbackMediaFacadeDecorator::class],
            [\OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface::class],
            [\OxidEsales\MediaLibrary\Media\Service\FallbackMediaResourceInterface::class],
            [\OxidEsales\MediaLibrary\Media\Service\FallbackMediaSeederInterface::class],
            [\OxidEsales\MediaLibrary\Media\Service\MediaDeletionPolicyServiceInterface::class],
            [\OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface::class],
            [\OxidEsales\MediaLibrary\Media\Service\MediaServiceInterface::class],
            [\OxidEsales\MediaLibrary\Media\Service\FrontendMediaFactoryInterface::class],
            [\OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface::class],
            [\OxidEsales\MediaLibrary\Media\Service\ValidatorStrategyServiceInterface::class],
            [\OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface::class],
            [\OxidEsales\MediaLibrary\Media\Repository\MediaFactoryInterface::class],
            [\OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface::class],
            [\OxidEsales\MediaLibrary\Media\Repository\MediaAltRepositoryInterface::class],
            [\OxidEsales\MediaLibrary\Media\Factory\MediaAltTextFactoryInterface::class],
            [\OxidEsales\MediaLibrary\Media\Controller\MediaAltTextController::class],

            [\OxidEsales\MediaLibrary\Media\Twig\MediaDataExtension::class],
            [\OxidEsales\MediaLibrary\Media\Twig\MediaDataLogicInterface::class],

            // Service
            [\OxidEsales\MediaLibrary\Service\FileSystemServiceInterface::class],
            [\OxidEsales\MediaLibrary\Service\FolderServiceInterface::class],
            [\OxidEsales\MediaLibrary\Service\NamingServiceInterface::class],

            // Settings
            [\OxidEsales\MediaLibrary\Settings\Service\ModuleSettingsInterface::class],

            // Transput
            [\OxidEsales\MediaLibrary\Transput\RequestInterface::class],
            [\OxidEsales\MediaLibrary\Transput\ResponseInterface::class],
            [\OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequestInterface::class],
            [\OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface::class],

            // Validation
            [\OxidEsales\MediaLibrary\Validation\Service\DirectoryNameValidatorChainInterface::class],
            [\OxidEsales\MediaLibrary\Validation\Service\FileNameValidatorChainInterface::class],
            [\OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChainInterface::class],
            [\OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistryInterface::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\FileNameValidator::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\FileUploadStatusValidator::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\FileExtensionValidator::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\MimeTypeValidator::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\ContentValidatorChain::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\RasterImageContentValidator::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\SvgContentValidator::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\SvgScannerInterface::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\ScriptElementDetector::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\ForeignObjectDetector::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\EventHandlerDetector::class],
            [\OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\ScriptableUrlDetector::class],
        ];
    }
}
