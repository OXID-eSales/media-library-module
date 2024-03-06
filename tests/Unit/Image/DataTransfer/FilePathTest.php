<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\DataTransfer;

use OxidEsales\MediaLibrary\Media\DataType\FilePath;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Media\DataType\FilePath
 */
class FilePathTest extends TestCase
{
    public function testGetPath(): void
    {
        $exampleFileName = uniqid();
        $examplePath = uniqid() . '/' . $exampleFileName;

        $sut = new FilePath($examplePath);
        $this->assertSame($examplePath, $sut->getPath());
    }

    public function testGetFileName(): void
    {
        $exampleFileName = uniqid();
        $examplePath = uniqid() . '/' . $exampleFileName;

        $sut = new FilePath($examplePath);
        $this->assertSame($exampleFileName, $sut->getFileName());
    }
}
