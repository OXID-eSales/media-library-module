<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Transput;

use OxidEsales\Eshop\Core\Request as ShopRequest;
use OxidEsales\MediaLibrary\Transput\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Request::class)]
class RequestTest extends TestCase
{
    #[DataProvider('requestOnlyStringDataProvider')]
    public function testGetStringRequestParameter(
        mixed $requestValue,
        ?string $defaultValue,
        string $expectedValue
    ): void {
        $paramName = uniqid();

        $requestMock = $this->createPartialMock(ShopRequest::class, ['getRequestEscapedParameter']);
        $requestMock->method('getRequestEscapedParameter')->willReturnMap([
            [$paramName, $defaultValue, $requestValue]
        ]);

        $sut = new Request($requestMock);

        if ($defaultValue) {
            $this->assertSame($expectedValue, $sut->getStringRequestParameter($paramName, $defaultValue));
        } else {
            $this->assertSame($expectedValue, $sut->getStringRequestParameter($paramName));
        }
    }

    public static function requestOnlyStringDataProvider(): array
    {
        $defaultValue = 'someDefault';
        return [
            [null, null, ''],
            [null, $defaultValue, $defaultValue],
            [0, null, ''],
            [1, $defaultValue, $defaultValue],
            [['someArray'], null, ''],
            ['random', $defaultValue, 'random'],
        ];
    }

    #[DataProvider('requestBoolDataProvider')]
    public function testGetBoolRequestParameter($requestValue, $expectedValue): void
    {
        $paramName = uniqid();

        $requestMock = $this->createPartialMock(ShopRequest::class, ['getRequestParameter']);
        $requestMock->method('getRequestParameter')->willReturnMap([
            [$paramName, null, $requestValue]
        ]);

        $sut = new Request($requestMock);
        $this->assertSame($expectedValue, $sut->getBoolRequestParameter($paramName));
    }

    public static function requestBoolDataProvider(): array
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

    #[DataProvider('getIntDataProvider')]
    public function testGetIntRequestParameter($requestValue, $expectedValue): void
    {
        $paramName = uniqid();

        $requestMock = $this->createPartialMock(ShopRequest::class, ['getRequestParameter']);
        $requestMock->method('getRequestParameter')->willReturnMap([
            [$paramName, null, $requestValue]
        ]);

        $sut = new Request($requestMock);
        $this->assertSame($expectedValue, $sut->getIntRequestParameter($paramName));
    }

    public static function getIntDataProvider(): array
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
