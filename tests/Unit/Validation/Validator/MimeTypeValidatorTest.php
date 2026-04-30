<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormat;
use OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistryInterface;
use OxidEsales\MediaLibrary\Validation\Validator\MimeTypeValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MimeTypeValidator::class)]
class MimeTypeValidatorTest extends TestCase
{
    #[Test]
    public function validateFilePassesWhenSniffedMimeIsAllowed(): void
    {
        $extension = uniqid();
        $allowedMimeType = uniqid() . '/' . uniqid();

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => $extension,
            'getPath' => uniqid(),
        ]);

        $registryStub = $this->createStub(FileFormatRegistryInterface::class);
        $registryStub->method('findByExtension')
            ->willReturn(new FileFormat($extension, [$allowedMimeType]));

        $fileSystemServiceStub = $this->createStub(FileSystemServiceInterface::class);
        $fileSystemServiceStub->method('getMimeType')->willReturn($allowedMimeType);

        $sut = $this->getSut($registryStub, $fileSystemServiceStub);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateFileNoOpsWhenExtensionIsNotInRegistry(): void
    {
        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => uniqid(),
            'getPath' => uniqid(),
        ]);

        $registryStub = $this->createStub(FileFormatRegistryInterface::class);
        $registryStub->method('findByExtension')->willReturn(null);

        $fileSystemServiceSpy = $this->createMock(FileSystemServiceInterface::class);
        $fileSystemServiceSpy->expects($this->never())->method('getMimeType');

        $sut = $this->getSut($registryStub, $fileSystemServiceSpy);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidSniffedMimeProvider')]
    #[Test]
    public function validateFileThrowsWhenSniffedMimeIsInvalid(string $sniffedMimeType): void
    {
        $extension = uniqid();

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => $extension,
            'getPath' => uniqid(),
        ]);

        $registryStub = $this->createStub(FileFormatRegistryInterface::class);
        $registryStub->method('findByExtension')
            ->willReturn(new FileFormat($extension, [uniqid() . '/' . uniqid()]));

        $fileSystemServiceStub = $this->createStub(FileSystemServiceInterface::class);
        $fileSystemServiceStub->method('getMimeType')->willReturn($sniffedMimeType);

        $sut = $this->getSut($registryStub, $fileSystemServiceStub);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_INVALID_FILE_MIME');

        $sut->validateFile($filePathStub);
    }

    public static function invalidSniffedMimeProvider(): \Generator
    {
        yield 'sniffed MIME does not match allowed list' => [
            'sniffedMimeType' => uniqid() . '/' . uniqid(),
        ];
        yield 'sniff returned empty (failed/missing file)' => [
            'sniffedMimeType' => '',
        ];
    }

    #[Test]
    public function validateFileLooksUpExtensionFromFilePath(): void
    {
        $extension = uniqid();
        $allowedMimeType = uniqid() . '/' . uniqid();

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getExtension' => $extension,
            'getPath' => uniqid(),
        ]);

        $registryMock = $this->createMock(FileFormatRegistryInterface::class);
        $registryMock->expects($this->once())
            ->method('findByExtension')
            ->with($extension)
            ->willReturn(new FileFormat($extension, [$allowedMimeType]));

        $fileSystemServiceStub = $this->createStub(FileSystemServiceInterface::class);
        $fileSystemServiceStub->method('getMimeType')->willReturn($allowedMimeType);

        $sut = $this->getSut($registryMock, $fileSystemServiceStub);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    private function getSut(
        ?FileFormatRegistryInterface $registry = null,
        ?FileSystemServiceInterface $fileSystemService = null,
    ): MimeTypeValidator {
        $registry ??= $this->createStub(FileFormatRegistryInterface::class);
        $fileSystemService ??= $this->createStub(FileSystemServiceInterface::class);

        return new MimeTypeValidator(
            registry: $registry,
            fileSystemService: $fileSystemService,
        );
    }
}
