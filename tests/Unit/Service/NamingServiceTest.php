<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use org\bovigo\vfs\vfsStream;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use OxidEsales\MediaLibrary\Service\NamingService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Service\NamingService
 */
class NamingServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $structure = [
            'someDirectory' => [
                'someFilename.txt' => 'file content',
                'someFilename_5.txt' => 'file content'
            ],
            'someFile.doc' => 'some doc content',
            'someFile_1.doc' => 'some doc content',
            'someFile_3.doc' => 'some doc content'
        ];
        vfsStream::setup('root', 0777, $structure);
    }

    /** @dataProvider sanitizeFilenameDataProvider */
    public function testSanitizeFilename($filename, $expectedResult): void
    {
        $exampleTranslation = [
            'x' => 'y',
            'c' => 'b'
        ];

        $languageStub = $this->createMock(LanguageInterface::class);
        $languageStub->method('getSeoReplaceChars')->willReturn($exampleTranslation);

        $sut = $this->getSut(
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

    /** @dataProvider getUniqueFilenameDataProvider */
    public function testGetUniqueFilename(string $filename, string $expectation): void
    {
        $sut = $this->getSut();
        $this->assertSame($expectation, $sut->getUniqueFilename($filename));
    }

    public function getUniqueFilenameDataProvider(): \Generator
    {
        yield 'not existing file no directory' => [
            'filename' => 'vfs://root/someSimpleFile.ext',
            'expectation' => 'vfs://root/someSimpleFile.ext'
        ];

        yield 'not existing directory' => [
            'filename' => 'vfs://root/directory',
            'expectation' => 'vfs://root/directory'
        ];

        yield 'not existing file in directory' => [
            'filename' => 'vfs://root/someDirectory/someSimpleFile.ext',
            'expectation' => 'vfs://root/someDirectory/someSimpleFile.ext'
        ];

        yield 'not existing directory in directory' => [
            'filename' => 'vfs://root/someDirectory/directory',
            'expectation' => 'vfs://root/someDirectory/directory'
        ];

        yield 'not existing file in not existing directory' => [
            'filename' => 'vfs://root/someOtherDirectory/someSimpleFile.ext',
            'expectation' => 'vfs://root/someOtherDirectory/someSimpleFile.ext'
        ];

        yield 'existing file no directory' => [
            'filename' => 'vfs://root/someFile.doc',
            'expectation' => 'vfs://root/someFile_2.doc'
        ];

        yield 'existing file no directory higher number' => [
            'filename' => 'vfs://root/someFile_3.doc',
            'expectation' => 'vfs://root/someFile_4.doc'
        ];

        yield 'existing directory' => [
            'filename' => 'vfs://root/someDirectory',
            'expectation' => 'vfs://root/someDirectory_1'
        ];

        yield 'existing file in directory' => [
            'filename' => 'vfs://root/someDirectory/someFilename.txt',
            'expectation' => 'vfs://root/someDirectory/someFilename_1.txt'
        ];

        yield 'existing file in directory with higher number' => [
            'filename' => 'vfs://root/someDirectory/someFilename_5.txt',
            'expectation' => 'vfs://root/someDirectory/someFilename_6.txt'
        ];
    }

    private function getSut(
        LanguageInterface $language = null
    ): NamingService {
        return new NamingService(
            language: $language ?? $this->createStub(LanguageInterface::class)
        );
    }
}
