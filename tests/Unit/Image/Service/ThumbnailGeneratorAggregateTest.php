<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\Exception\AggregatorInputType;
use OxidEsales\MediaLibrary\Image\Exception\NoSupportedDriversForSource;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregate;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\ThumbnailGeneratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ThumbnailGeneratorAggregate::class)]
class ThumbnailGeneratorAggregateTest extends TestCase
{
    public function testConstructorDoesNotAcceptWrongType(): void
    {
        $this->expectException(AggregatorInputType::class);
        new ThumbnailGeneratorAggregate([new \stdClass()]);
    }

    public function testGetSupportedThumbnailGenerator(): void
    {
        $filePath = uniqid();

        $wrongGeneratorStub = $this->createMock(ThumbnailGeneratorInterface::class);
        $wrongGeneratorStub->method('isOriginSupported')->with($filePath)->willReturn(false);

        $expectedGeneratorStub = $this->createMock(ThumbnailGeneratorInterface::class);
        $expectedGeneratorStub->method('isOriginSupported')->with($filePath)->willReturn(true);

        $supportedButLowerPriorityGeneratorStub = $this->createMock(ThumbnailGeneratorInterface::class);
        $supportedButLowerPriorityGeneratorStub->method('isOriginSupported')->with($filePath)->willReturn(true);

        $sut = new ThumbnailGeneratorAggregate([
            $wrongGeneratorStub,
            $expectedGeneratorStub,
            $supportedButLowerPriorityGeneratorStub
        ]);

        $this->assertSame($expectedGeneratorStub, $sut->getSupportedGenerator($filePath));
    }

    public function testNoSupportedDriversExceptionCase(): void
    {
        $sut = new ThumbnailGeneratorAggregate([]);

        $this->expectException(NoSupportedDriversForSource::class);
        $sut->getSupportedGenerator(uniqid());
    }
}
