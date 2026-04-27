<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Sanitizer\Detector;

use DOMDocument;
use OxidEsales\MediaLibrary\Image\Sanitizer\Detector\ScriptElementDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScriptElementDetector::class)]
class ScriptElementDetectorTest extends TestCase
{
    public function testReturnsTrueWhenScriptIsPresent(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <script>alert(1)</script>
                <rect/>
            </svg>
            XML);

        $this->assertTrue((new ScriptElementDetector())->detect($document));
    }

    public function testReturnsTrueWhenScriptIsNested(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <g><script>alert(1)</script></g>
            </svg>
            XML);

        $this->assertTrue((new ScriptElementDetector())->detect($document));
    }

    public function testReturnsFalseForBenignDocument(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <rect width="10" height="10"/>
            </svg>
            XML);

        $this->assertFalse((new ScriptElementDetector())->detect($document));
    }

    private function loadSvg(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->loadXML($xml);
        return $document;
    }
}
