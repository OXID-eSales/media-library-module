<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator;

use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\SvgValidatorInterface;
use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormat;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\SvgContentValidator;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

#[CoversClass(SvgContentValidator::class)]
class SvgContentValidatorTest extends TestCase
{
    #[Test]
    public function supportsReturnsTrueForSvgFormat(): void
    {
        $sut = $this->getSut();

        $this->assertTrue($sut->supports(new FileFormat('svg', ['image/svg+xml'])));
    }

    #[DataProvider('nonSvgExtensionProvider')]
    #[Test]
    public function supportsReturnsFalseForNonSvgFormat(string $extension): void
    {
        $sut = $this->getSut();

        $this->assertFalse($sut->supports(new FileFormat($extension, [])));
    }

    public static function nonSvgExtensionProvider(): \Generator
    {
        yield 'png' => ['extension' => 'png'];
        yield 'jpg' => ['extension' => 'jpg'];
        yield 'pdf' => ['extension' => 'pdf'];
        yield 'gif' => ['extension' => 'gif'];
    }

    #[Test]
    public function validateReadsContentAndDelegatesToSvgValidator(): void
    {
        $fileName = uniqid() . '.svg';
        $fileContent = uniqid();
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => $fileContent]);

        $svgValidatorSpy = $this->createMock(SvgValidatorInterface::class);
        $svgValidatorSpy->expects($this->once())
            ->method('validate')
            ->with($fileContent);

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorSpy);
        $sut->validate($filePathStub);
    }

    #[Test]
    public function validatePropagatesValidationException(): void
    {
        $fileName = uniqid() . '.svg';
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => uniqid()]);
        $exceptionMessage = uniqid();

        $svgValidatorStub = $this->createStub(SvgValidatorInterface::class);
        $svgValidatorStub->method('validate')
            ->willThrowException(new ValidationFailedException($exceptionMessage));

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorStub);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $sut->validate($filePathStub);
    }

    #[Test]
    public function validateIgnoresNonUploadedPaths(): void
    {
        $svgValidatorSpy = $this->createMock(SvgValidatorInterface::class);
        $svgValidatorSpy->expects($this->never())->method('validate');

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getFileName' => uniqid() . '.svg',
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorSpy);
        $sut->validate($filePathStub);

        $this->addToAssertionCount(1);
    }

    #[DataProvider('unreadableSvgProvider')]
    #[Test]
    public function validateThrowsWhenSvgContentIsUnusable(?string $fileContent): void
    {
        $fileName = uniqid() . '.svg';
        $vfsContents = $fileContent === null ? [] : [$fileName => $fileContent];
        $vfs = vfsStream::setup(uniqid(), null, $vfsContents);

        $svgValidatorSpy = $this->createMock(SvgValidatorInterface::class);
        $svgValidatorSpy->expects($this->never())->method('validate');

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorSpy);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_FILE_NOT_UPLOADED');

        $sut->validate($filePathStub);
    }

    public static function unreadableSvgProvider(): \Generator
    {
        yield 'file does not exist' => ['fileContent' => null];
        yield 'file exists but is empty' => ['fileContent' => ''];
    }

    #[Test]
    public function validateLogsRejectedSvg(): void
    {
        $fileName = uniqid() . '.svg';
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => uniqid()]);

        $svgValidatorStub = $this->createStub(SvgValidatorInterface::class);
        $svgValidatorStub->method('validate')
            ->willThrowException(new ValidationFailedException(uniqid()));

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Rejected SVG upload'),
                $this->callback(fn(array $context) => ($context['file'] ?? null) === $fileName)
            );

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorStub, logger: $loggerSpy);

        $this->expectException(ValidationFailedException::class);

        $sut->validate($filePathStub);
    }

    protected function getSut(
        ?SvgValidatorInterface $svgValidator = null,
        ?LoggerInterface $logger = null,
    ): SvgContentValidator {
        return new SvgContentValidator(
            svgValidator: $svgValidator ?? $this->createStub(SvgValidatorInterface::class),
            logger: $logger ?? new NullLogger(),
        );
    }
}
