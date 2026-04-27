<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Sanitizer\Detector;

use DOMDocument;
use OxidEsales\MediaLibrary\Image\Sanitizer\Detector\ScriptableUrlDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScriptableUrlDetector::class)]
class ScriptableUrlDetectorTest extends TestCase
{
    public function testReturnsTrueForJavascriptHref(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <a href="javascript:alert(1)"><text>x</text></a>
            </svg>
            XML);

        $this->assertTrue((new ScriptableUrlDetector())->detect($document));
    }

    public function testReturnsTrueForXlinkJavascriptHref(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <use xlink:href="javascript:alert(1)"/>
            </svg>
            XML);

        $this->assertTrue((new ScriptableUrlDetector())->detect($document));
    }

    public function testReturnsTrueForDataUriHref(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <a href="data:text/html,&lt;script/&gt;"><text>x</text></a>
            </svg>
            XML);

        $this->assertTrue((new ScriptableUrlDetector())->detect($document));
    }

    public function testReturnsTrueIgnoringCaseAndWhitespace(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <a href="  JaVaScRiPt:alert(1)"><text>x</text></a>
            </svg>
            XML);

        $this->assertTrue((new ScriptableUrlDetector())->detect($document));
    }

    public function testReturnsFalseForBenignHttpHref(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <a href="https://example.com/page"><text>x</text></a>
            </svg>
            XML);

        $this->assertFalse((new ScriptableUrlDetector())->detect($document));
    }

    public function testReturnsFalseForRelativeHref(): void
    {
        $document = $this->loadSvg(<<<'XML'
            <svg xmlns="http://www.w3.org/2000/svg">
                <a href="/page.html"><text>x</text></a>
            </svg>
            XML);

        $this->assertFalse((new ScriptableUrlDetector())->detect($document));
    }

    private function loadSvg(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->loadXML($xml);
        return $document;
    }
}
