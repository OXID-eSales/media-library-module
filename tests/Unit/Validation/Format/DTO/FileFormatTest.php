<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Format\DTO;

use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormat;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileFormat::class)]
class FileFormatTest extends TestCase
{
    #[Test]
    public function exposesExtensionAndMimeTypesAsProvided(): void
    {
        $extension = uniqid();
        $mimeTypes = [uniqid() . '/' . uniqid(), uniqid() . '/' . uniqid()];

        $sut = $this->getSut(extension: $extension, mimeTypes: $mimeTypes);

        $this->assertSame($extension, $sut->getExtension());
        $this->assertSame($mimeTypes, $sut->getMimeTypes());
    }

    #[Test]
    public function preservesExtensionCase(): void
    {
        $extension = strtoupper(uniqid());

        $sut = $this->getSut(extension: $extension, mimeTypes: [uniqid()]);

        $this->assertSame($extension, $sut->getExtension());
    }

    /**
     * @param string[] $mimeTypes
     */
    private function getSut(string $extension, array $mimeTypes): FileFormatInterface
    {
        return new FileFormat($extension, $mimeTypes);
    }
}
