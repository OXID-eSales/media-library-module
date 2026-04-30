<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormat;
use OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistryInterface;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidatorDispatcher;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\ContentValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentValidatorDispatcher::class)]
class ContentValidatorDispatcherTest extends TestCase
{
    #[Test]
    public function validateFileNoOpsWhenExtensionIsNotInRegistry(): void
    {
        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => uniqid(),
        ]);

        $registryStub = $this->createStub(FileFormatRegistryInterface::class);
        $registryStub->method('findByExtension')->willReturn(null);

        $contentValidatorSpy = $this->createMock(ContentValidatorInterface::class);
        $contentValidatorSpy->expects($this->never())->method('supports');
        $contentValidatorSpy->expects($this->never())->method('validate');

        $sut = $this->getSut($registryStub, [$contentValidatorSpy]);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateFileNoOpsWhenNoValidatorSupportsTheFormat(): void
    {
        $extension = uniqid();
        $format = new FileFormat($extension, [uniqid()]);

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => $extension,
        ]);

        $registryStub = $this->createStub(FileFormatRegistryInterface::class);
        $registryStub->method('findByExtension')->willReturn($format);

        $unsupportedMock = $this->createMock(ContentValidatorInterface::class);
        $unsupportedMock->expects($this->once())->method('supports')->with($format)->willReturn(false);
        $unsupportedMock->expects($this->never())->method('validate');

        $sut = $this->getSut($registryStub, [$unsupportedMock]);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateFileDispatchesToEverySupportingValidator(): void
    {
        $extension = uniqid();
        $format = new FileFormat($extension, [uniqid()]);

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => $extension,
        ]);

        $registryStub = $this->createStub(FileFormatRegistryInterface::class);
        $registryStub->method('findByExtension')->willReturn($format);

        $supportingMock = $this->createMock(ContentValidatorInterface::class);
        $supportingMock->expects($this->once())->method('supports')->with($format)->willReturn(true);
        $supportingMock->expects($this->once())->method('validate')->with($filePathStub);

        $alsoSupportingMock = $this->createMock(ContentValidatorInterface::class);
        $alsoSupportingMock->expects($this->once())->method('supports')->with($format)->willReturn(true);
        $alsoSupportingMock->expects($this->once())->method('validate')->with($filePathStub);

        $skipMock = $this->createMock(ContentValidatorInterface::class);
        $skipMock->expects($this->once())->method('supports')->with($format)->willReturn(false);
        $skipMock->expects($this->never())->method('validate');

        $sut = $this->getSut($registryStub, [$supportingMock, $skipMock, $alsoSupportingMock]);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateFileLooksUpExtensionFromFilePath(): void
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

        $sut = $this->getSut($registryMock, []);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    /**
     * @param iterable<ContentValidatorInterface> $contentValidators
     */
    private function getSut(
        ?FileFormatRegistryInterface $registry = null,
        iterable $contentValidators = [],
    ): ContentValidatorDispatcher {
        $registry ??= $this->createStub(FileFormatRegistryInterface::class);

        return new ContentValidatorDispatcher(
            registry: $registry,
            contentValidators: $contentValidators,
        );
    }
}
