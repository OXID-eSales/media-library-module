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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScriptableUrlDetector::class)]
class ScriptableUrlDetectorTest extends TestCase
{
    #[DataProvider('violatingSvgProvider')]
    #[Test]
    public function detectFindsScriptableUrl(string $svg): void
    {
        $sut = $this->getSut();

        $this->assertTrue($sut->detect($this->loadSvg($svg)));
    }

    public static function violatingSvgProvider(): \Generator
    {
        yield 'javascript scheme in href' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <a href="javascript:alert(1)"><text>x</text></a>
                </svg>
                XML,
        ];

        yield 'javascript scheme in xlink:href' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <use xlink:href="javascript:alert(1)"/>
                </svg>
                XML,
        ];

        yield 'data URI scheme in href' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <a href="data:text/html,&lt;script/&gt;"><text>x</text></a>
                </svg>
                XML,
        ];

        yield 'obfuscated javascript scheme (mixed case + leading whitespace)' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <a href="  JaVaScRiPt:alert(1)"><text>x</text></a>
                </svg>
                XML,
        ];
    }

    #[DataProvider('benignSvgProvider')]
    #[Test]
    public function detectIgnoresBenignDocument(string $svg): void
    {
        $sut = $this->getSut();

        $this->assertFalse($sut->detect($this->loadSvg($svg)));
    }

    public static function benignSvgProvider(): \Generator
    {
        yield 'absolute https href' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <a href="https://example.com/page"><text>x</text></a>
                </svg>
                XML,
        ];

        yield 'relative path href' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <a href="/page.html"><text>x</text></a>
                </svg>
                XML,
        ];
    }

    private function loadSvg(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->loadXML($xml);
        return $document;
    }

    protected function getSut(): ScriptableUrlDetector
    {
        return new ScriptableUrlDetector();
    }
}
