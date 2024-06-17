<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Application\Controller\Admin;

use OxidEsales\MediaLibrary\Application\Controller\Admin\MediaController;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaServiceInterface;
use OxidEsales\MediaLibrary\Service\FolderServiceInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequestInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChainInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Application\Controller\Admin\MediaController
 */
class MediaControllerTest extends TestCase
{
    public function testUploadServiceTriggeredAndResponseBuiltCorrectly(): void
    {
        $sut = $this->getSut(
            response: $responseSpy = $this->createMock(ResponseInterface::class),
            uiRequest: $requestStub = $this->createMock(UIRequestInterface::class),
            thumbnailService: $thumbnailServiceMock = $this->createMock(ThumbnailServiceInterface::class),
            mediaService: $mediaServiceSpy = $this->createMock(MediaServiceInterface::class)
        );

        $requestStub->method('getFolderId')->willReturn($folderId = uniqid());
        $requestStub->method('getUploadedFile')->willReturn(
            $this->createConfiguredStub(UploadedFileInterface::class, [
                'getFileName' => $fileName = uniqid(),
                'getFileType' => $fileType = uniqid(),
                'getFilePath' => $filePath = uniqid(),
                'getSize' => $fileSize = rand(10, 10000),
            ])
        );

        $imageSizeMock = $this->createMock(ImageSizeInterface::class);
        $imageSizeMock->method('getInFormat')->with('%dx%d', '')->willReturn($uploadSize = uniqid());

        $mediaServiceSpy->method('upload')
            ->with($filePath, $folderId, $fileName)
            ->willReturn(
                $this->createConfiguredStub(MediaInterface::class, [
                    'getOxid' => $uploadOxid = uniqid(),
                    'getFileName' => $uploadFileName = uniqid(),
                    'getImageSize' => $imageSizeMock,
                    'getFolderName' => $uploadFolderName = uniqid(),
                ])
            );

        $thumbnailServiceMock->method('ensureAndGetThumbnailUrl')
            ->with($uploadFolderName, $uploadFileName)
            ->willReturn($thumbnailUrl = uniqid());

        $responseSpy->expects($this->once())->method('responseAsJson')
            ->with([
                'success' => true,
                'id' => $uploadOxid,
                'file' => $uploadFileName,
                'filetype' => $fileType,
                'filesize' => $fileSize,
                'imagesize' => $uploadSize,
                'thumb' => $thumbnailUrl
            ]);

        $sut->upload();
    }

    public function testValidationExceptionTriggersErrorResponse(): void
    {
        $sut = $this->getSut(
            response: $responseSpy = $this->createMock(ResponseInterface::class),
            validatorChain: $validationMock = $this->createMock(UploadedFileValidatorChainInterface::class),
        );

        $exception = new ValidationFailedException($exceptionMessage = uniqid());
        $validationMock->method('validateFile')->willThrowException($exception);

        $responseSpy->expects($this->once())
            ->method('errorResponseAsJson')
            ->with(415, $exceptionMessage, ['error' => $exceptionMessage]);

        $sut->upload();
    }

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

    private function getSut(
        ResponseInterface $response = null,
        UploadedFileValidatorChainInterface $validatorChain = null,
        UIRequestInterface $uiRequest = null,
        ThumbnailServiceInterface $thumbnailService = null,
        MediaServiceInterface $mediaService = null,
    ): MediaController {
        $sut = $this->createPartialMock(MediaController::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [ResponseInterface::class, $response ?? $this->createStub(ResponseInterface::class)],
            [
                UploadedFileValidatorChainInterface::class,
                $validatorChain ?? $this->createStub(UploadedFileValidatorChainInterface::class)
            ],
            [UIRequestInterface::class, $uiRequest ?? $this->createStub(UIRequestInterface::class)],
            [
                ThumbnailServiceInterface::class,
                $thumbnailService ?? $this->createStub(ThumbnailServiceInterface::class)
            ],
            [MediaServiceInterface::class, $mediaService ?? $this->createStub(MediaServiceInterface::class)],
        ]);

        return $sut;
    }
}
