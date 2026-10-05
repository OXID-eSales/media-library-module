<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Transition\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Transition\Core\ViewConfig;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ViewConfig::class)]
class ViewConfigTest extends IntegrationTestCase
{
    public function testGetMediaUrl(): void
    {
        $imageResourceMock = $this->createMock(MediaResourceInterface::class);
        $imageResourceMock->method('getUrlToMediaFiles')->willReturn('someFilePath');

        $sut = $this->getSut(
            mediaResource: $imageResourceMock,
        );

        $this->assertSame('someFilePath', $sut->getMediaUrl());
    }

    public function testFormJsFileUrl(): void
    {
        $config = Registry::getConfig();
        $file = tempnam($config->getConfigParam('sShopDir'), 'test_');
        file_put_contents($file, 'dummy content');
        $mtime = filemtime($file);

        $sut = $this->getSut();
        $shopUrl = $config->getCurrentShopUrl(false);

        $result = $sut->formJsFileUrl($shopUrl . basename($file));
        $this->assertStringEndsWith('?' . $mtime, $result);
        unlink($file);
    }

    private function getSut(?MediaResourceInterface $mediaResource = null): ViewConfig
    {
        $mediaResource ??= $this->createStub(MediaResourceInterface::class);

        $sut = $this->createPartialMock(
            originalClassName: oxNew(\OxidEsales\Eshop\Core\ViewConfig::class)::class,
            methods: ['getService'],
        );
        $sut->method('getService')->willReturnMap([
            [MediaResourceInterface::class, $mediaResource],
        ]);

        return $sut;
    }
}
