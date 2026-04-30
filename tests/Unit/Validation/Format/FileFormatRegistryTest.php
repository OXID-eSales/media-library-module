<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Format;

use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormat;
use OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileFormatRegistry::class)]
class FileFormatRegistryTest extends TestCase
{
    #[Test]
    public function findByExtensionReturnsMatchingFormat(): void
    {
        $targetExtension = uniqid();
        $targetFormat = new FileFormat($targetExtension, [uniqid()]);
        $otherFormat = new FileFormat(uniqid(), [uniqid()]);

        $sut = $this->getSut([$targetFormat, $otherFormat]);

        $this->assertSame($targetFormat, $sut->findByExtension($targetExtension));
    }

    #[Test]
    public function findByExtensionIsCaseInsensitive(): void
    {
        $extension = uniqid();
        $format = new FileFormat($extension, [uniqid()]);

        $sut = $this->getSut([$format]);

        $this->assertSame($format, $sut->findByExtension(strtoupper($extension)));
    }

    #[Test]
    public function findByExtensionReturnsNullWhenNotRegistered(): void
    {
        $sut = $this->getSut([new FileFormat(uniqid(), [uniqid()])]);

        $this->assertNull($sut->findByExtension(uniqid()));
    }

    /**
     * @param iterable<FileFormat> $formats
     */
    private function getSut(iterable $formats = []): FileFormatRegistry
    {
        return new FileFormatRegistry($formats);
    }
}
