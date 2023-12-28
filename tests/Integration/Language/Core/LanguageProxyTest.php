<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Language\Core;

use OxidEsales\MediaLibrary\Language\Core\LanguageExtension;
use OxidEsales\MediaLibrary\Language\Core\LanguageProxy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Language\Core\LanguageExtension
 */
class LanguageProxyTest extends TestCase
{
    public function testGetLanguageStrings(): void
    {
        $languageStringsList = ['somekey' => 'someValue'];

        /** @var \OxidEsales\Eshop\Core\Language $languageMock */
        $languageMock = $this->createPartialMock(LanguageExtension::class, ['getLanguageStrings']);
        $languageMock->method('getLanguageStrings')->willReturn($languageStringsList);

        $sut = new LanguageProxy($languageMock);

        $this->assertSame($languageStringsList, $sut->getLanguageStringsArray());
    }
}
