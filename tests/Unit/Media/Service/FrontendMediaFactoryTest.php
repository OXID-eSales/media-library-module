<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\FrontendMedia;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Service\FrontendMediaFactory;
use OxidEsales\MediaLibrary\Media\Service\FrontendMediaFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FrontendMediaFactory::class)]
class FrontendMediaFactoryTest extends TestCase
{
    #[Test]
    public function createFromMedia(): void
    {
        $thumbnailService = $this->createMock(ThumbnailServiceInterface::class);
        $sut = $this->getSut(
            thumbnailService: $thumbnailService,
        );

        $mediaStub = $this->createStub(MediaInterface::class);
        $mediaStub->method('getOxid')->willReturn($id = uniqid());
        $mediaStub->method('getFolderName')->willReturn($folderName = uniqid());
        $mediaStub->method('getFileName')->willReturn($fileName = uniqid());
        $mediaStub->method('getFileType')->willReturn($fileType = uniqid());
        $mediaStub->method('getFileSize')->willReturn($fileSize = 123);
        $mediaStub->method('getImageSize')->willReturn(new ImageSize(width: 100, height: 100));

        $thumbnailService->method('ensureAndGetThumbnailUrl')
            ->with($folderName, $fileName)
            ->willReturn($thumbUrl = 'someThumbUrl');

        $expected = new FrontendMedia(
            id: $id,
            file: $fileName,
            filetype: $fileType,
            filesize: $fileSize,
            thumb: $thumbUrl,
            imageSize: '100x100'
        );

        $this->assertEquals($expected, $sut->createFromMedia($mediaStub));
    }

    #[Test]
    public function thumbnailEmptyForFolder(): void
    {
        $thumbnailServiceSpy = $this->createMock(ThumbnailServiceInterface::class);
        $sut = $this->getSut(
            thumbnailService: $thumbnailServiceSpy,
        );

        $mediaStub = $this->createStub(MediaInterface::class);
        $mediaStub->method('getOxid')->willReturn($id = uniqid());
        $mediaStub->method('getFileName')->willReturn($fileName = uniqid());
        $mediaStub->method('getFileType')->willReturn($fileType = uniqid());
        $mediaStub->method('getFileSize')->willReturn($fileSize = 123);
        $mediaStub->method('getImageSize')->willReturn(new ImageSize(width: 100, height: 100));
        $mediaStub->method('isDirectory')->willReturn(true);

        $thumbnailServiceSpy->expects($this->never())->method('ensureAndGetThumbnailUrl');

        $expected = new FrontendMedia(
            id: $id,
            file: $fileName,
            filetype: $fileType,
            filesize: $fileSize,
            thumb: '',
            imageSize: '100x100'
        );

        $this->assertEquals($expected, $sut->createFromMedia($mediaStub));
    }

    private function getSut(
        ?ThumbnailServiceInterface $thumbnailService = null,
    ): FrontendMediaFactoryInterface {
        $thumbnailService ??= $this->createStub(ThumbnailServiceInterface::class);

        return new FrontendMediaFactory(
            thumbnailService: $thumbnailService,
        );
    }
}
