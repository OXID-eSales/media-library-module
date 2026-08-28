<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Transition\Core;

use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\MediaLibrary\Media\Service\FallbackMediaSeederInterface;
use OxidEsales\MediaLibrary\Module;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

/**
 * Class defines what module does on Shop events.
 */
class Events
{
    /**
     * Execute action on activate event
     */
    public static function onActivate(): void
    {
        self::executeMigrations();
        self::seedFallbackMedia();
    }

    private static function executeMigrations(): void
    {
        $migrations = (new MigrationsBuilder())->build();

        $output = new BufferedOutput();
        $migrations->setOutput($output);
        $needsUpdate = $migrations->execute('migrations:up-to-date', Module::MODULE_ID);

        if ($needsUpdate) {
            $migrations->execute('migrations:migrate', Module::MODULE_ID);
        }
    }

    private static function seedFallbackMedia(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();

        try {
            /** @var FallbackMediaSeederInterface $seeder */
            $seeder = $container->get(FallbackMediaSeederInterface::class);
            $seeder->seedMedia();
        } catch (Throwable $throwable) {
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);
            $logger->error(
                'Could not seed the fallback media.',
                ['exception' => $throwable->getMessage()]
            );
        }
    }

    /**
     * Execute action on deactivate event
     */
    public static function onDeactivate(): void
    {
    }
}
