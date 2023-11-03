<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use OxidEsales\MediaLibrary\Service\NamingService;
use PHPUnit\Framework\TestCase;

class NamingServiceTest extends TestCase
{
    /** @dataProvider sanitizeFilenameDataProvider */
    public function testSanitizeFilename($filename, $expectedResult): void
    {
        $exampleTranslation = [
            'x' => 'y',
            'c' => 'b'
        ];

        $languageStub = $this->createMock(LanguageInterface::class);
        $languageStub->method('getSeoReplaceChars')->willReturn($exampleTranslation);

        $sut = new NamingService(
            language: $languageStub
        );

        $this->assertSame($expectedResult, $sut->sanitizeFilename($filename));
    }

    public function sanitizeFilenameDataProvider(): \Generator
    {
        yield "no extension" => ['filename' => 'somexc', 'expectedResult' => 'someyb'];
        yield "extension should not be changed" => ['filename' => 'somexc.xcabc', 'expectedResult' => 'someyb.xcabc'];
        yield "multiple dots replaced" => ['filename' => 'somexc.!^xc.xcabc', 'expectedResult' => 'someyb-yb.xcabc'];
        yield "not affected" => ['filename' => 'some1-_', 'expectedResult' => 'some1-_'];
    }
}
