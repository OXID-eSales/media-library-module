<?php

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

// phpcs:ignoreFile
final class Version20250910121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ddmedia_translations table for media alt text translations.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        if (!$schema->hasTable('ddmedia_translations')) {
            $this->addSql("CREATE TABLE `ddmedia_translations` (
                `OXOBJECTID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'object identifier aka oxid',
                `OXLANGUAGEID` int NOT NULL DEFAULT 0,
                `OXALTSHORTTEXT` varchar(255) NOT NULL DEFAULT '' COMMENT 'short alternative text',
                `OXTIMESTAMP` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'timestamp',
                UNIQUE KEY `OXOBJECTID_OXLANGUAGEID` (`OXOBJECTID`, `OXLANGUAGEID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }
    }
}
