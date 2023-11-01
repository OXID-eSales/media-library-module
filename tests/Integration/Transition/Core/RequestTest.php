<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Transition\Core;

use OxidEsales\Eshop\Core\Request as ShopRequest;
use OxidEsales\MediaLibrary\Transition\Core\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /**
     * @dataProvider requestBoolDataProvider
     */
    public function testIsOverlay($requestValue, $expectedValue): void
    {
        $requestMock = $this->createPartialMock(ShopRequest::class, ['getRequestParameter']);
        $requestMock->method('getRequestParameter')->willReturnMap([
            [Request::REQUEST_PARAM_OVERLAY, false, $requestValue]
        ]);

        $sut = new Request($requestMock);
        $this->assertSame($expectedValue, $sut->isOverlay());
    }

    /**
     * @dataProvider requestBoolDataProvider
     */
    public function testIsPopup($requestValue, $expectedValue): void
    {
        $requestMock = $this->createPartialMock(ShopRequest::class, ['getRequestParameter']);
        $requestMock->method('getRequestParameter')->willReturnMap([
            [Request::REQUEST_PARAM_POPUP, false, $requestValue]
        ]);

        $sut = new Request($requestMock);
        $this->assertSame($expectedValue, $sut->isPopout());
    }

    public function requestBoolDataProvider(): array
    {
        return [
            [null, false],
            [0, false],
            [1, true],
            ['random', true],
            [true, true],
            [false, false]
        ];
    }

    /**
     * @dataProvider requestOnlyStringDataProvider
     */
    public function testGetFolderId($requestValue, $expectedValue): void
    {
        $requestMock = $this->createPartialMock(ShopRequest::class, ['getRequestEscapedParameter']);
        $requestMock->method('getRequestEscapedParameter')->willReturnMap([
            [Request::REQUEST_PARAM_FOLDER_ID, null, $requestValue]
        ]);

        $sut = new Request($requestMock);
        $this->assertSame($expectedValue, $sut->getFolderId());
    }

    public function requestOnlyStringDataProvider(): array
    {
        return [
            [null, ''],
            [0, ''],
            [1, ''],
            [['someArray'], ''],
            ['random', 'random'],
        ];
    }

    /**
     * @dataProvider getMediaListStartIndexDataProvider
     */
    public function testGetMediaListStartIndex($requestValue, $expectedValue): void
    {
        $requestMock = $this->createPartialMock(ShopRequest::class, ['getRequestParameter']);
        $requestMock->method('getRequestParameter')->willReturnMap([
            [Request::REQUEST_PARAM_MEDIA_LIST_START_INDEX, null, $requestValue]
        ]);

        $sut = new Request($requestMock);
        $this->assertSame($expectedValue, $sut->getMediaListStartIndex());
    }

    public function getMediaListStartIndexDataProvider(): array
    {
        return [
            'null' => [null, 0],
            'empty string' => ['', 0],
            'one string' => ['1', 1],
            'one as int' => [1, 1],
            'ten as int' => [10, 10],
            'string with 10 as start' => ['10something', 10],
            'string with 10 inside' => ['some10xx', 0]
        ];
    }
}
