<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Service;

use OxidEsales\MediaLibrary\Validation\Exception\ChainInputTypeException;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Service\FileValidatorChain;
use OxidEsales\MediaLibrary\Validation\Validator\FileValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Validation\Service\FileValidatorChain
 */
class FileValidatorChainTest extends TestCase
{
    public function testConstructorDoesNotAcceptWrongType(): void
    {
        $this->expectException(ChainInputTypeException::class);
        new FileValidatorChain([new \stdClass()]);
    }

    public function testValidateFileWorksIfNoExceptionsThrown(): void
    {
        $validatorStub = $this->createStub(FileValidatorInterface::class);
        $exampleFilePath = uniqid();

        $sut = new FileValidatorChain([$validatorStub]);
        $sut->validateFile($exampleFilePath);

        $this->addToAssertionCount(1);
    }

    public function testExceptionOnValidatorException(): void
    {
        $validatorStub = $this->createMock(FileValidatorInterface::class);
        $validatorStub->method('validateFile')->willThrowException(new ValidationFailedException());
        $exampleFilePath = uniqid();

        $this->expectException(ValidationFailedException::class);

        $sut = new FileValidatorChain([$validatorStub]);
        $sut->validateFile($exampleFilePath);
    }
}
