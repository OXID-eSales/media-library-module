<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Language\Core;

use OxidEsales\Eshop\Core\Language as ShopLanguage;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use OxidEsales\MediaLibrary\Language\Core\LanguageProxy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LanguageProxy::class)]
class LanguageProxyTest extends TestCase
{
    #[Test]
    public function getSeoReplaceChars(): void
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

    #[Test]
    public function getLanguageArray(): void
    {
        $expected = [uniqid()];
        $shopLanguageMock = $this->createPartialMock(ShopLanguage::class, ['getLanguageArray']);
        $shopLanguageMock->expects($this->once())
            ->method('getLanguageArray')
            ->willReturn($expected);

        $sut = $this->getSut(shopLanguage: $shopLanguageMock);
        $this->assertSame($expected, $sut->getLanguageArray());
    }

    #[Test]
    public function getBaseLanguageWithString(): void
    {
        $languageId = rand(0, 10);
        $shopLanguageMock = $this->createPartialMock(ShopLanguage::class, ['getBaseLanguage']);
        $shopLanguageMock->expects($this->once())
            ->method('getBaseLanguage')
            ->willReturn((string)$languageId);

        $sut = $this->getSut(shopLanguage: $shopLanguageMock);
        $this->assertSame($languageId, $sut->getBaseLanguage());
    }

    #[Test]
    public function getBaseLanguageWithInt(): void
    {
        $shopLanguageMock = $this->createPartialMock(ShopLanguage::class, ['getBaseLanguage']);
        $shopLanguageMock->expects($this->once())
            ->method('getBaseLanguage')
            ->willReturn($languageId = rand(0, 10));

        $sut = $this->getSut(shopLanguage: $shopLanguageMock);
        $this->assertSame($languageId, $sut->getBaseLanguage());
    }

    private function getSut(
        ShopLanguage $shopLanguage = null
    ): LanguageInterface {
        return new LanguageProxy(
            language: $shopLanguage ?? $this->createStub(ShopLanguage::class)
        );
    }
}
