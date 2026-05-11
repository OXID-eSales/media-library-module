<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator\Svg;

use DOMDocument;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\SvgViolationDetectorInterface;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\SvgScanner;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\SvgScannerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SvgScanner::class)]
class SvgScannerTest extends TestCase
{
    private const SVG_CONTENT = '<svg xmlns="http://www.w3.org/2000/svg"/>';

    #[Test]
    public function scanPassesWhenAllDetectorsReturnFalse(): void
    {
        $detectorAMock = $this->createMock(SvgViolationDetectorInterface::class);
        $detectorAMock->expects($this->once())
            ->method('detect')
            ->with($this->isInstanceOf(DOMDocument::class))
            ->willReturn(false);

        $detectorBMock = $this->createMock(SvgViolationDetectorInterface::class);
        $detectorBMock->expects($this->once())
            ->method('detect')
            ->with($this->isInstanceOf(DOMDocument::class))
            ->willReturn(false);

        $sut = $this->getSut(detectors: [$detectorAMock, $detectorBMock]);
        $sut->scan(self::SVG_CONTENT);
    }

    #[Test]
    public function scanThrowsWhenAnyDetectorReturnsTrue(): void
    {
        $cleanMock = $this->createMock(SvgViolationDetectorInterface::class);
        $cleanMock->expects($this->once())
            ->method('detect')
            ->with($this->isInstanceOf(DOMDocument::class))
            ->willReturn(false);

        $hitsMock = $this->createMock(SvgViolationDetectorInterface::class);
        $hitsMock->expects($this->once())
            ->method('detect')
            ->with($this->isInstanceOf(DOMDocument::class))
            ->willReturn(true);

        $sut = $this->getSut(detectors: [$cleanMock, $hitsMock]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_SVG_DISALLOWED_CONTENT');

        $sut->scan(self::SVG_CONTENT);
    }

    /**
     * @param iterable<SvgViolationDetectorInterface> $detectors
     */
    private function getSut(iterable $detectors = []): SvgScannerInterface
    {
        return new SvgScanner($detectors);
    }
}
