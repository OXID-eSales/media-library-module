<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Sanitizer\Detector;

use DOMDocument;
use OxidEsales\MediaLibrary\Image\Sanitizer\Detector\EventHandlerDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventHandlerDetector::class)]
class EventHandlerDetectorTest extends TestCase
{
    public function testReturnsTrueWhenOnloadIsPresent(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <rect onload="alert(1)" width="10"/>
            </svg>
            XML);

        $this->assertTrue((new EventHandlerDetector())->detect($document));
    }

    public function testReturnsTrueWhenAnimationEventIsPresent(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <animate onbegin="alert(1)" attributeName="x"/>
            </svg>
            XML);

        $this->assertTrue((new EventHandlerDetector())->detect($document));
    }

    public function testReturnsFalseForBenignDocument(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <text font-size="20" stroke="black">hi</text>
            </svg>
            XML);

        $this->assertFalse((new EventHandlerDetector())->detect($document));
    }

    private function loadSvg(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->loadXML($xml);
        return $document;
    }
}
