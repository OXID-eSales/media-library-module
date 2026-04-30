<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator\Svg\Detector;

use DOMDocument;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\ScriptElementDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScriptElementDetector::class)]
class ScriptElementDetectorTest extends TestCase
{
    #[DataProvider('violatingSvgProvider')]
    #[Test]
    public function detectFindsScriptElement(string $svg): void
    {
        $sut = $this->getSut();

        $this->assertTrue($sut->detect($this->loadSvg($svg)));
    }

    public static function violatingSvgProvider(): \Generator
    {
        yield 'top-level script element' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <script>alert(1)</script>
                    <rect/>
                </svg>
                XML,
        ];

        yield 'nested script element inside group' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <g><script>alert(1)</script></g>
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
        yield 'plain rect element' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <rect width="10" height="10"/>
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

    protected function getSut(): ScriptElementDetector
    {
        return new ScriptElementDetector();
    }
}
