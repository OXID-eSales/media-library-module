<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Media\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepository;

class MediaRepositoryTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->prepareDatabase();
    }

    public function testGetFilesCount(): void
    {
        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);

        $sut = new MediaRepository($queryBuilderFactory);
        $this->assertSame(3, $sut->getShopFolderMediaCount(2, ''));
    }

    /**
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function prepareDatabase(): void
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
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

        $queryBuilder->setParameters([
            'OXID' => 'example1',
            'OXSHOPID' => 2,
            'DDFILENAME' => 'filename1.jpg',
            'DDFILESIZE' => 1 * 10,
            'DDFILETYPE' => 'image/gif',
            'DDTHUMB' => 'thumbfilename1.jpg',
            'DDIMAGESIZE' => '100x100.jpg',
            'DDFOLDERID' => '',
        ]);
        $queryBuilder->execute();

        $queryBuilder->setParameters([
            'OXID' => 'example2',
            'OXSHOPID' => 2,
            'DDFILENAME' => 'filename2.jpg',
            'DDFILESIZE' => 2 * 10,
            'DDFILETYPE' => 'image/gif',
            'DDTHUMB' => 'thumbfilename2.jpg',
            'DDIMAGESIZE' => '200x200.jpg',
            'DDFOLDERID' => '',
        ]);
        $queryBuilder->execute();

        $queryBuilder->setParameters([
            'OXID' => 'example3',
            'OXSHOPID' => 3,
            'DDFILENAME' => 'filename3.jpg',
            'DDFILESIZE' => 3 * 10,
            'DDFILETYPE' => 'image/gif',
            'DDTHUMB' => 'thumbfilename3.jpg',
            'DDIMAGESIZE' => '300x300.jpg',
            'DDFOLDERID' => '',
        ]);
        $queryBuilder->execute();

        $queryBuilder->setParameters([
            'OXID' => 'example4',
            'OXSHOPID' => 2,
            'DDFILENAME' => 'directory',
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'directory',
            'DDTHUMB' => '',
            'DDIMAGESIZE' => '',
            'DDFOLDERID' => '',
        ]);
        $queryBuilder->execute();

        $queryBuilder->setParameters([
            'OXID' => 'example5',
            'OXSHOPID' => 2,
            'DDFILENAME' => 'filename5.jpg',
            'DDFILESIZE' => 0,
            'DDFILETYPE' => 'img/jpeg',
            'DDTHUMB' => 'thumbfilename5.jpg',
            'DDIMAGESIZE' => '500x500.jpg',
            'DDFOLDERID' => 'subfolder',
        ]);
        $queryBuilder->execute();
    }
}
