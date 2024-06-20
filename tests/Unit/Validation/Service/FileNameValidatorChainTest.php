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
use OxidEsales\MediaLibrary\Validation\Service\DocumentNameValidatorChain;
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChain;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Validation\Service\DocumentNameValidatorChain
 */
class FileNameValidatorChainTest extends TestCase
{
    public function testConstructorDoesNotAcceptWrongType(): void
    {
        $this->expectException(ChainInputTypeException::class);
        new DocumentNameValidatorChain([new \stdClass()]);
    }

    public function testValidateFileWorksIfNoExceptionsThrown(): void
    {
        $validatorStub = $this->createStub(FilePathValidatorInterface::class);

        $sut = new DocumentNameValidatorChain([$validatorStub]);
        $sut->validateDocumentName(uniqid());

        $this->addToAssertionCount(1);
    }

    public function testExceptionOnValidatorException(): void
    {
        $fileName = uniqid();

        $validatorMock = $this->createMock(FilePathValidatorInterface::class);
        $validatorMock->method('validateFile')->willThrowException(new ValidationFailedException());

        $this->expectException(ValidationFailedException::class);

        $sut = new DocumentNameValidatorChain([$validatorMock]);
        $sut->validateDocumentName($fileName);
    }
}
