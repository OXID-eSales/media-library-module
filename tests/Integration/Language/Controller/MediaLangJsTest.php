<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Language\Controller;

use OxidEsales\MediaLibrary\Language\Controller\MediaLangJs;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MediaLangJs::class)]
class MediaLangJsTest extends \PHPUnit\Framework\TestCase
{
    public function testInit(): void
    {
        $exampleLanguageKeys = ['key' => 'value'];
        $languageMock = $this->createMock(LanguageInterface::class);
        $languageMock->method('getLanguageStringsArray')->willReturn($exampleLanguageKeys);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('responseAsJavaScript')
            ->with($this->matchesRegularExpression('/i18n\s?=\s?' . json_encode($exampleLanguageKeys) . ';/'));

        $sut = $this->createPartialMock(MediaLangJs::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [LanguageInterface::class, $languageMock],
            [ResponseInterface::class, $responseMock]
        ]);

        $sut->init();
    }
}
