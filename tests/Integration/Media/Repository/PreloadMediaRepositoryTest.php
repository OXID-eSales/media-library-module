<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Media\Repository;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\MediaFactoryInterface;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepository;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PreloadMediaRepository::class)]
class PreloadMediaRepositoryTest extends RepositoryIntegrationTestCase
{
    #[Test]
    public function getMediaByIdReturnsMediaObjectWithoutPreloadRegistration()
    {
        $id = $this->createRandomMedia();

        $sut = $this->getSut();
        $media = $sut->getMediaById($id);
        $this->assertSame($id, $media->getOxid());
        $this->assertSame('alttext_' . $id, $media->getMediaAltText());
    }

    #[Test]
    public function getMediaByIdStillReturnsRemovedMediaObjectAfterItsLoaded()
    {
        $id = $this->createRandomMedia();

        $sut = $this->getSut();

        // load the media object for the first time
        $media = $sut->getMediaById($id);
        $this->assertSame('alttext_' . $id, $media->getMediaAltText());

        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder->delete("ddmedia")
            ->where('OXID = :OXID')
            ->setParameter('OXID', $id)
            ->execute();

        $media = $sut->getMediaById($id);
        $this->assertSame($id, $media->getOxid());
        $this->assertSame('alttext_' . $id, $media->getMediaAltText());
    }

    #[Test]
    public function getMediaByIdIsFastForMultipleCalls()
    {
        $id = $this->createRandomMedia();

        $sut = $this->getSut();

        // load the media object for the first time
        $media = $sut->getMediaById($id);
        $this->assertSame('alttext_' . $id, $media->getMediaAltText());

        $startTime = microtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $sut->getMediaById($id);
        }
        $totalTime = microtime(true) - $startTime;

        $this->assertLessThan(0.1, $totalTime);
    }

    #[Test]
    public function getMediaByIdDoesntReturnRemovedMediaObjectIfItWasntLoadedBeforeRemoved()
    {
        $id = $this->createRandomMedia();

        $sut = $this->getSut();

        // register the media object for preload, but do not load it
        $sut->registerForPreload($id);

        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder->delete("ddmedia")
            ->where('OXID = :OXID')
            ->setParameter('OXID', $id)
            ->execute();

        $this->expectException(MediaNotFoundException::class);
        $sut->getMediaById($id);
    }

    #[Test]
    public function getMediaByIdTriggersLoadingOfAllRegisteredMediaItems()
    {
        $id1 = $this->createRandomMedia();
        $id2 = $this->createRandomMedia();
        $id3 = $this->createRandomMedia();

        $sut = $this->getSut();

        // load the media object for the first time
        $sut->registerForPreload($id1, $id2, $id3);

        $media1 = $sut->getMediaById($id1);
        $this->assertSame($id1, $media1->getOxid());
        $this->assertSame('alttext_' . $id1, $media1->getMediaAltText());

        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder->delete("ddmedia")->where('OXID = :OXID');
        $queryBuilder->setParameter('OXID', $id1)->execute();
        $queryBuilder->setParameter('OXID', $id2)->execute();
        $queryBuilder->setParameter('OXID', $id3)->execute();

        $media2 = $sut->getMediaById($id2);
        $this->assertSame($id2, $media2->getOxid());
        $this->assertSame('alttext_' . $id2, $media2->getMediaAltText());

        $media3 = $sut->getMediaById($id3);
        $this->assertSame($id3, $media3->getOxid());
        $this->assertSame('alttext_' . $id3, $media3->getMediaAltText());
    }

    #[Test]
    public function getMediaByIdReturnsEmptyAltTextIfNoTranslationExists()
    {
        $queryBuilder = $this->getAddItemQueryBuilder();
        $id = uniqid();
        $queryBuilder->setParameters([
            'OXID' => $id,
            'OXSHOPID' => 1,
            'DDFILENAME' => uniqid(),
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'not in directory',
            'DDIMAGESIZE' => 0,
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:59")
        ])->execute();

        $sut = $this->getSut();
        $media = $sut->getMediaById($id);
        $this->assertSame($id, $media->getOxid());
        $this->assertSame('', $media->getMediaAltText());
    }

    private function getSut(): PreloadMediaRepositoryInterface
    {
        return new PreloadMediaRepository(
            connection: ContainerFacade::get(ConnectionProviderInterface::class)->get(),
            mediaFactory: $this->get(MediaFactoryInterface::class),
            language: $this->createLanguageStub(),
        );
    }

    private function createLanguageStub(): LanguageInterface
    {
        return $this->createConfiguredStub(LanguageInterface::class, [
            'getBaseLanguage' => 1,
        ]);
    }

    private function createRandomMedia(): string
    {
        $queryBuilder = $this->getAddItemQueryBuilder();
        $queryBuilder->setParameters([
            'OXID' => $id = uniqid(),
            'OXSHOPID' => 1,
            'DDFILENAME' => uniqid(),
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'not in directory',
            'DDIMAGESIZE' => 0,
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:59")
        ])->execute();

        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $qbAlt = $queryBuilderFactory->create();
        $qbAlt->insert('ddmedia_translations')->values([
            'OXOBJECTID' => ':OXOBJECTID',
            'OXLANGUAGEID' => ':OXLANGUAGEID',
            'OXALTSHORTTEXT' => ':OXALTSHORTTEXT',
        ])->setParameters([
            'OXOBJECTID' => $id,
            'OXLANGUAGEID' => 1,
            'OXALTSHORTTEXT' => 'alttext_' . $id
        ])->execute();

        return $id;
    }
}
