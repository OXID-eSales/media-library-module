<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator\Svg\Detector;

use DOMDocument;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\ForeignObjectDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ForeignObjectDetector::class)]
class ForeignObjectDetectorTest extends TestCase
{
    #[DataProvider('violatingSvgProvider')]
    #[Test]
    public function detectFindsForeignObject(string $svg): void
    {
        $sut = $this->getSut();

        $this->assertTrue($sut->detect($this->loadSvg($svg)));
    }

    public static function violatingSvgProvider(): \Generator
    {
        yield 'foreignObject element' => [
            'svg' => <<<'XML'
                <svg xmlns="http://www.w3.org/2000/svg">
                    <foreignObject width="100" height="50"/>
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
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg"><rect/></svg>',
        ];
    }

    private function loadSvg(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->loadXML($xml);
        return $document;
    }

    protected function getSut(): ForeignObjectDetector
    {
        return new ForeignObjectDetector();
    }
}
