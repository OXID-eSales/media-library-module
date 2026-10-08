<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Service;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ChainInputTypeException;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChain;
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChainInterface;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UploadedFileValidatorChain::class)]
class UploadedFileValidatorChainTest extends TestCase
{
    #[Test]
    public function constructorDoesNotAcceptWrongType(): void
    {
        $this->expectException(ChainInputTypeException::class);
        $this->getSut(
            fileValidators: [new \stdClass()],
        );
    }

    #[Test]
    public function validateFilePassesIfNoValidatorThrows(): void
    {
        $fileStub = $this->createStub(UploadedFileInterface::class);
        $validatorStub = $this->createStub(FilePathValidatorInterface::class);

        $sut = $this->getSut(
            fileValidators: [$validatorStub],
        );
        $sut->validateFile($fileStub);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateFileRethrowsValidatorException(): void
    {
        $fileStub = $this->createStub(UploadedFileInterface::class);

        $validatorStub = $this->createMock(FilePathValidatorInterface::class);
        $validatorStub->method('validateFile')->willThrowException(new ValidationFailedException());

        $this->expectException(ValidationFailedException::class);

        $sut = $this->getSut(
            fileValidators: [$validatorStub],
        );
        $sut->validateFile($fileStub);
    }

    private function getSut(
        ?iterable $fileValidators = null,
    ): UploadedFileValidatorChainInterface {
        $fileValidators ??= [];

        return new UploadedFileValidatorChain(
            fileValidators: $fileValidators,
        );
    }
}
