<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Service;

use OxidEsales\MediaLibrary\Validation\Exception\ChainInputTypeException;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Service\DocumentNameValidatorChain;
use OxidEsales\MediaLibrary\Validation\Service\DocumentNameValidatorChainInterface;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentNameValidatorChain::class)]
class FileNameValidatorChainTest extends TestCase
{
    public function testConstructorDoesNotAcceptWrongType(): void
    {
        $this->expectException(ChainInputTypeException::class);
        $this->getSut(
            fileValidators: [new \stdClass()],
        );
    }

    public function testValidateFileWorksIfNoExceptionsThrown(): void
    {
        $validatorStub = $this->createStub(FilePathValidatorInterface::class);

        $sut = $this->getSut(
            fileValidators: [$validatorStub],
        );
        $sut->validateDocumentName(uniqid());

        $this->addToAssertionCount(1);
    }

    public function testExceptionOnValidatorException(): void
    {
        $fileName = uniqid();

        $validatorMock = $this->createMock(FilePathValidatorInterface::class);
        $validatorMock->method('validateFile')->willThrowException(new ValidationFailedException());

        $this->expectException(ValidationFailedException::class);

        $sut = $this->getSut(
            fileValidators: [$validatorMock],
        );
        $sut->validateDocumentName($fileName);
    }

    private function getSut(
        ?iterable $fileValidators = null,
    ): DocumentNameValidatorChainInterface {
        $fileValidators ??= [];

        return new DocumentNameValidatorChain(
            fileValidators: $fileValidators,
        );
    }
}
