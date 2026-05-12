<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\DataTransfer;

use OxidEsales\MediaLibrary\Media\DataType\FilePath;
use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilePath::class)]
class FilePathTest extends TestCase
{
    #[Test]
    public function getPath(): void
    {
        $exampleFileName = uniqid();
        $examplePath = uniqid() . '/' . $exampleFileName;

        $sut = $this->getSut(filePath: $examplePath);

        $this->assertSame($examplePath, $sut->getPath());
    }

    #[Test]
    public function getFileName(): void
    {
        $exampleFileName = uniqid();
        $examplePath = uniqid() . '/' . $exampleFileName;

        $sut = $this->getSut(filePath: $examplePath);

        $this->assertSame($exampleFileName, $sut->getFileName());
    }

    #[Test]
    #[DataProvider('extensionProvider')]
    public function getExtension(string $fileName, string $expectedExtension): void
    {
        $sut = $this->getSut(filePath: '/some/path/' . $fileName);

        $this->assertSame($expectedExtension, $sut->getExtension());
    }

    public static function extensionProvider(): \Generator
    {
        yield 'lowercase extension preserved' => [
            'fileName' => uniqid() . '.txt',
            'expectedExtension' => 'txt',
        ];

        yield 'uppercase extension preserved' => [
            'fileName' => uniqid() . '.PNG',
            'expectedExtension' => 'PNG',
        ];

        yield 'mixed case extension preserved' => [
            'fileName' => uniqid() . '.Svg',
            'expectedExtension' => 'Svg',
        ];

        yield 'no extension yields empty string' => [
            'fileName' => uniqid(),
            'expectedExtension' => '',
        ];
    }

    private function getSut(string $filePath): FilePathInterface
    {
        return new FilePath($filePath);
    }
}
