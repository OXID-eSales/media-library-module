<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Language\Core;

use OxidEsales\Eshop\Core\Language as ShopLanguage;
use OxidEsales\MediaLibrary\Language\Core\LanguageProxy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Language\Core\LanguageProxy
 */
class LanguageProxyTest extends TestCase
{
    public function testSanitizeFilename(): void
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

        $sut = new LanguageProxy($shopLanguageMock);
        $this->assertSame($exampleTranslation, $sut->getSeoReplaceChars());
    }
}
