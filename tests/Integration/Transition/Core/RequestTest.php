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
}
