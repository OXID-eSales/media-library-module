<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Language\Controller;

use OxidEsales\MediaLibrary\Language\Controller\LanguageController;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

#[CoversClass(LanguageController::class)]
class LanguageControllerTest extends TestCase
{
    #[Test]
    public function getTranslationsReturnsLanguageStringsAsJson(): void
    {
        $languageStrings = [
            'SOME_KEY' => 'Some translation',
            'OTHER_KEY' => 'Other translation',
        ];

        $languageServiceStub = $this->createConfiguredStub(LanguageInterface::class, [
            'getLanguageStringsArray' => $languageStrings,
        ]);

        $sut = $this->getSut(
            languageService: $languageServiceStub,
        );

        $response = $sut->getTranslations();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJsonStringEqualsJsonString(
            expectedJson: json_encode($languageStrings),
            actualJson: $response->getContent(),
        );
    }

    private function getSut(
        ?LanguageInterface $languageService = null,
    ): LanguageController {
        $languageService ??= $this->createStub(LanguageInterface::class);

        return new LanguageController(
            languageService: $languageService,
        );
    }
}
