<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Application\Controller\Admin;

use OxidEsales\MediaLibrary\Application\Controller\Admin\MediaController;
use OxidEsales\MediaLibrary\Media\Exception\MediaDeletionErrorException;
use OxidEsales\MediaLibrary\Media\Service\MediaServiceInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaController::class)]
class MediaControllerRemoveTest extends TestCase
{
    #[Test]
    public function removeDeletesTheRequestedMediaAndReportsSuccess(): void
    {
        $mediaIds = [uniqid(), uniqid()];

        $mediaServiceSpy = $this->createMock(MediaServiceInterface::class);
        $mediaServiceSpy->expects($this->once())->method('delete')->with($mediaIds);

        $responseSpy = $this->createMock(ResponseInterface::class);
        $responseSpy->expects($this->once())
            ->method('responseAsJson')
            ->with(['success' => true, 'msg' => '']);

        $this->getSut(mediaIds: $mediaIds, mediaService: $mediaServiceSpy, response: $responseSpy)->remove();
    }

    #[Test]
    public function removeReportsTheMessageIdentOfARefusedDeletion(): void
    {
        $messageIdent = uniqid();

        $mediaService = $this->createMock(MediaServiceInterface::class);
        $mediaService->method('delete')
            ->willThrowException(new MediaDeletionErrorException($messageIdent));

        $responseSpy = $this->createMock(ResponseInterface::class);
        $responseSpy->expects($this->once())
            ->method('responseAsJson')
            ->with(['success' => false, 'msg' => $messageIdent]);

        $this->getSut(mediaIds: [uniqid()], mediaService: $mediaService, response: $responseSpy)->remove();
    }

    #[Test]
    public function removeReportsTheGenericErrorWhenNoMediaWasRequested(): void
    {
        $mediaServiceSpy = $this->createMock(MediaServiceInterface::class);
        $mediaServiceSpy->expects($this->never())->method('delete');

        $responseSpy = $this->createMock(ResponseInterface::class);
        $responseSpy->expects($this->once())
            ->method('responseAsJson')
            ->with(['success' => false, 'msg' => 'DD_MEDIA_REMOVE_ERR']);

        $this->getSut(mediaIds: [], mediaService: $mediaServiceSpy, response: $responseSpy)->remove();
    }

    /**
     * @param string[] $mediaIds
     */
    private function getSut(
        array $mediaIds = [],
        ?MediaServiceInterface $mediaService = null,
        ?ResponseInterface $response = null,
    ): MediaController {
        $sut = $this->createPartialMock(MediaController::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [MediaServiceInterface::class, $mediaService ?? $this->createStub(MediaServiceInterface::class)],
            [ResponseInterface::class, $response ?? $this->createStub(ResponseInterface::class)],
            [
                UIRequestInterface::class,
                $this->createConfiguredStub(UIRequestInterface::class, ['getMediaIds' => $mediaIds])
            ],
        ]);

        return $sut;
    }
}
