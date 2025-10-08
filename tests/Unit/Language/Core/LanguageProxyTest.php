<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Language\Core;

use OxidEsales\Eshop\Core\Language as ShopLanguage;
use OxidEsales\MediaLibrary\Language\Core\LanguageProxy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LanguageProxy::class)]
class LanguageProxyTest extends TestCase
{
    public function testGetSeoReplaceChars(): void
    {
        $exampleTranslation = [
            'x' => 'y',
            'c' => 'b'
        ];

        /** @var ShopLanguage $shopLanguageMock */
        $shopLanguageMock = $this->createPartialMock(ShopLanguage::class, ['getSeoReplaceChars', 'getEditLanguage']);
        $shopLanguageMock->method('getEditLanguage')->willReturn(10);
        $shopLanguageMock->method('getSeoReplaceChars')->willReturnMap([
            [10, $exampleTranslation]
        ]);

        $sut = $this->getSut(shopLanguage: $shopLanguageMock);
        $this->assertSame($exampleTranslation, $sut->getSeoReplaceChars());
    }

    public function testGetLanguageArray(): void
    {
        $expected = [uniqid()];
        $shopLanguageMock = $this->createPartialMock(ShopLanguage::class, ['getLanguageArray']);
        $shopLanguageMock->expects($this->once())
            ->method('getLanguageArray')
            ->willReturn($expected);

        $sut = $this->getSut(shopLanguage: $shopLanguageMock);
        $this->assertSame($expected, $sut->getLanguageArray());
    }

    public function testGetBaseLanguageReturnsInt(): void
    {
        $shopLanguageMock = $this->createPartialMock(ShopLanguage::class, ['getBaseLanguage']);
        $shopLanguageMock->expects($this->once())
            ->method('getBaseLanguage')
            ->willReturn('0');

        $sut = $this->getSut(shopLanguage: $shopLanguageMock);
        $this->assertSame(0, $sut->getBaseLanguage());
    }

    public function testGetBaseLanguageWithIntInput(): void
    {
        $shopLanguageMock = $this->createPartialMock(ShopLanguage::class, ['getBaseLanguage']);
        $shopLanguageMock->expects($this->once())
            ->method('getBaseLanguage')
            ->willReturn(1);

        $sut = $this->getSut(shopLanguage: $shopLanguageMock);
        $this->assertSame(1, $sut->getBaseLanguage());
    }

    public function getSut(
        ShopLanguage $shopLanguage = null
    ): LanguageProxy {
        return new LanguageProxy(
            language: $shopLanguage ?? $this->createStub(ShopLanguage::class)
        );
    }
}
