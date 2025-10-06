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
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Exception\WrongMediaIdGivenException;
use OxidEsales\MediaLibrary\Media\Repository\MediaFactoryInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepository;
use OxidEsales\MediaLibrary\Media\Repository\MediaAltRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use OxidEsales\Eshop\Core\Language;

#[CoversClass(MediaRepository::class)]
class MediaRepositoryTest extends RepositoryIntegrationTestCase
{
    #[Test]
    public function getShopFolderMediaCount(): void
    {
        $this->createTestItems(3, 'someFolder');
        $this->createTestItems(2, '');

        $contextStub = $this->createMock(ContextInterface::class);
        $contextStub->method('getCurrentShopId')->willReturn(2);
        $sut = $this->getSut(
            context: $contextStub
        );

        $this->assertSame(3, $sut->getFolderMediaCount('someFolder'));
        $this->assertSame(3, $sut->getFolderMediaCount(''));
    }

    #[DataProvider('getFolderMediaDataProvider')]
    #[Test]
    public function getShopFolderMediaInFolder(
        string $folder,
        int $start,
        int $expectedItems,
        int $firstListItemId
    ): void {
        $this->createTestItems(7, 'someFolder');
        $this->createTestItems(3, '');

        $sut = $this->getSutForShop(2);

        $result = $sut->getFolderMedia($folder, $start, 5);

        $this->assertSame($expectedItems, count($result));
        foreach ($result as $key => $oneItem) {
            $this->assertInstanceOf(Media::class, $oneItem);
            $expectedOxid = $folder . 'example' . ($firstListItemId - $key);
            $this->assertSame($expectedOxid, $oneItem->getOxid());
            $this->assertSame('alttext_' . $expectedOxid, $oneItem->getMediaAltText());
        }
    }

    #[Test]
    public function getShopFolderMediaInRootWithFolderPresent(): void
    {
        $expectedItems = 4;
        $firstListItemId = 3;

        $this->createTestItems(7, 'someFolder');
        $this->createTestItems(3, '');

        $sut = $this->getSutForShop(2);

        $result = $sut->getFolderMedia('', 0, 5);

        $this->assertSame($expectedItems, count($result));

        $oneItem = current($result);
        $this->assertInstanceOf(Media::class, $oneItem);
        $this->assertSame('someFolder', $oneItem->getOxid());
        next($result);

        foreach ($result as $key => $oneItem) {
            if (!$key) {
                continue;
            }
            $this->assertInstanceOf(Media::class, $oneItem);
            $this->assertSame('example' . ($firstListItemId - $key + 1), $oneItem->getOxid());
        }
    }

    #[Test]
    public function getMediaByIdNotFound(): void
    {
        $sut = $this->getSut();

        $this->expectException(MediaNotFoundException::class);
        $sut->getMediaById('someWrongId');
    }

    public static function getFolderMediaDataProvider(): \Generator
    {
        yield "first page in folder" => [
            'folder' => 'someFolder',
            'start' => 0,
            'expectedItems' => 5,
            'firstListItemId' => 7
        ];

        yield "second page in folder" => [
            'folder' => 'someFolder',
            'start' => 5,
            'expectedItems' => 2,
            'firstListItemId' => 2
        ];
    }

    private function createTestItems(int $amount, string $folderId): void
    {
        $queryBuilder = $this->getAddItemQueryBuilder();
        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $altTextLanguageId = 1;

        if ($folderId) {
            $queryBuilder->setParameters([
                'OXID' => $folderId,
                'OXSHOPID' => 2,
                'DDFILENAME' => $folderId . 'Filename',
                'DDFILESIZE' => 0,
                'DDFILETYPE' => 'directory',
                'DDIMAGESIZE' => 0,
                'DDFOLDERID' => '',
                'OXTIMESTAMP' => date("Y-m-d H:i:59")
            ])->execute();

            $qbAlt = $queryBuilderFactory->create();
            $qbAlt->insert('ddmedia_translations')->values([
                'OXOBJECTID' => ':OXOBJECTID',
                'OXLANGUAGEID' => ':OXLANGUAGEID',
                'OXALTSHORTTEXT' => ':OXALTSHORTTEXT',
            ])->setParameters([
                'OXOBJECTID' => $folderId,
                'OXLANGUAGEID' => $altTextLanguageId,
                'OXALTSHORTTEXT' => 'alttext_' . $folderId
            ])->execute();
        }

        for ($i = 1; $i <= $amount; $i++) {
            $oxid = $folderId . 'example' . $i;
            $queryBuilder->setParameters([
                'OXID' => $oxid,
                'OXSHOPID' => 2,
                'DDFILENAME' => 'filename' . $i . '.jpg',
                'DDFILESIZE' => $i * 10,
                'DDFILETYPE' => 'image/gif',
                'DDIMAGESIZE' => $i . '00x' . $i . '00.jpg',
                'DDFOLDERID' => $folderId,
                'OXTIMESTAMP' => date("Y-m-d H:i:") . $i
            ])->execute();

            $qbAlt = $queryBuilderFactory->create();
            $qbAlt->insert('ddmedia_translations')->values([
                'OXOBJECTID' => ':OXOBJECTID',
                'OXLANGUAGEID' => ':OXLANGUAGEID',
                'OXALTSHORTTEXT' => ':OXALTSHORTTEXT',
            ])->setParameters([
                'OXOBJECTID' => $oxid,
                'OXLANGUAGEID' => $altTextLanguageId,
                'OXALTSHORTTEXT' => 'alttext_' . $oxid
            ])->execute();
        }
    }

    private function getSutForShop(int $shopId): MediaRepository
    {
        $contextStub = $this->createMock(ContextInterface::class);
        $contextStub->method('getCurrentShopId')->willReturn($shopId);
        return $this->getSut(
            context: $contextStub
        );
    }

    private function getSut(
        ?ContextInterface $context = null,
        ?ConnectionProviderInterface $connectionProvider = null,
        ?MediaFactoryInterface $mediaFactory = null,
        ?Language $language = null
    ): MediaRepository {
        $language = $language ?? $this->createLanguageStub();
        return new MediaRepository(
            connectionProvider: $connectionProvider ?? $this->get(ConnectionProviderInterface::class),
            context: $context ?? $this->get(ContextInterface::class),
            mediaFactory: $mediaFactory ?? $this->get(MediaFactoryInterface::class),
            language: $language
        );
    }

    private function createLanguageStub(): Language
    {
        $languageStub = $this->createMock(Language::class);
        $languageStub->method('getBaseLanguage')->willReturn(1);
        return $languageStub;
    }

    #[Test]
    public function addMedia(): void
    {
        $oxid = 'someExampleMediaId';
        $exampleMedia = new Media(
            oxid: $oxid,
            fileName: 'someFilename',
            fileSize: 123,
            fileType: 'image/gif',
            imageSize: new ImageSize(111, 222),
            folderId: 'someFolderId'
        );

        $sut = $this->getSutForShop(3);
        $sut->addMedia($exampleMedia);

        $resultMedia = $sut->getMediaById($oxid);
        $this->assertEquals($exampleMedia, $resultMedia);
        $this->assertSame('', $resultMedia->getMediaAltText());
    }

    #[Test]
    public function renameMedia(): void
    {
        $mediaIdToRename = 'mediaToRename';

        $queryBuilder = $this->getAddItemQueryBuilder();
        $queryBuilder->setParameters([
            'OXID' => $mediaIdToRename,
            'OXSHOPID' => 2,
            'DDFILENAME' => 'OriginalName',
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'any',
            'DDIMAGESIZE' => 0,
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:59")
        ])->execute();

        $newName = 'NewName';

        $sut = $this->getSut();
        $renameResult = $sut->renameMedia($mediaIdToRename, $newName);
        $this->assertSame($newName, $renameResult->getFileName());

        $updatedData = $sut->getMediaById($mediaIdToRename);
        $this->assertSame($newName, $updatedData->getFileName());
    }

    #[Test]
    public function changeMediaFolder(): void
    {
        $mediaIdToUpdate = 'mediaToChangeFolderId';

        $queryBuilder = $this->getAddItemQueryBuilder();
        $queryBuilder->setParameters([
            'OXID' => $mediaIdToUpdate,
            'OXSHOPID' => 2,
            'DDFILENAME' => 'OriginalName',
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'any',
            'DDIMAGESIZE' => 0,
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:59")
        ])->execute();

        $newFolderId = uniqid();

        $sut = $this->getSut();
        $sut->changeMediaFolderId($mediaIdToUpdate, $newFolderId);

        $updatedData = $sut->getMediaById($mediaIdToUpdate);
        $this->assertSame($newFolderId, $updatedData->getFolderId());
    }

    #[Test]
    public function deleteRegularMedia(): void
    {
        $queryBuilder = $this->getAddItemQueryBuilder();

        $idToRemove = 'regularMediaForRemoval';
        $queryBuilder->setParameters([
            'OXID' => $idToRemove,
            'OXSHOPID' => 3,
            'DDFILENAME' => uniqid(),
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'not directory',
            'DDIMAGESIZE' => 0,
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:59")
        ])->execute();

        $sut = $this->getSut();
        $sut->deleteMedia($idToRemove);

        $this->expectException(MediaNotFoundException::class);
        $sut->getMediaById($idToRemove);
    }

    #[Test]
    public function deleteRemovesDirectoryRelatedMediaOnly(): void
    {
        $queryBuilder = $this->getAddItemQueryBuilder();

        $idToRemove = 'directoryMediaForRemoval';
        $queryBuilder->setParameters([
            'OXID' => $idToRemove,
            'OXSHOPID' => 3,
            'DDFILENAME' => uniqid(),
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'directory',
            'DDIMAGESIZE' => 0,
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:59")
        ])->execute();

        $inDirectoryId = uniqid();
        $queryBuilder->setParameters([
            'OXID' => $inDirectoryId,
            'OXSHOPID' => 3,
            'DDFILENAME' => uniqid(),
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'in directory',
            'DDIMAGESIZE' => 0,
            'DDFOLDERID' => $idToRemove,
            'OXTIMESTAMP' => date("Y-m-d H:i:59")
        ])->execute();

        $notInDirectoryId = uniqid();
        $queryBuilder->setParameters([
            'OXID' => $notInDirectoryId,
            'OXSHOPID' => 3,
            'DDFILENAME' => uniqid(),
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'not in directory',
            'DDIMAGESIZE' => 0,
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:59")
        ])->execute();

        $sut = $this->getSut();
        $sut->deleteMedia($idToRemove);

        $this->assertInstanceOf(Media::class, $sut->getMediaById($notInDirectoryId));

        $this->expectException(MediaNotFoundException::class);
        $sut->getMediaById($inDirectoryId);
    }

    #[Test]
    public function deleteArgumentWrongValueExplodes(): void
    {
        $sut = $this->getSut();

        $this->expectException(WrongMediaIdGivenException::class);
        $sut->deleteMedia('');
    }

    #[Test]
    public function deleteMediaRemovesAltTextTranslations(): void
    {
        $connection = ContainerFacade::get(ConnectionProviderInterface::class)->get();
        $mediaId = uniqid();

        $queryBuilder = $this->getAddItemQueryBuilder();
        $queryBuilder->setParameters([
            'OXID' => $mediaId,
            'OXSHOPID' => 2,
            'DDFILENAME' => 'TestImage.jpg',
            'DDFILESIZE' => 1000,
            'DDFILETYPE' => 'image/jpeg',
            'DDIMAGESIZE' => '100x100',
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:s")
        ])->execute();

        $altText1 = uniqid();
        $altText2 = uniqid();
        $altText3 = uniqid();

        $connection->executeQuery(
            "INSERT INTO ddmedia_translations (OXOBJECTID, OXLANGUAGEID, OXALTSHORTTEXT) VALUES
             (:id1, 0, :alt1),
             (:id2, 1, :alt2),
             (:id3, 2, :alt3)",
            [
                'id1' => $mediaId,
                'alt1' => $altText1,
                'id2' => $mediaId,
                'alt2' => $altText2,
                'id3' => $mediaId,
                'alt3' => $altText3
            ]
        );

        $result = $connection->executeQuery(
            "SELECT COUNT(*) FROM ddmedia_translations WHERE OXOBJECTID = :id",
            ['id' => $mediaId]
        );
        $this->assertEquals(3, $result->fetchOne());

        $sut = $this->getSut();
        $sut->deleteMedia($mediaId);

        $this->expectException(MediaNotFoundException::class);
        $sut->getMediaById($mediaId);

        $result = $connection->executeQuery(
            "SELECT COUNT(*) FROM ddmedia_translations WHERE OXOBJECTID = :id",
            ['id' => $mediaId]
        );
        $this->assertEquals(0, $result->fetchOne());
    }

    #[Test]
    public function deleteFolderRemovesAltTextForAllMediaInFolder(): void
    {
        $connection = ContainerFacade::get(ConnectionProviderInterface::class)->get();
        $folderId = uniqid();
        $mediaIds = [uniqid(), uniqid()];
        $outsideId = uniqid();

        // Add folder
        $this->getAddItemQueryBuilder()->setParameters([
            'OXID' => $folderId,
            'OXSHOPID' => 2,
            'DDFILENAME' => uniqid(),
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'directory',
            'DDIMAGESIZE' => '',
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:s")
        ])->execute();

        // Add media inside folder
        foreach ($mediaIds as $i => $mediaId) {
            $this->getAddItemQueryBuilder()->setParameters([
                'OXID' => $mediaId,
                'OXSHOPID' => 2,
                'DDFILENAME' => uniqid() . '.jpg',
                'DDFILESIZE' => 1000 * ($i + 1),
                'DDFILETYPE' => 'image/jpeg',
                'DDIMAGESIZE' => '100x100',
                'DDFOLDERID' => $folderId,
                'OXTIMESTAMP' => date("Y-m-d H:i:s")
            ])->execute();
        }

        // Add media outside folder
        $this->getAddItemQueryBuilder()->setParameters([
            'OXID' => $outsideId,
            'OXSHOPID' => 2,
            'DDFILENAME' => uniqid() . '.jpg',
            'DDFILESIZE' => 3000,
            'DDFILETYPE' => 'image/jpeg',
            'DDIMAGESIZE' => '300x300',
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => date("Y-m-d H:i:s")
        ])->execute();

        // Insert alt texts
        $ids = array_merge([$folderId], $mediaIds, [$outsideId]);
        foreach ($ids as $id) {
            $connection->executeQuery(
                "INSERT INTO ddmedia_translations (OXOBJECTID, OXLANGUAGEID, OXALTSHORTTEXT) VALUES (:id, 0, :alt)",
                ['id' => $id, 'alt' => uniqid()]
            );
        }

        // Verify all alt texts exist
        $result = $connection->executeQuery(
            "SELECT COUNT(*) FROM ddmedia_translations WHERE OXOBJECTID IN (?, ?, ?, ?)",
            [$folderId, $mediaIds[0], $mediaIds[1], $outsideId]
        );
        $this->assertEquals(4, $result->fetchOne());

        // Delete folder
        $sut = $this->getSut();
        $sut->deleteMedia($folderId);

        // Assert deleted
        foreach ([$folderId, ...$mediaIds] as $id) {
            $this->expectException(MediaNotFoundException::class);
            $sut->getMediaById($id);
        }
        $this->assertInstanceOf(Media::class, $sut->getMediaById($outsideId));

        // Alt text checks
        $result = $connection->executeQuery(
            "SELECT COUNT(*) FROM ddmedia_translations WHERE OXOBJECTID = :id",
            ['id' => $outsideId]
        );
        $this->assertEquals(1, $result->fetchOne());

        $result = $connection->executeQuery(
            "SELECT COUNT(*) FROM ddmedia_translations WHERE OXOBJECTID IN (?, ?, ?)",
            [$folderId, $mediaIds[0], $mediaIds[1]]
        );
        $this->assertEquals(0, $result->fetchOne());
    }
}
