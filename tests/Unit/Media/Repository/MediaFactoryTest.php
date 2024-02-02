<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Repository;

use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Media\Repository\MediaFactory
 */
class MediaFactoryTest extends TestCase
{
    public function testFromDatabaseArray(): void
    {
        $sut = $this->getSut(
            thumbnailResource: $thumbnailResource = $this->createStub(ThumbnailResourceInterface::class)
        );
        $thumbUrlExample = 'thumbUrlValue';
        $fileNameValue = 'filenameValue';
        $fileTypeValue = 'filetypeValue';

        $thumbnailResource->method('calculateMediaThumbnailUrl')
            ->with($fileNameValue, $fileTypeValue)
            ->willReturn($thumbUrlExample);

        $data = [
            'OXID' => 'oxidValue',
            'OXSHOPID' => 2,
            'DDFILENAME' => $fileNameValue,
            'DDFILESIZE' => 123,
            'DDFILETYPE' => $fileTypeValue,
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'OXTIMESTAMP' => '2023-10-30 12:53:10',
            'FOLDERNAME' => 'someFolderName',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertSame('oxidValue', $result->getOxid());
        $this->assertSame($fileNameValue, $result->getFileName());
        $this->assertSame(123, $result->getFileSize());
        $this->assertSame($fileTypeValue, $result->getFileType());
        $this->assertSame($thumbUrlExample, $result->getThumbFileName());
        $this->assertSame('someFolderId', $result->getFolderId());
        $this->assertSame('someFolderName', $result->getFolderName());

        $size = $result->getImageSize();
        $this->assertSame(100, $size->getWidth());
        $this->assertSame(200, $size->getHeight());
    }

    public function getSut(
        ThumbnailResourceInterface $thumbnailResource = null
    ): MediaFactory {
        return new MediaFactory(
            thumbnailResource: $thumbnailResource ?? $this->createStub(ThumbnailResourceInterface::class)
        );
    }
}
