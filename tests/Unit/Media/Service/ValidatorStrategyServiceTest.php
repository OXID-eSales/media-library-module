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
use OxidEsales\MediaLibrary\Validation\Service\DirectoryNameValidatorChainInterface;
use OxidEsales\MediaLibrary\Validation\Service\FileNameValidatorChainInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidatorStrategyService::class)]
class ValidatorStrategyServiceTest extends TestCase
{
    public function testGetMediaValidatorReturnsDirectoryValidatorIfMediaIsDirectory(): void
    {
        $fileNameValidatorChain = $this->createStub(FileNameValidatorChainInterface::class);
        $directoryNameValidatorChain = $this->createStub(DirectoryNameValidatorChainInterface::class);

        $sut = new ValidatorStrategyService(
            mediaService: $mediaServiceMock = $this->createMock(MediaServiceInterface::class),
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

    public function testGetMediaValidatorReturnsFileValidatorIfMediaIsDirectory(): void
    {
        $fileNameValidatorChain = $this->createStub(FileNameValidatorChainInterface::class);
        $directoryNameValidatorChain = $this->createStub(DirectoryNameValidatorChainInterface::class);

        $sut = new ValidatorStrategyService(
            mediaService: $mediaServiceMock = $this->createMock(MediaServiceInterface::class),
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
}
