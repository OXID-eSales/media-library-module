<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\DefaultDriver;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\ThumbnailGeneratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultDriver::class)]
class DefaultDriverTest extends TestCase
{
    public function testIsOriginSupportedAlwaysReturnTrue(): void
    {
        $sut = $this->getSut();
        $this->assertTrue($sut->isOriginSupported(uniqid()));
    }

    public function testGetThumbnailFileNameReturnsDefaultValue(): void
    {
        $sut = $this->getSut();
        $this->assertSame(
            'default.svg',
            $sut->getThumbnailFileName(
                originalFileName: uniqid(),
                thumbnailSize: $this->createStub(ImageSizeInterface::class),
                isCropRequired: (bool)random_int(0, 1)
            )
        );
    }

    public function testGetThumbnailsGlob(): void
    {
        $sut = $this->getSut();
        $this->assertSame(
            'default.svg',
            $sut->getThumbnailsGlob(uniqid())
        );
    }

    private function getSut(): ThumbnailGeneratorInterface
    {
        return new DefaultDriver();
    }
}
