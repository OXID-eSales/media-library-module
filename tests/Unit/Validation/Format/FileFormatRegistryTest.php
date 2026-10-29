<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Format;

use OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistry;
use OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileFormatRegistry::class)]
class FileFormatRegistryTest extends TestCase
{
    #[Test]
    #[DataProvider('caseInsensitiveLookupProvider')]
    public function findByExtensionMatchesRegardlessOfCase(
        string $lookupExtension,
        string $expectedExtension,
        array $expectedMimeTypes,
    ): void {
        $sut = $this->getSut();
        $foundFormat = $sut->findByExtension($lookupExtension);

        $this->assertNotNull($foundFormat);
        $this->assertSame($expectedExtension, $foundFormat->getExtension());
        $this->assertSame($expectedMimeTypes, $foundFormat->getMimeTypes());
    }

    #[Test]
    public function findByExtensionReturnsNullWhenNotRegistered(): void
    {
        $sut = $this->getSut();

        $unregisteredExtension = uniqid('unregistered_extension_');
        $foundFormat = $sut->findByExtension($unregisteredExtension);

        $this->assertNull($foundFormat);
    }

    public static function caseInsensitiveLookupProvider(): \Generator
    {
        yield 'lowercase lookup' => [
            'lookupExtension' => 'png',
            'expectedExtension' => 'png',
            'expectedMimeTypes' => ['image/png'],
        ];

        yield 'uppercase lookup' => [
            'lookupExtension' => 'SVG',
            'expectedExtension' => 'svg',
            'expectedMimeTypes' => ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml'],
        ];

        yield 'mixed case lookup' => [
            'lookupExtension' => 'JpEg',
            'expectedExtension' => 'jpeg',
            'expectedMimeTypes' => ['image/jpeg'],
        ];
    }

    private function getSut(): FileFormatRegistryInterface
    {
        return new FileFormatRegistry();
    }
}
