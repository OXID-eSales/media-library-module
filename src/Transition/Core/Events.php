<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Transition\Core;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\MediaLibrary\Media\Service\FallbackMediaSeederInterface;
use Psr\Log\LoggerInterface;
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
        self::seedFallbackMedia();
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
