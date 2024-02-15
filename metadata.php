<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\MediaLibrary\Service\ModuleSettings;

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => 'ddoemedialibrary',
    'title'       => 'Mediathek',
    'description' => [
        'de' => '',
        'en' => '',
    ],
    'thumbnail'   => 'logo.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG & digidesk - media solutions',
    'url'         => 'http://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'extend'      => [
        // Core
        \OxidEsales\Eshop\Core\ViewConfig::class => \OxidEsales\MediaLibrary\Transition\Core\ViewConfig::class,
        \OxidEsales\Eshop\Core\Language::class   => \OxidEsales\MediaLibrary\Language\Core\LanguageExtension::class,
    ],
    'controllers' => [
        // Lang
        'ddoelangjs'        => \OxidEsales\MediaLibrary\Language\Controller\MediaLangJs::class,

        // Admin Controller
        'ddoemedia_view'    => \OxidEsales\MediaLibrary\Application\Controller\Admin\MediaController::class,
        'ddoemedia_wrapper' => \OxidEsales\MediaLibrary\Application\Controller\Admin\MediaWrapperController::class,

    ],
    'templates'   => [
    ],
    'events'      => [
        'onActivate'   => '\OxidEsales\MediaLibrary\Transition\Core\Events::onActivate',
        'onDeactivate' => '\OxidEsales\MediaLibrary\Transition\Core\Events::onDeactivate',
    ],
    'blocks'      => [],
    'settings'    => [
        [
            'group' => 'main',
            'name'  => ModuleSettings::SETTING_ALTERNATIVE_IMAGE_URL,
            'type'  => 'str',
            'value' => '',
        ],
    ],
];
