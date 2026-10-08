<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaServiceInterface;
use OxidEsales\MediaLibrary\Media\Service\ValidatorStrategyService;
use OxidEsales\MediaLibrary\Media\Service\ValidatorStrategyServiceInterface;
use OxidEsales\MediaLibrary\Validation\Service\DirectoryNameValidatorChainInterface;
use OxidEsales\MediaLibrary\Validation\Service\DocumentNameValidatorChainInterface;
use OxidEsales\MediaLibrary\Validation\Service\FileNameValidatorChainInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidatorStrategyService::class)]
class ValidatorStrategyServiceTest extends TestCase
{
    #[Test]
    public function getValidatorChainByMediaIdReturnsDirectoryValidatorChainForDirectory(): void
    {
        $fileNameValidatorChain = $this->createStub(FileNameValidatorChainInterface::class);
        $directoryNameValidatorChain = $this->createStub(DirectoryNameValidatorChainInterface::class);

        $mediaServiceMock = $this->createMock(MediaServiceInterface::class);

        $sut = $this->getSut(
            mediaService: $mediaServiceMock,
            fileNameValidatorChain: $fileNameValidatorChain,
            directoryNameValidatorChain: $directoryNameValidatorChain,
        );

        $exampleMediaId = uniqid();

        $mediaServiceMock->method('getMediaById')
            ->with($exampleMediaId)
            ->willReturn($this->createConfiguredStub(MediaInterface::class, [
                'isDirectory' => true
            ]));


        $this->assertSame($directoryNameValidatorChain, $sut->getValidatorChainByMediaId($exampleMediaId));
    }

    #[Test]
    public function getValidatorChainByMediaIdReturnsFileValidatorChainForFile(): void
    {
        $fileNameValidatorChain = $this->createStub(FileNameValidatorChainInterface::class);
        $directoryNameValidatorChain = $this->createStub(DirectoryNameValidatorChainInterface::class);

        $mediaServiceMock = $this->createMock(MediaServiceInterface::class);

        $sut = $this->getSut(
            mediaService: $mediaServiceMock,
            fileNameValidatorChain: $fileNameValidatorChain,
            directoryNameValidatorChain: $directoryNameValidatorChain,
        );

        $exampleMediaId = uniqid();

        $mediaServiceMock->method('getMediaById')
            ->with($exampleMediaId)
            ->willReturn($this->createConfiguredStub(MediaInterface::class, [
                'isDirectory' => false
            ]));


        $this->assertSame($fileNameValidatorChain, $sut->getValidatorChainByMediaId($exampleMediaId));
    }

    private function getSut(
        ?MediaServiceInterface $mediaService = null,
        ?DocumentNameValidatorChainInterface $fileNameValidatorChain = null,
        ?DocumentNameValidatorChainInterface $directoryNameValidatorChain = null,
    ): ValidatorStrategyServiceInterface {
        $mediaService ??= $this->createStub(MediaServiceInterface::class);
        $fileNameValidatorChain ??= $this->createStub(FileNameValidatorChainInterface::class);
        $directoryNameValidatorChain ??= $this->createStub(DirectoryNameValidatorChainInterface::class);

        return new ValidatorStrategyService(
            mediaService: $mediaService,
            fileNameValidatorChain: $fileNameValidatorChain,
            directoryNameValidatorChain: $directoryNameValidatorChain,
        );
    }
}
