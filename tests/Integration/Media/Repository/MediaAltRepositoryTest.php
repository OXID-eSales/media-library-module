<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Media\Repository;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\MediaLibrary\Media\DataType\MediaAltText;
use OxidEsales\MediaLibrary\Media\Repository\MediaAltRepository;
use PHPUnit\Framework\Attributes\Test;

class MediaAltRepositoryTest extends RepositoryIntegrationTestCase
{
    #[Test]
    public function saveAndRetrieveAltText(): void
    {
        $objectId = uniqid();
        $languageId = rand(1, 10);
        $text = uniqid('alt_');
        $altText = new MediaAltText($objectId, $languageId, $text);

        $repository = $this->getSut();
        $repository->saveAltText($altText);
        $results = $repository->getObjectAltTexts($objectId);

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
        $altText1 = new MediaAltText($objectId, $languageId, 'first');
        $altText2 = new MediaAltText($objectId, $languageId, 'second');

        $repository = $this->getSut();
        $repository->saveAltText($altText1);
        $repository->saveAltText($altText2);
        $results = $repository->getObjectAltTexts($objectId);

        $this->assertCount(1, $results);
        $this->assertSame('second', $results[0]->getText());
    }

    #[Test]
    public function saveMultipleLanguages(): void
    {
        $objectId = uniqid();
        $altText1 = new MediaAltText($objectId, 1, 'en');
        $altText2 = new MediaAltText($objectId, 2, 'de');

        $repository = $this->getSut();
        $repository->saveAltText($altText1);
        $repository->saveAltText($altText2);
        $results = $repository->getObjectAltTexts($objectId);

        $this->assertCount(2, $results);
        $texts = array_map(fn($a) => $a->getText(), $results);
        $this->assertContains('en', $texts);
        $this->assertContains('de', $texts);
    }

    #[Test]
    public function getAltTextsForNonExistentObjectReturnsEmpty(): void
    {
        $repository = $this->getSut();
        $results = $repository->getObjectAltTexts(uniqid());
        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    #[Test]
    public function saveEmptyAltText(): void
    {
        $objectId = uniqid();
        $languageId = rand(1, 10);
        $altText = new MediaAltText($objectId, $languageId, '');

        $repository = $this->getSut();
        $repository->saveAltText($altText);
        $results = $repository->getObjectAltTexts($objectId);
        $this->assertCount(1, $results);
        $this->assertSame('', $results[0]->getText());
    }

    private function getSut(
        ?QueryBuilderFactoryInterface $queryBuilderFactory = null
    ): MediaAltRepository {
        return new MediaAltRepository(
            $queryBuilderFactory ?? ContainerFacade::get(QueryBuilderFactoryInterface::class)
        );
    }
}
