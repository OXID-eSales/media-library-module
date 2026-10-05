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

        $responseSpy = $this->createMock(ResponseInterface::class);
        $responseSpy->expects($this->once())
            ->method('responseAsJavaScript')
            ->with($this->matchesRegularExpression('/i18n\s?=\s?' . json_encode($exampleLanguageKeys) . ';/'));

        $sut = $this->getSut(
            language: $languageMock,
            response: $responseSpy,
        );

        $sut->init();
    }

    private function getSut(
        ?LanguageInterface $language = null,
        ?ResponseInterface $response = null,
    ): MediaLangJs {
        $language ??= $this->createStub(LanguageInterface::class);
        $response ??= $this->createStub(ResponseInterface::class);

        $sut = $this->createPartialMock(
            originalClassName: MediaLangJs::class,
            methods: ['getService'],
        );
        $sut->method('getService')->willReturnMap([
            [LanguageInterface::class, $language],
            [ResponseInterface::class, $response],
        ]);

        return $sut;
    }
}
