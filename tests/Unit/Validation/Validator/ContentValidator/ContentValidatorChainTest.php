<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;
use OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistryInterface;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\ContentValidatorChain;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\ContentValidatorInterface;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentValidatorChain::class)]
class ContentValidatorChainTest extends TestCase
{
    #[Test]
    public function validateFileNoOpsWhenExtensionIsNotInRegistry(): void
    {
        $extension = uniqid();

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => $extension,
        ]);

        $registryMock = $this->createMock(FileFormatRegistryInterface::class);
        $registryMock->expects($this->once())
            ->method('findByExtension')
            ->with($extension)
            ->willReturn(null);

        $contentValidatorSpy = $this->createMock(ContentValidatorInterface::class);
        $contentValidatorSpy->expects($this->never())->method('supports');
        $contentValidatorSpy->expects($this->never())->method('validate');

        $sut = $this->getSut($registryMock, [$contentValidatorSpy]);
        $sut->validateFile($filePathStub);
    }

    #[Test]
    public function validateFileNoOpsWhenNoValidatorSupportsTheFormat(): void
    {
        $extension = uniqid();
        $formatStub = $this->createConfiguredStub(FileFormatInterface::class, [
            'getExtension' => $extension,
        ]);

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => $extension,
        ]);

        $registryMock = $this->createMock(FileFormatRegistryInterface::class);
        $registryMock->expects($this->once())
            ->method('findByExtension')
            ->with($extension)
            ->willReturn($formatStub);

        $unsupportedMock = $this->createMock(ContentValidatorInterface::class);
        $unsupportedMock->expects($this->once())->method('supports')->with($formatStub)->willReturn(false);
        $unsupportedMock->expects($this->never())->method('validate');

        $sut = $this->getSut($registryMock, [$unsupportedMock]);
        $sut->validateFile($filePathStub);
    }

    #[Test]
    public function validateFileDispatchesToEverySupportingValidator(): void
    {
        $extension = uniqid();
        $formatStub = $this->createConfiguredStub(FileFormatInterface::class, [
            'getExtension' => $extension,
        ]);

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => $extension,
        ]);

        $registryMock = $this->createMock(FileFormatRegistryInterface::class);
        $registryMock->expects($this->once())
            ->method('findByExtension')
            ->with($extension)
            ->willReturn($formatStub);

        $supportingMock = $this->createMock(ContentValidatorInterface::class);
        $supportingMock->expects($this->once())->method('supports')->with($formatStub)->willReturn(true);
        $supportingMock->expects($this->once())->method('validate')->with($filePathStub);

        $alsoSupportingMock = $this->createMock(ContentValidatorInterface::class);
        $alsoSupportingMock->expects($this->once())->method('supports')->with($formatStub)->willReturn(true);
        $alsoSupportingMock->expects($this->once())->method('validate')->with($filePathStub);

        $skipMock = $this->createMock(ContentValidatorInterface::class);
        $skipMock->expects($this->once())->method('supports')->with($formatStub)->willReturn(false);
        $skipMock->expects($this->never())->method('validate');

        $sut = $this->getSut($registryMock, [$supportingMock, $skipMock, $alsoSupportingMock]);
        $sut->validateFile($filePathStub);
    }

    /**
     * @param iterable<ContentValidatorInterface> $contentValidators
     */
    private function getSut(
        ?FileFormatRegistryInterface $registry = null,
        iterable $contentValidators = [],
    ): FilePathValidatorInterface {
        $registry ??= $this->createStub(FileFormatRegistryInterface::class);

        return new ContentValidatorChain(
            registry: $registry,
            contentValidators: $contentValidators,
        );
    }
}
