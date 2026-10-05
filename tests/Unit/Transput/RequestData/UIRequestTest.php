<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Transput\RequestData;

use OxidEsales\MediaLibrary\Transput\RequestData\UIRequest;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
use OxidEsales\MediaLibrary\Transput\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UIRequest::class)]
class UIRequestTest extends TestCase
{
    public function testIsPopup(): void
    {
        $requestExampleValue = (bool)rand(0, 1);

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getBoolRequestParameter')->willReturnMap([
            [UIRequest::REQUEST_PARAM_POPUP, $requestExampleValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );
        $this->assertSame($requestExampleValue, $sut->isPopout());
    }

    public function testGetMediaIds(): void
    {
        $requestExampleValue = [uniqid(), uniqid()];

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getArrayRequestParameter')->willReturnMap([
            [UIRequest::REQUEST_PARAM_MEDIA_IDS, $requestExampleValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );
        $this->assertSame($requestExampleValue, $sut->getMediaIds());
    }

    public function testGetFolderId(): void
    {
        $requestExampleValue = uniqid();

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getStringRequestParameter')->willReturnMap([
            [UIRequest::REQUEST_PARAM_FOLDER_ID, '', $requestExampleValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );
        $this->assertSame($requestExampleValue, $sut->getFolderId());
    }

    public function testGetTabName(): void
    {
        $requestExampleValue = uniqid();

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getStringRequestParameter')->willReturnMap([
            [UIRequest::REQUEST_PARAM_TAB, '', $requestExampleValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );
        $this->assertSame($requestExampleValue, $sut->getTabName());
    }

    public function testGetMediaListStartIndex(): void
    {
        $requestExampleValue = rand(0, 1000);

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getIntRequestParameter')->willReturnMap([
            [UIRequest::REQUEST_PARAM_MEDIA_LIST_START_INDEX, $requestExampleValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );
        $this->assertSame($requestExampleValue, $sut->getMediaListStartIndex());
    }

    public function testGetUploadedFile(): void
    {
        $fileName = uniqid();

        $_FILES['file'] = [
            'name' => $fileName
        ];

        $sut = $this->getSut();

        $uploadedFile = $sut->getUploadedFile();

        $this->assertEquals($fileName, $uploadedFile->getFileName());
    }

    private function getSut(
        ?RequestInterface $request = null,
    ): UIRequestInterface {
        $request ??= $this->createStub(RequestInterface::class);

        return new UIRequest(
            request: $request,
        );
    }
}
