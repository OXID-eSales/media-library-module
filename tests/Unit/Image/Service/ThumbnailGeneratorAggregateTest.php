<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Service;

use OxidEsales\MediaLibrary\Image\Exception\AggregatorInputType;
use OxidEsales\MediaLibrary\Image\Exception\NoSupportedDriversForSource;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregate;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregateInterface;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\ThumbnailGeneratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ThumbnailGeneratorAggregate::class)]
class ThumbnailGeneratorAggregateTest extends TestCase
{
    #[Test]
    public function constructorDoesNotAcceptWrongType(): void
    {
        $this->expectException(AggregatorInputType::class);
        $this->getSut(
            thumbnailGenerators: [new \stdClass()],
        );
    }

    #[Test]
    public function getSupportedGeneratorReturnsFirstSupportedGenerator(): void
    {
        $filePath = uniqid();

        $wrongGeneratorStub = $this->createMock(ThumbnailGeneratorInterface::class);
        $wrongGeneratorStub->method('isOriginSupported')->with($filePath)->willReturn(false);

        $expectedGeneratorStub = $this->createMock(ThumbnailGeneratorInterface::class);
        $expectedGeneratorStub->method('isOriginSupported')->with($filePath)->willReturn(true);

        $supportedButLowerPriorityGeneratorStub = $this->createMock(ThumbnailGeneratorInterface::class);
        $supportedButLowerPriorityGeneratorStub->method('isOriginSupported')->with($filePath)->willReturn(true);

        $sut = $this->getSut(
            thumbnailGenerators: [
                $wrongGeneratorStub,
                $expectedGeneratorStub,
                $supportedButLowerPriorityGeneratorStub,
            ],
        );

        $this->assertSame($expectedGeneratorStub, $sut->getSupportedGenerator($filePath));
    }

    #[Test]
    public function getSupportedGeneratorThrowsIfNoGeneratorSupportsSource(): void
    {
        $sut = $this->getSut(
            thumbnailGenerators: [],
        );

        $this->expectException(NoSupportedDriversForSource::class);
        $sut->getSupportedGenerator(uniqid());
    }

    private function getSut(
        ?iterable $thumbnailGenerators = null,
    ): ThumbnailGeneratorAggregateInterface {
        $thumbnailGenerators ??= [];

        return new ThumbnailGeneratorAggregate(
            thumbnailGenerators: $thumbnailGenerators,
        );
    }
}
