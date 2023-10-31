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
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\Repository\MediaFactoryInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepository;

class MediaRepositoryTest extends IntegrationTestCase
{
    protected QueryBuilderFactoryInterface $queryBuilderFactory;

    public function testGetShopFolderMediaCount(): void
    {
        $this->createTestItems(3, 'someFolder');
        $this->createTestItems(2, '');

        $basicContextStub = $this->createMock(BasicContextInterface::class);
        $basicContextStub->method('getCurrentShopId')->willReturn(2);
        $sut = $this->getSut(
            basicContext: $basicContextStub
        );

        $this->assertSame(3, $sut->getFolderMediaCount('someFolder'));
        $this->assertSame(2, $sut->getFolderMediaCount(''));
    }

    /**
     * @dataProvider getFolderMediaDataProvider
     */
    public function testGetShopFolderMedia(
        string $folder,
        int $start,
        int $expectedItems,
        int $firstListItemId
    ): void {
        $this->createTestItems(7, 'someFolder');
        $this->createTestItems(3, '');

        $basicContextStub = $this->createMock(BasicContextInterface::class);
        $basicContextStub->method('getCurrentShopId')->willReturn(2);
        $sut = $this->getSut(
            basicContext: $basicContextStub
        );

        $result = $sut->getFolderMedia($folder, $start, 5);

        $this->assertSame($expectedItems, count($result));
        foreach ($result as $key => $oneItem) {
            $this->assertInstanceOf(Media::class, $oneItem);
            $this->assertSame($folder . 'example' . ($firstListItemId - $key), $oneItem->getOxid());
        }
    }

    public function testGetMediaById(): void
    {
        $addItemQueryBuilder = $this->getAddItemQueryBuilder();
        $addItemQueryBuilder->setParameters([
            'OXID' => 'someFolderId',
            'OXSHOPID' => 2,
            'DDFILENAME' => 'someDirectoryName',
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'directory',
            'DDTHUMB' => '',
            'DDIMAGESIZE' => '',
            'DDFOLDERID' => '',
            'OXTIMESTAMP' => 'NOW()'
        ]);
        $addItemQueryBuilder->execute();

        $basicContextStub = $this->createMock(BasicContextInterface::class);
        $basicContextStub->method('getCurrentShopId')->willReturn(2);
        $sut = $this->getSut(
            basicContext: $basicContextStub
        );

        $this->assertNull($sut->getMediaById('someWrongId'));

        $result = $sut->getMediaById('someFolderId');
        $this->assertInstanceOf(Media::class, $result);
        $this->assertSame('someDirectoryName', $result->getFileName());
    }

    public function getFolderMediaDataProvider(): \Generator
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

        yield "first page without folder" => [
            'folder' => '',
            'page' => 0,
            'expectedItems' => 3,
            'firstListItemId' => 3
        ];
    }

    private function createTestItems(int $amount, string $folderId): void
    {
        $queryBuilder = $this->getAddItemQueryBuilder();

        for ($i = 1; $i <= $amount; $i++) {
            $queryBuilder->setParameters([
                'OXID' => $folderId . 'example' . $i,
                'OXSHOPID' => 2,
                'DDFILENAME' => 'filename' . $i . '.jpg',
                'DDFILESIZE' => $i * 10,
                'DDFILETYPE' => 'image/gif',
                'DDTHUMB' => 'thumbfilename' . $i . '.jpg',
                'DDIMAGESIZE' => $i . '00x' . $i . '00.jpg',
                'DDFOLDERID' => $folderId,
                'OXTIMESTAMP' => date("Y-m-d H:i:") . $i
            ]);
            $queryBuilder->execute();
        }
    }

    private function getSut(
        ?BasicContextInterface $basicContext = null,
        ?ConnectionProviderInterface $connectionProvider = null,
        ?MediaFactoryInterface $mediaFactory = null,
    ): MediaRepository {
        $sut = new MediaRepository(
            connectionProvider: $connectionProvider ?? $this->get(ConnectionProviderInterface::class),
            basicContext: $basicContext ?? $this->get(BasicContextInterface::class),
            mediaFactory: $mediaFactory ?? $this->get(MediaFactoryInterface::class),
        );

        return $sut;
    }

    private function getAddItemQueryBuilder(): \Doctrine\DBAL\Query\QueryBuilder
    {
        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder->insert("ddmedia")->values([
            'OXID' => ':OXID',
            'OXSHOPID' => ':OXSHOPID',
            'DDFILENAME' => ':DDFILENAME',
            'DDFILESIZE' => ':DDFILESIZE',
            'DDFILETYPE' => ':DDFILETYPE',
            'DDTHUMB' => ':DDTHUMB',
            'DDIMAGESIZE' => ':DDIMAGESIZE',
            'DDFOLDERID' => ':DDFOLDERID',
            'OXTIMESTAMP' => ':OXTIMESTAMP'
        ]);

        return $queryBuilder;
    }
}
