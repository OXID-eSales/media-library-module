<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\MediaLibrary\Media\Service\FallbackMediaResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FallbackMediaResourceTest extends TestCase
{
    #[Test]
    public function getMediaId(): void
    {
        $sut = new FallbackMediaResource();

        $this->assertSame(FallbackMediaResource::MEDIA_ID, $sut->getMediaId());
        $this->assertSame(32, strlen($sut->getMediaId()));
    }

    #[Test]
    public function getSourcePath(): void
    {
        $sut = new FallbackMediaResource();

        $sourcePath = $sut->getSourcePath();

        $this->assertStringEndsWith('/assets/' . $sut->getFileName(), $sourcePath);
        $this->assertFileExists($sourcePath);
    }

    #[Test]
    public function getFileName(): void
    {
        $sut = new FallbackMediaResource();

        $this->assertSame(basename($sut->getSourcePath()), $sut->getFileName());
    }
}
