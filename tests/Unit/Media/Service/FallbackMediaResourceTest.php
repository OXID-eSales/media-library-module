<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\MediaLibrary\Media\Service\FallbackMediaResource;
use OxidEsales\MediaLibrary\Media\Service\FallbackMediaResourceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FallbackMediaResourceTest extends TestCase
{
    #[Test]
    public function getMediaId(): void
    {
        $sut = $this->getSut();

        $this->assertSame(FallbackMediaResource::MEDIA_ID, $sut->getMediaId());
        $this->assertSame(32, strlen($sut->getMediaId()));
    }

    #[Test]
    public function getSourcePath(): void
    {
        $sut = $this->getSut();

        $sourcePath = $sut->getSourcePath();

        $this->assertStringEndsWith('/assets/' . $sut->getFileName(), $sourcePath);
        $this->assertFileExists($sourcePath);
    }

    #[Test]
    public function getFileName(): void
    {
        $sut = $this->getSut();

        $this->assertSame(basename($sut->getSourcePath()), $sut->getFileName());
    }

    private function getSut(): FallbackMediaResourceInterface
    {
        return new FallbackMediaResource();
    }
}
