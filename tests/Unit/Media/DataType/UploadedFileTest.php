<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\DataType;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFile;
use PHPUnit\Framework\TestCase;

class UploadedFileTest extends TestCase
{
    public function testRegularCaseWorks(): void
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

        $sut = new UploadedFile($fileExample);

        $this->assertSame($fileName, $sut->getFileName());
        $this->assertSame($fileType, $sut->getFileType());
        $this->assertSame($filePath, $sut->getPath());
        $this->assertFalse($sut->isError());
        $this->assertSame($fileSize, $sut->getSize());
    }

    public function testEmptyDataWorks(): void
    {
        $fileExample = [];

        $sut = new UploadedFile($fileExample);

        $this->assertSame('', $sut->getFileName());
        $this->assertSame('', $sut->getFileType());
        $this->assertSame('', $sut->getPath());
        $this->assertTrue($sut->isError());
        $this->assertSame(0, $sut->getSize());
    }
}
