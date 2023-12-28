<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Repository;

use OxidEsales\MediaLibrary\Media\Repository\MediaFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Media\Repository\MediaFactory
 */
class MediaFactoryTest extends TestCase
{
    public function testFromDatabaseArray(): void
    {
        $sut = new MediaFactory();

        $data = [
            'OXID' => 'oxidValue',
            'OXSHOPID' => 2,
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDTHUMB' => 'thumbValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'OXTIMESTAMP' => '2023-10-30 12:53:10',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertSame('oxidValue', $result->getOxid());
        $this->assertSame('filenameValue', $result->getFileName());
        $this->assertSame(123, $result->getFileSize());
        $this->assertSame('filetypeValue', $result->getFileType());
        $this->assertSame('thumbValue', $result->getThumbFileName());
        $this->assertSame('someFolderId', $result->getFolderId());

        $size = $result->getImageSize();
        $this->assertSame(100, $size->getWidth());
        $this->assertSame(200, $size->getHeight());
    }
}
