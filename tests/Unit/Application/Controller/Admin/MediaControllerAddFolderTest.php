<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Application\Controller\Admin;

use OxidEsales\MediaLibrary\Application\Controller\Admin\MediaController;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Service\FolderServiceInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequestInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Service\DirectoryNameValidatorChainInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Application\Controller\Admin\MediaController
 */
class MediaControllerAddFolderTest extends TestCase
{
    public function testAddFolderSuccess(): void
    {
        $folderName = uniqid();

        $requestStub = $this->createConfiguredStub(AddFolderRequestInterface::class, [
            'getName' => $folderName
        ]);

        $newMediaItem = new MediaDataType(
            oxid: 'fid',
            fileName: 'someDirName',
            fileType: 'directory'
        );

        $folderServiceMock = $this->createMock(FolderServiceInterface::class);
        $folderServiceMock->method('createCustomDir')->with($folderName)->willReturn($newMediaItem);

        $responseSpy = $this->createMock(ResponseInterface::class);
        $responseSpy->expects($this->once())->method('responseAsJson')->with([
            'id' => 'fid',
            'name' => 'someDirName'
        ]);

        $validatorSpy = $this->createMock(DirectoryNameValidatorChainInterface::class);
        $validatorSpy->expects($this->once())->method('validateDocumentName')->with($folderName);

        $sut = $this->getSut(
            response: $responseSpy,
            directoryNameValidatorChain: $validatorSpy,
            addFolderRequest: $requestStub,
            folderService: $folderServiceMock,
        );

        $sut->addFolder();
    }

    public function testValidationExceptionTriggersErrorResponseDuringAddFolder(): void
    {
        $validationMock = $this->createMock(DirectoryNameValidatorChainInterface::class);
        $sut = $this->getSut(
            response: $responseSpy = $this->createMock(ResponseInterface::class),
            directoryNameValidatorChain: $validationMock,
        );

        $exception = new ValidationFailedException($exceptionMessage = uniqid());
        $validationMock->method('validateDocumentName')->willThrowException($exception);

        $responseSpy->expects($this->once())
            ->method('errorResponseAsJson')
            ->with(400, $exceptionMessage, ['error' => $exceptionMessage]);

        $sut->addFolder();
    }

    private function getSut(
        ResponseInterface $response = null,
        DirectoryNameValidatorChainInterface $directoryNameValidatorChain = null,
        AddFolderRequestInterface $addFolderRequest = null,
        FolderServiceInterface $folderService = null,
    ): MediaController {
        $sut = $this->createPartialMock(MediaController::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [ResponseInterface::class, $response ?? $this->createStub(ResponseInterface::class)],
            [
                DirectoryNameValidatorChainInterface::class,
                $directoryNameValidatorChain ?? $this->createStub(DirectoryNameValidatorChainInterface::class)
            ],
            [
                AddFolderRequestInterface::class,
                $addFolderRequest ?? $this->createStub(AddFolderRequestInterface::class)
            ],
            [FolderServiceInterface::class, $folderService ?? $this->createStub(FolderServiceInterface::class)],
        ]);

        return $sut;
    }
}
