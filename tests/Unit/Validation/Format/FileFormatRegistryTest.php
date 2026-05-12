<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Format;

use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;
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
        string $registeredExtension,
        string $lookupExtension,
    ): void {
        $format = $this->createConfiguredStub(FileFormatInterface::class, [
            'getExtension' => $registeredExtension,
        ]);

        $sut = $this->getSut(formats: [$format]);
        $foundFormat = $sut->findByExtension($lookupExtension);

        $this->assertSame($format, $foundFormat);
    }

    #[Test]
    public function findByExtensionReturnsNullWhenNotRegistered(): void
    {
        $registeredFormat = $this->createConfiguredStub(FileFormatInterface::class, [
            'getExtension' => uniqid(),
        ]);

        $sut = $this->getSut(formats: [$registeredFormat]);

        $unregisteredExtension = uniqid();
        $foundFormat = $sut->findByExtension($unregisteredExtension);

        $this->assertNull($foundFormat);
    }

    public static function caseInsensitiveLookupProvider(): \Generator
    {
        yield 'lowercase registered, uppercase lookup' => [
            'registeredExtension' => 'svg',
            'lookupExtension' => 'SVG',
        ];

        yield 'uppercase registered, lowercase lookup' => [
            'registeredExtension' => 'PNG',
            'lookupExtension' => 'png',
        ];

        yield 'mixed case registered, lowercase lookup' => [
            'registeredExtension' => 'JpEg',
            'lookupExtension' => 'jpeg',
        ];

        yield 'uppercase registered, uppercase lookup' => [
            'registeredExtension' => 'GIF',
            'lookupExtension' => 'GIF',
        ];
    }

    /**
     * @param iterable<FileFormatInterface> $formats
     */
    private function getSut(iterable $formats = []): FileFormatRegistryInterface
    {
        return new FileFormatRegistry($formats);
    }
}
