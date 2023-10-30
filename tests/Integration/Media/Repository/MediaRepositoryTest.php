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
        $this->createItems(3, 'someFolder');
        $this->createItems(2, '');

        $basicContextStub = $this->createMock(BasicContextInterface::class);
        $basicContextStub->method('getCurrentShopId')->willReturn(2);

        $sut = $this->getSut(
            basicContext: $basicContextStub
        );
        $this->assertSame(3, $sut->getFolderMediaCount('someFolder'));
        $this->assertSame(2, $sut->getFolderMediaCount(''));
    }

    public function testGetShopFolderMedia(): void
    {
        $folder = 'someFolder';
        $this->createItems(20, $folder);
        $this->createItems(3, '');

        $basicContextStub = $this->createMock(BasicContextInterface::class);
        $basicContextStub->method('getCurrentShopId')->willReturn(2);

        $sut = $this->getSut(
            basicContext: $basicContextStub
        );

        $result = $sut->getFolderMedia($folder, 0);

        $this->assertSame(18, count($result));
        foreach ($result as $oneItem) {
            $this->assertInstanceOf(Media::class, $oneItem);
        }

        $result = $sut->getFolderMedia($folder, 1);

        $this->assertSame(2, count($result));
        foreach ($result as $oneItem) {
            $this->assertInstanceOf(Media::class, $oneItem);
        }

        $result = $sut->getFolderMedia('', 0);

        $this->assertSame(3, count($result));
        foreach ($result as $oneItem) {
            $this->assertInstanceOf(Media::class, $oneItem);
        }
    }

    protected function createItems(int $amount, string $folderId): void
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
        ]);

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
            ]);
            $queryBuilder->execute();
        }
    }

    /**
     * @return MediaRepository
     */
    protected function getSut(
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
}
