<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Sanitizer;

use DOMDocument;
use OxidEsales\MediaLibrary\Image\Sanitizer\Detector\SvgViolationDetectorInterface;
use OxidEsales\MediaLibrary\Image\Sanitizer\SvgValidator;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SvgValidator::class)]
class SvgValidatorTest extends TestCase
{
    #[Test]
    public function validatePassesWhenAllDetectorsReturnFalse(): void
    {
        $detectorAStub = $this->createStub(SvgViolationDetectorInterface::class);
        $detectorAStub->method('detect')->willReturn(false);

        $detectorBStub = $this->createStub(SvgViolationDetectorInterface::class);
        $detectorBStub->method('detect')->willReturn(false);

        $sut = $this->getSut([$detectorAStub, $detectorBStub]);
        $sut->validate('<svg xmlns="http://www.w3.org/2000/svg"/>');

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateThrowsWhenAnyDetectorReturnsTrue(): void
    {
        $cleanStub = $this->createStub(SvgViolationDetectorInterface::class);
        $cleanStub->method('detect')->willReturn(false);

        $hitsStub = $this->createStub(SvgViolationDetectorInterface::class);
        $hitsStub->method('detect')->willReturn(true);

        $sut = $this->getSut([$cleanStub, $hitsStub]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_SVG_DISALLOWED_CONTENT');

        $sut->validate('<svg xmlns="http://www.w3.org/2000/svg"/>');
    }

    #[Test]
    public function validateStopsAtFirstHit(): void
    {
        $firstMock = $this->createMock(SvgViolationDetectorInterface::class);
        $firstMock->expects($this->once())->method('detect')->willReturn(true);

        $secondSpy = $this->createMock(SvgViolationDetectorInterface::class);
        $secondSpy->expects($this->never())->method('detect');

        $sut = $this->getSut([$firstMock, $secondSpy]);

        $this->expectException(ValidationFailedException::class);
        $sut->validate('<svg xmlns="http://www.w3.org/2000/svg"/>');
    }

    #[Test]
    public function validatePassesDomDocumentToEachDetector(): void
    {
        $detectorMock = $this->createMock(SvgViolationDetectorInterface::class);
        $detectorMock->expects($this->once())
            ->method('detect')
            ->with($this->isInstanceOf(DOMDocument::class))
            ->willReturn(false);

        $sut = $this->getSut([$detectorMock]);
        $sut->validate('<svg xmlns="http://www.w3.org/2000/svg"><rect/></svg>');
    }

    protected function getSut(iterable $detectors = []): SvgValidator
    {
        return new SvgValidator($detectors);
    }
}
