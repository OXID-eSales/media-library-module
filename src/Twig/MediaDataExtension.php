<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Twig;

use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MediaDataExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'oeMediaUrl',
                [$this->container->get(MediaDataLogicInterface::class), 'getMediaUrl']
            )
        ];
    }
}
