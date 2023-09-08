<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\Facts\Facts;

class IntegrationTestCase extends \OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase
{
    public function setUp(): void
    {
        $facts = new Facts();
        $container = ContainerFactory::getInstance()->getContainer();
        $connection = $container->get(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();

        $testdata = file_get_contents(
            __DIR__ . '/../fixtures/testdata_'
            . strtolower($facts->getEdition()) . '.sql'
        );
        if (trim($testdata)) {
            $connection->executeStatement(
                $testdata
            );
        }
    }
}
