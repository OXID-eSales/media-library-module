<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface as ShopRequestInterface;
use OxidEsales\MediaLibrary\Transput\Request;
use OxidEsales\MediaLibrary\Transput\RequestInterface;
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

        $requestMock = $this->createMock(ShopRequestInterface::class);
        $requestMock->method('get')->willReturnMap([
            [$paramName, $defaultValue, $requestValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );

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

    #[DataProvider('requestArrayDataProvider')]
    public function testGetArrayRequestParameter(mixed $requestValue, array $expectedValue): void
    {
        $paramName = uniqid();

        $requestMock = $this->createMock(ShopRequestInterface::class);
        $requestMock->method('get')->willReturnMap([
            [$paramName, null, $requestValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );

        $this->assertSame($expectedValue, $sut->getArrayRequestParameter($paramName));
    }

    public static function requestArrayDataProvider(): array
    {
        return [
            'missing parameter' => [null, []],
            'scalar instead of an array' => ['single', []],
            'list of ids' => [['first', 'second'], ['first', 'second']],
            'numeric values become strings' => [[1, 2], ['1', '2']],
            'keys are discarded' => [['a' => 'first', 'b' => 'second'], ['first', 'second']],
        ];
    }

    #[DataProvider('requestBoolDataProvider')]
    public function testGetBoolRequestParameter($requestValue, $expectedValue): void
    {
        $paramName = uniqid();

        $requestMock = $this->createMock(ShopRequestInterface::class);
        $requestMock->method('get')->willReturnMap([
            [$paramName, null, $requestValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );
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

        $requestMock = $this->createMock(ShopRequestInterface::class);
        $requestMock->method('get')->willReturnMap([
            [$paramName, null, $requestValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );
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

    private function getSut(
        ?ShopRequestInterface $request = null,
    ): RequestInterface {
        $request ??= $this->createStub(ShopRequestInterface::class);

        return new Request(
            request: $request,
        );
    }
}
