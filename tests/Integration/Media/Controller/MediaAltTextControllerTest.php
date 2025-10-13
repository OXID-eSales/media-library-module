<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Media\Controller;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Media\Controller\MediaAltTextController;
use OxidEsales\MediaLibrary\Media\DataType\MediaAltTextInterface;
use OxidEsales\MediaLibrary\Media\Factory\MediaAltTextFactoryInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaAltRepositoryInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaAltTextController::class)]

final class MediaAltTextControllerTest extends TestCase
{
    #[Test]
    public function getAltTexts(): void
    {
        $mediaAltRepositoryMock = $this->createMock(MediaAltRepositoryInterface::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $responseSpy = $this->createMock(ResponseInterface::class);

        $objectId = uniqid();
        $requestMock->method('get')->willReturnMap([
            ['objectId', $objectId],
        ]);
        $languageId1 = rand(1, 20);
        $languageId2 = rand(1, 20);
        $altText1 = uniqid();
        $altText2 = uniqid();
        $mediaAltRepositoryMock->method('getObjectAltTexts')->with($objectId)->willReturn([
            $this->createConfiguredStub(MediaAltTextInterface::class, [
                'getLanguageId' => $languageId1,
                'getText' => $altText1,
            ]),
            $this->createConfiguredStub(MediaAltTextInterface::class, [
                'getLanguageId' => $languageId2,
                'getText' => $altText2,
            ]),
        ]);
        $responseSpy->expects($this->once())
            ->method('responseAsJson')
            ->with([
                'success' => true,
                'altTexts' => [$languageId1 => $altText1, $languageId2 => $altText2],
            ]);

        $sut = $this->getSut(
            repository: $mediaAltRepositoryMock,
            request: $requestMock,
            response: $responseSpy
        );
        $sut->getAltTexts();
    }

    #[Test]
    public function saveAltText(): void
    {
        $mediaAltRepositoryMock = $this->createMock(MediaAltRepositoryInterface::class);
        $mediaAltTextFactoryStub = $this->createMock(MediaAltTextFactoryInterface::class);
        $requestStub = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $shopAdapterMock = $this->createMock(ShopAdapterInterface::class);
        $objectId = uniqid();
        $altText1 = uniqid();
        $altText2 = uniqid();
        $altTexts = [1 => $altText1, 2 => $altText2];

        $requestStub->method('get')->willReturnMap([
            ['objectId', $objectId],
            ['altTexts', $altTexts],
        ]);

        $mediaAltText1Stub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $objectId,
            'getLanguageId' => 1,
            'getText' => $altText1,
        ]);

        $mediaAltText2Stub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $objectId,
            'getLanguageId' => 2,
            'getText' => $altText2,
        ]);

        $mediaAltTextFactoryStub->method('create')->willReturnMap([
            [$objectId, 1, $altText1, $mediaAltText1Stub],
            [$objectId, 2, $altText2, $mediaAltText2Stub],
        ]);

        $mediaAltRepositoryMock->expects($this->exactly(2))
            ->method('saveAltText')
            ->willReturnCallback(
                function (MediaAltTextInterface $mediaAltText) use ($mediaAltText1Stub, $mediaAltText2Stub) {
                    $this->assertContains($mediaAltText, [$mediaAltText1Stub, $mediaAltText2Stub]);
                }
            );

        $successMsg = uniqid();
        $shopAdapterMock->method('translateString')
            ->with('DD_MEDIA_ALT_TEXT_SAVE_SUCCESS')
            ->willReturn($successMsg);
        $responseMock->expects($this->once())
            ->method('responseAsJson')
            ->with(['success' => true, 'message' => $successMsg]);

        $sut = $this->getSut(
            repository: $mediaAltRepositoryMock,
            factory: $mediaAltTextFactoryStub,
            request: $requestStub,
            response: $responseMock,
            shopAdapter: $shopAdapterMock
        );
        $sut->saveAltText();
    }

    private function getSut(
        $repository = null,
        $factory = null,
        $request = null,
        $response = null,
        $shopAdapter = null
    ): MediaAltTextController {
        return new MediaAltTextController(
            $repository ?? $this->createStub(MediaAltRepositoryInterface::class),
            $factory ?? $this->createStub(MediaAltTextFactoryInterface::class),
            $request ?? $this->createStub(RequestInterface::class),
            $response ?? $this->createStub(ResponseInterface::class),
            $shopAdapter ?? $this->createStub(ShopAdapterInterface::class)
        );
    }
}
