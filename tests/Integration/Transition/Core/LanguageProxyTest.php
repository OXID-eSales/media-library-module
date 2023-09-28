<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Transition\Core;

use OxidEsales\MediaLibrary\Transition\Core\Language;
use OxidEsales\MediaLibrary\Transition\Core\LanguageProxy;
use PHPUnit\Framework\TestCase;

class LanguageProxyTest extends TestCase
{
    public function testGetLanguageStrings(): void
    {
        $languageStringsList = ['somekey' => 'someValue'];

        /** @var \OxidEsales\Eshop\Core\Language $languageMock */
        $languageMock = $this->createPartialMock(Language::class, ['getLanguageStrings']);
        $languageMock->method('getLanguageStrings')->willReturn($languageStringsList);

        $sut = new LanguageProxy($languageMock);

        $this->assertSame($languageStringsList, $sut->getLanguageStringsArray());
    }
}
