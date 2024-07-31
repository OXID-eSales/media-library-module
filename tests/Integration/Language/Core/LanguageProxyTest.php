<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Language\Core;

use OxidEsales\Eshop\Core\Language as ShopLanguage;
use OxidEsales\MediaLibrary\Language\Core\LanguageExtension;
use OxidEsales\MediaLibrary\Language\Core\LanguageProxy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LanguageExtension::class)]
class LanguageProxyTest extends TestCase
{
    public function testGetLanguageStrings(): void
    {
        $languageStringsList = ['somekey' => 'someValue'];

        /** @var ShopLanguage $languageMock */
        $languageMock = $this->createPartialMock(LanguageExtension::class, ['getLanguageStrings']);
        $languageMock->method('getLanguageStrings')->willReturn($languageStringsList);

        $sut = $this->getSut(
            shopLanguage: $languageMock
        );

        $this->assertSame($languageStringsList, $sut->getLanguageStringsArray());
    }

    public function testGetLanguageStringsArray(): void
    {
        $exampleLanguageStrings = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        /** @var LanguageExtension&ShopLanguage $shopLanguageMock */
        $shopLanguageMock = $this->createPartialMock(LanguageExtension::class, ['getLanguageStrings']);
        $shopLanguageMock->method('getLanguageStrings')->willReturn($exampleLanguageStrings);

        $sut = $this->getSut(shopLanguage: $shopLanguageMock);
        $this->assertSame($exampleLanguageStrings, $sut->getLanguageStringsArray());
    }


    public function getSut(
        ShopLanguage $shopLanguage = null
    ): LanguageProxy {
        return new LanguageProxy(
            language: $shopLanguage ?? $this->createStub(ShopLanguage::class)
        );
    }
}
