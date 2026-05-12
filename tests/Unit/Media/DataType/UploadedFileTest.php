<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\DataType;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFile;
use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UploadedFile::class)]
class UploadedFileTest extends TestCase
{
    #[Test]
    public function regularCaseWorks(): void
    {
        $fileName = uniqid();
        $fileType = uniqid();
        $filePath = uniqid();
        $fileSize = rand(100, 10000);

        $fileExample = [
            'name' => $fileName,
            'type' => $fileType,
            'tmp_name' => $filePath,
            'error' => UPLOAD_ERR_OK,
            'size' => $fileSize,
        ];

        $sut = $this->getSut(fileData: $fileExample);

        $this->assertSame($fileName, $sut->getFileName());
        $this->assertSame($fileType, $sut->getFileType());
        $this->assertSame($filePath, $sut->getPath());
        $this->assertFalse($sut->isError());
        $this->assertSame($fileSize, $sut->getSize());
    }

    #[Test]
    public function emptyDataWorks(): void
    {
        $sut = $this->getSut(fileData: []);

        $this->assertSame('', $sut->getFileName());
        $this->assertSame('', $sut->getFileType());
        $this->assertSame('', $sut->getPath());
        $this->assertTrue($sut->isError());
        $this->assertSame(0, $sut->getSize());
        $this->assertSame('', $sut->getExtension());
    }

    #[Test]
    public function getExtensionPreservesCase(): void
    {
        $extension = strtoupper(uniqid());

        $sut = $this->getSut(fileData: ['name' => uniqid() . '.' . $extension]);

        $this->assertSame($extension, $sut->getExtension());
    }

    /**
     * @param array<string, mixed> $fileData
     */
    private function getSut(array $fileData = []): UploadedFileInterface
    {
        return new UploadedFile($fileData);
    }
}
