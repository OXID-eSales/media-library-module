<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResource;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaObjectResourceTest extends TestCase
{
    #[Test]
    public function getUrlToMediaCorrectlyGetsDataFromMediaResource()
    {
        $mediaStub = $this->createConfiguredStub(MediaInterface::class, [
            'getFileName' => $fileName = uniqid(),
            'getFolderName' => $folderName = uniqid(),
        ]);

        $mediaResourceMock = $this->createMock(MediaResourceInterface::class);
        $mediaResourceMock->method('getUrlToMediaFile')
            ->with($folderName, $fileName)
            ->willReturn($mediaFileUrl = uniqid());

        $sut = $this->getSut(
            mediaResource: $mediaResourceMock,
        );

        $result = $sut->getUrlToMedia($mediaStub);
        $this->assertSame($mediaFileUrl, $result);
    }

    #[Test]
    public function getPathToMediaCorrectlyGetsDataFromMediaResource()
    {
        $mediaStub = $this->createConfiguredStub(MediaInterface::class, [
            'getFileName' => $fileName = uniqid(),
            'getFolderName' => $folderName = uniqid(),
        ]);

        $mediaResourceMock = $this->createMock(MediaResourceInterface::class);
        $mediaResourceMock->method('getPathToMediaFile')
            ->with($folderName, $fileName)
            ->willReturn($mediaFilePath = uniqid());

        $sut = $this->getSut(
            mediaResource: $mediaResourceMock,
        );

        $result = $sut->getPathToMedia($mediaStub);
        $this->assertSame($mediaFilePath, $result);
    }

    protected function getSut(
        MediaResourceInterface $mediaResource = null,
    ): MediaObjectResourceInterface {
        return new MediaObjectResource(
            mediaResource: $mediaResource,
        );
    }
}
