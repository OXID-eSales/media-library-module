<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Sanitizer\Detector;

use DOMDocument;
use OxidEsales\MediaLibrary\Image\Sanitizer\Detector\ForeignObjectDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ForeignObjectDetector::class)]
class ForeignObjectDetectorTest extends TestCase
{
    public function testReturnsTrueWhenForeignObjectIsPresent(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <foreignObject width="100" height="50"/>
            </svg>
            XML);

        $this->assertTrue((new ForeignObjectDetector())->detect($document));
    }

    public function testReturnsFalseForBenignDocument(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg"><rect/></svg>
            XML);

        $this->assertFalse((new ForeignObjectDetector())->detect($document));
    }

    private function loadSvg(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->loadXML($xml);
        return $document;
    }
}
