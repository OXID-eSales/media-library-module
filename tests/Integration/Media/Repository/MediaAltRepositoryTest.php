<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Media\Repository;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\MediaLibrary\Media\DataType\MediaAltTextInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaAltRepository;
use PHPUnit\Framework\Attributes\Test;

class MediaAltRepositoryTest extends RepositoryIntegrationTestCase
{
    #[Test]
    public function saveAndRetrieveAltText(): void
    {
        $objectId = uniqid();
        $languageId = rand(1, 10);
        $text = uniqid();

        $altTextStub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $objectId,
            'getLanguageId' => $languageId,
            'getText' => $text,
        ]);

        $sut = $this->getSut();
        $sut->saveAltText($altTextStub);
        $results = $sut->getObjectAltTexts($objectId);

        $this->assertCount(1, $results);
        $this->assertSame($objectId, $results[0]->getObjectId());
        $this->assertSame($languageId, $results[0]->getLanguageId());
        $this->assertSame($text, $results[0]->getText());
    }

    #[Test]
    public function updateAltText(): void
    {
        $objectId = uniqid();
        $languageId = rand(1, 10);
        $text2 = uniqid();

        $altText1Stub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $objectId,
            'getLanguageId' => $languageId,
            'getText' => uniqid(),
        ]);

        $altText2Stub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $objectId,
            'getLanguageId' => $languageId,
            'getText' => $text2,
        ]);

        $sut = $this->getSut();
        $sut->saveAltText($altText1Stub);
        $sut->saveAltText($altText2Stub);
        $results = $sut->getObjectAltTexts($objectId);

        $this->assertCount(1, $results);
        $this->assertSame($text2, $results[0]->getText());
    }

    #[Test]
    public function saveMultipleLanguages(): void
    {
        $objectId = uniqid();

        $text1 = uniqid();
        $text2 = uniqid();
        $languageId1 = rand(1, 10);
        $languageId2 = rand(1, 10);
        while ($languageId2 === $languageId1) {
            $languageId2 = rand(1, 10);
        }
        $altText1Stub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $objectId,
            'getLanguageId' => $languageId1,
            'getText' => $text1,
        ]);

        $altText2Stub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $objectId,
            'getLanguageId' => $languageId2,
            'getText' => $text2,
        ]);

        $sut = $this->getSut();
        $sut->saveAltText($altText1Stub);
        $sut->saveAltText($altText2Stub);
        $results = $sut->getObjectAltTexts($objectId);

        $this->assertCount(2, $results);
        $texts = array_map(fn($a) => $a->getText(), $results);
        $this->assertContains($text1, $texts);
        $this->assertContains($text2, $texts);
    }

    #[Test]
    public function getAltTextsForNonExistentObjectReturnsEmpty(): void
    {
        $sut = $this->getSut();
        $results = $sut->getObjectAltTexts(uniqid());
        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    #[Test]
    public function saveEmptyAltText(): void
    {
        $objectId = uniqid();
        $languageId = rand(1, 10);

        $altTextStub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $objectId,
            'getLanguageId' => $languageId,
            'getText' => '',
        ]);

        $sut = $this->getSut();
        $sut->saveAltText($altTextStub);
        $results = $sut->getObjectAltTexts($objectId);
        $this->assertCount(1, $results);
        $this->assertSame('', $results[0]->getText());
    }

    #[Test]
    public function deleteMediaAltTextsRemovesAllTranslations(): void
    {
        $mediaId = uniqid();

        $sut = $this->getSut();
        foreach ([0, 1, 2] as $langId) {
            $altTextStub = $this->createConfiguredStub(MediaAltTextInterface::class, [
                'getObjectId' => $mediaId,
                'getLanguageId' => $langId,
                'getText' => uniqid(),
            ]);
            $sut->saveAltText($altTextStub);
        }

        $this->assertCount(3, $sut->getObjectAltTexts($mediaId));

        $sut->deleteMediaAltTexts($mediaId);
        $this->assertCount(0, $sut->getObjectAltTexts($mediaId));
    }

    #[Test]
    public function deleteMediaAltTextsWithEmptyString(): void
    {
        $mediaId = uniqid();

        $altTextStub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $mediaId,
            'getLanguageId' => 0,
            'getText' => uniqid(),
        ]);

        $sut = $this->getSut();
        $sut->saveAltText($altTextStub);
        $sut->deleteMediaAltTexts('');

        $this->assertCount(1, $sut->getObjectAltTexts($mediaId));
    }

    #[Test]
    public function deleteMediaAltTextsForNonExistentMedia(): void
    {
        $existingMediaId = uniqid();

        $altTextStub = $this->createConfiguredStub(MediaAltTextInterface::class, [
            'getObjectId' => $existingMediaId,
            'getLanguageId' => 0,
            'getText' => uniqid(),
        ]);

        $sut = $this->getSut();
        $sut->saveAltText($altTextStub);

        $nonExistentId = uniqid();
        $sut->deleteMediaAltTexts($nonExistentId);

        $this->assertCount(1, $sut->getObjectAltTexts($existingMediaId));
    }

    #[Test]
    public function deleteMediaAltTextsIsolation(): void
    {
        $mediaId1 = uniqid();
        $mediaId2 = uniqid();

        $altTextsData = [
            [$mediaId1, 0,  uniqid()],
            [$mediaId1, 1,  uniqid()],
            [$mediaId2, 0, $text2_0 = uniqid()],
            [$mediaId2, 1, $text2_1 = uniqid()],
        ];

        $sut = $this->getSut();
        foreach ($altTextsData as [$objectId, $langId, $text]) {
            $altTextStub = $this->createConfiguredStub(MediaAltTextInterface::class, [
                'getObjectId' => $objectId,
                'getLanguageId' => $langId,
                'getText' => $text,
            ]);
            $sut->saveAltText($altTextStub);
        }

        $sut->deleteMediaAltTexts($mediaId1);
        $this->assertCount(0, $sut->getObjectAltTexts($mediaId1));

        $this->assertCount(2, $sut->getObjectAltTexts($mediaId2));
        $texts = array_map(fn($a) => $a->getText(), $sut->getObjectAltTexts($mediaId2));
        $this->assertContains($text2_0, $texts);
        $this->assertContains($text2_1, $texts);
    }

    private function getSut(
        ?QueryBuilderFactoryInterface $queryBuilderFactory = null
    ): MediaAltRepository {
        return new MediaAltRepository(
            $queryBuilderFactory ?? ContainerFacade::get(QueryBuilderFactoryInterface::class)
        );
    }
}
