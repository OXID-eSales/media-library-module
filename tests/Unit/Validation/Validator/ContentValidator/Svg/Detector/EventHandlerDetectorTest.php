<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator\Svg\Detector;

use DOMDocument;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\EventHandlerDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventHandlerDetector::class)]
class EventHandlerDetectorTest extends TestCase
{
    #[DataProvider('violatingSvgProvider')]
    #[Test]
    public function detectFindsEventHandler(string $svg): void
    {
        $sut = $this->getSut();

        $this->assertTrue($sut->detect($this->loadSvg($svg)));
    }

    public static function violatingSvgProvider(): \Generator
    {
        yield 'onload attribute' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <rect onload="alert(1)" width="10"/>
                </svg>
                XML,
        ];

        yield 'animation onbegin attribute' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <animate onbegin="alert(1)" attributeName="x"/>
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
        yield 'text element with styling attributes' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <text font-size="20" stroke="black">hi</text>
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

    protected function getSut(): EventHandlerDetector
    {
        return new EventHandlerDetector();
    }
}
