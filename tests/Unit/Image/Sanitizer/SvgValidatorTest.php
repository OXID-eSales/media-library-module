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
use PHPUnit\Framework\TestCase;

#[CoversClass(SvgValidator::class)]
class SvgValidatorTest extends TestCase
{
    public function testPassesWhenAllDetectorsReturnFalse(): void
    {
        $detectorA = $this->createMock(SvgViolationDetectorInterface::class);
        $detectorA->method('detect')->willReturn(false);

        $detectorB = $this->createMock(SvgViolationDetectorInterface::class);
        $detectorB->method('detect')->willReturn(false);

        $sut = new SvgValidator([$detectorA, $detectorB]);
        $sut->validate('<svg xmlns="http://www.w3.org/2000/svg"/>');

        $this->addToAssertionCount(1);
    }

    public function testThrowsWhenAnyDetectorReturnsTrue(): void
    {
        $clean = $this->createMock(SvgViolationDetectorInterface::class);
        $clean->method('detect')->willReturn(false);

        $hits = $this->createMock(SvgViolationDetectorInterface::class);
        $hits->method('detect')->willReturn(true);

        $sut = new SvgValidator([$clean, $hits]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_SVG_DISALLOWED_CONTENT');

        $sut->validate('<svg xmlns="http://www.w3.org/2000/svg"/>');
    }

    public function testStopsAtFirstHit(): void
    {
        $first = $this->createMock(SvgViolationDetectorInterface::class);
        $first->expects($this->once())->method('detect')->willReturn(true);

        $second = $this->createMock(SvgViolationDetectorInterface::class);
        $second->expects($this->never())->method('detect');

        $sut = new SvgValidator([$first, $second]);

        $this->expectException(ValidationFailedException::class);
        $sut->validate('<svg xmlns="http://www.w3.org/2000/svg"/>');
    }

    public function testEachDetectorReceivesADomDocument(): void
    {
        $detector = $this->createMock(SvgViolationDetectorInterface::class);
        $detector->expects($this->once())
            ->method('detect')
            ->with($this->isInstanceOf(DOMDocument::class))
            ->willReturn(false);

        (new SvgValidator([$detector]))
            ->validate('<svg xmlns="http://www.w3.org/2000/svg"><rect/></svg>');
    }
}
