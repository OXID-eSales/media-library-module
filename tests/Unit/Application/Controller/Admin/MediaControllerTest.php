<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Application\Controller\Admin;

use OxidEsales\MediaLibrary\Application\Controller\Admin\MediaController;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Service\FolderServiceInterface;
use OxidEsales\MediaLibrary\Service\Media;
use OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequestInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Application\Controller\Admin\MediaController
 */
class MediaControllerTest extends TestCase
{
    public function testAddFolderSuccess(): void
    {
        $folderName = uniqid();

        $requestStub = $this->createStub(AddFolderRequestInterface::class);
        $requestStub->method('getName')->willReturn($folderName);

        $newMediaItem = new MediaDataType(
            oxid: 'fid',
            fileName: 'someDirName',
            fileType: 'directory'
        );

        $folderServiceStub = $this->createStub(FolderServiceInterface::class);
        $folderServiceStub->method('createCustomDir')->with($folderName)->willReturn($newMediaItem);

        $responseSpy = $this->createMock(ResponseInterface::class);
        $responseSpy->expects($this->once())->method('responseAsJson')->with([
            'id' => 'fid',
            'name' => 'someDirName'
        ]);

        $sut = $this->createPartialMock(MediaController::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [AddFolderRequestInterface::class, $requestStub],
            [FolderServiceInterface::class, $folderServiceStub],
            [ResponseInterface::class, $responseSpy],
        ]);

        $sut->addFolder();
    }
}
