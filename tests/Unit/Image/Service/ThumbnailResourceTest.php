<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Service;

use OxidEsales\MediaLibrary\Image\Service\ThumbnailResource;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ThumbnailResource::class)]
class ThumbnailResourceTest extends TestCase
{
    private function getSut(
        MediaResourceInterface $imageResource = null,
    ) {
        return new ThumbnailResource(
            mediaResource: $imageResource ?: $this->createStub(MediaResourceInterface::class),
        );
    }

    #[Test]
    public function getDefaultThumbnailSize(): void
    {
        $sut = $this->getSut();

        $size = $sut->getDefaultThumbnailSize();

        $this->assertSame($sut::THUMBNAIL_DEFAULT_SIZE, $size->getWidth());
        $this->assertSame($sut::THUMBNAIL_DEFAULT_SIZE, $size->getHeight());
    }

    public static function getPathToThumbnailFilesDataProvider(): \Generator
    {
        $folder = uniqid();
        yield 'specific folder' => [
            'folder' => $folder,
            'expectedResult' => 'somePathToMediaFiles/' . $folder . '/thumbs'
        ];
    }

    #[Test]
    public function getPathToThumbnailFilesNoFolder(): void
    {
        $mediaFilesPath = 'somePathToMediaFiles';

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createMock(MediaResourceInterface::class)
        );
        $imageResource->method('getPathToMediaFiles')->with('')->willReturn($mediaFilesPath);

        $this->assertSame($mediaFilesPath . '/thumbs', $sut->getPathToThumbnailFiles());
    }

    #[Test]
    public function getPathToThumbnailFilesWithFolder(): void
    {
        $mediaFilesPath = 'somePathToMediaFilesWithFolder';
        $folder = uniqid();

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createMock(MediaResourceInterface::class)
        );
        $imageResource->method('getPathToMediaFiles')->with($folder)->willReturn($mediaFilesPath);

        $this->assertSame($mediaFilesPath . '/thumbs', $sut->getPathToThumbnailFiles($folder));
    }

    #[Test]
    public function getUrlToThumbnailFilesNoFolder(): void
    {
        $mediaFilesUrl = 'someUrlToMediaFiles';

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createMock(MediaResourceInterface::class)
        );
        $imageResource->method('getUrlToMediaFiles')->with($this->isEmpty())->willReturn($mediaFilesUrl);

        $this->assertSame($mediaFilesUrl . '/thumbs', $sut->getUrlToThumbnailFiles());
    }

    #[Test]
    public function getUrlToThumbnailFilesWithFolder(): void
    {
        $mediaFilesUrlWithFolder = 'someUrlToMediaFiles';
        $folder = uniqid();

        $sut = $this->getSut(
            imageResource: $imageResourceMock = $this->createMock(MediaResourceInterface::class)
        );
        $imageResourceMock->method('getUrlToMediaFiles')->with($folder)->willReturn($mediaFilesUrlWithFolder);

        $this->assertSame($mediaFilesUrlWithFolder . '/thumbs', $sut->getUrlToThumbnailFiles($folder));
    }

    #[Test]
    public function getPathToThumbnailFile(): void
    {
        $thumbFilesPath = 'somePathToThumbnailFile';
        $fileName = uniqid();
        $folder = uniqid();

        $sut = $this->createPartialMock(ThumbnailResource::class, ['getPathToThumbnailFiles']);
        $sut->method('getPathToThumbnailFiles')->with($folder)->willReturn($thumbFilesPath);

        $this->assertSame($thumbFilesPath . '/' . $fileName, $sut->getPathToThumbnailFile($fileName, $folder));
    }

    #[Test]
    public function getPathToThumbnailFileWithoutFolder(): void
    {
        $thumbFilesPath = 'somePathToThumbnailFile';
        $fileName = uniqid();

        $sut = $this->createPartialMock(ThumbnailResource::class, ['getPathToThumbnailFiles']);
        $sut->method('getPathToThumbnailFiles')->with('')->willReturn($thumbFilesPath);

        $this->assertSame($thumbFilesPath . '/' . $fileName, $sut->getPathToThumbnailFile($fileName));
    }

    #[Test]
    public function getUrlToThumbnailFile(): void
    {
        $mediaFilesUrlWithoutFolder = 'someUrlToThumbnailFiles';
        $fileName = uniqid();
        $folder = uniqid();

        $sut = $this->createPartialMock(ThumbnailResource::class, ['getUrlToThumbnailFiles']);
        $sut->method('getUrlToThumbnailFiles')->with($folder)->willReturn($mediaFilesUrlWithoutFolder);

        $this->assertSame(
            $mediaFilesUrlWithoutFolder . '/' . $fileName,
            $sut->getUrlToThumbnailFile($fileName, $folder)
        );
    }

    #[Test]
    public function getUrlToThumbnailFileWithoutFolder(): void
    {
        $mediaFilesUrlWithoutFolder = 'someUrlToThumbnailFiles';
        $thumbnailFileName = uniqid();

        $sut = $this->createPartialMock(ThumbnailResource::class, ['getUrlToThumbnailFiles']);
        $sut->method('getUrlToThumbnailFiles')->with('')->willReturn($mediaFilesUrlWithoutFolder);

        $this->assertSame(
            $mediaFilesUrlWithoutFolder . '/' . $thumbnailFileName,
            $sut->getUrlToThumbnailFile($thumbnailFileName)
        );
    }
}
