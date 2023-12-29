<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Language\Core;

use OxidEsales\Eshop\Core\Language as ShopLanguage;
use OxidEsales\MediaLibrary\Language\Core\LanguageExtension;
use OxidEsales\MediaLibrary\Language\Core\LanguageProxy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Language\Core\LanguageProxy
 */
class LanguageProxyTest extends TestCase
{
    public function testGetLanguageStringsArray(): void
    {
        $exampleLanguageStrings = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        /** @var LanguageExtension&ShopLanguage $shopLanguageMock */
        $shopLanguageMock = $this->createPartialMock(LanguageExtension::class, ['getLanguageStrings']);
        $shopLanguageMock->method('getLanguageStrings')->willReturn($exampleLanguageStrings);

        $sut = $this->getSut(shopLanguage:  $shopLanguageMock);
        $this->assertSame($exampleLanguageStrings, $sut->getLanguageStringsArray());
    }

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

    public function getSut(
        ShopLanguage $shopLanguage = null
    ): LanguageProxy {
        return new LanguageProxy(
            language: $shopLanguage ?? $this->createStub(ShopLanguage::class)
        );
    }
}
