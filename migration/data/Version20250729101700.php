<?php


/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

// phpcs:ignoreFile
class Version20250729101700 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        if (!$schema->hasTable('ddoemedialibrary_translations')) {
            $this->addSql("CREATE TABLE `ddoemedialibrary_translations` (
                `OXID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Primary oxid',
                `OXSHOPID` int(11) NOT NULL DEFAULT '0' COMMENT 'Shop id (oxshops), value 0 in case no shop was specified',
                `OXOBJECTID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'object identifier aka oxid',
                `OXLOCALEID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'locale oxid',
                `OXALTSHORTTEXT` varchar(255) NOT NULL DEFAULT '' COMMENT 'short alternative text',
                `OXTIMESTAMP` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'timestamp',
                PRIMARY KEY (`OXID`),
                UNIQUE KEY `OXOBJECT_LOCALE` (`OXSHOPID`, `OXOBJECTID`, `OXLOCALEID`),
                INDEX `IDX_OBJECTID` (`OXOBJECTID`),
                INDEX `IDX_LOCALEID` (`OXLOCALEID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        }
    }
}
