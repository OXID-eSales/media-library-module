<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Repository;

use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
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
            imageResource: $imageResource = $this->createStub(ImageResourceRefactoredInterface::class)
        );
        $thumbUrlExample = 'thumbUrlValue';
        $fileNameValue = 'filenameValue';
        $fileTypeValue = 'filetypeValue';

        $imageResource->method('calculateMediaThumbnailUrl')
            ->with($fileNameValue, $fileTypeValue)
            ->willReturn($thumbUrlExample);

        $data = [
            'OXID' => 'oxidValue',
            'OXSHOPID' => 2,
            'DDFILENAME' => $fileNameValue,
            'DDFILESIZE' => 123,
            'DDFILETYPE' => $fileTypeValue,
            'DDTHUMB' => '',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'OXTIMESTAMP' => '2023-10-30 12:53:10',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertSame('oxidValue', $result->getOxid());
        $this->assertSame($fileNameValue, $result->getFileName());
        $this->assertSame(123, $result->getFileSize());
        $this->assertSame($fileTypeValue, $result->getFileType());
        $this->assertSame($thumbUrlExample, $result->getThumbFileName());
        $this->assertSame('someFolderId', $result->getFolderId());

        $size = $result->getImageSize();
        $this->assertSame(100, $size->getWidth());
        $this->assertSame(200, $size->getHeight());
    }

    public function getSut(
        ImageResourceRefactoredInterface $imageResource = null
    ): MediaFactory {
        return new MediaFactory(
            imageResource: $imageResource ?? $this->createStub(ImageResourceRefactoredInterface::class)
        );
    }
}
