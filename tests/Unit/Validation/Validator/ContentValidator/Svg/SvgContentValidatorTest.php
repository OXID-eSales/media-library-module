<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator\Svg;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormat;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\SvgContentValidator;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\SvgScannerInterface;
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
    public function validateReadsContentAndDelegatesToSvgScanner(): void
    {
        $fileName = uniqid() . '.svg';
        $fileContent = uniqid();
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => $fileContent]);

        $svgScannerSpy = $this->createMock(SvgScannerInterface::class);
        $svgScannerSpy->expects($this->once())
            ->method('scan')
            ->with($fileContent);

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgScanner: $svgScannerSpy);
        $sut->validate($filePathStub);
    }

    #[Test]
    public function validatePropagatesValidationException(): void
    {
        $fileName = uniqid() . '.svg';
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => uniqid()]);
        $exceptionMessage = uniqid();

        $svgScannerStub = $this->createStub(SvgScannerInterface::class);
        $svgScannerStub->method('scan')
            ->willThrowException(new ValidationFailedException($exceptionMessage));

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgScanner: $svgScannerStub);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $sut->validate($filePathStub);
    }

    #[Test]
    public function validateIgnoresNonUploadedPaths(): void
    {
        $svgScannerSpy = $this->createMock(SvgScannerInterface::class);
        $svgScannerSpy->expects($this->never())->method('scan');

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getFileName' => uniqid() . '.svg',
        ]);

        $sut = $this->getSut(svgScanner: $svgScannerSpy);
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

        $svgScannerSpy = $this->createMock(SvgScannerInterface::class);
        $svgScannerSpy->expects($this->never())->method('scan');

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgScanner: $svgScannerSpy);

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

        $svgScannerStub = $this->createStub(SvgScannerInterface::class);
        $svgScannerStub->method('scan')
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

        $sut = $this->getSut(svgScanner: $svgScannerStub, logger: $loggerSpy);

        $this->expectException(ValidationFailedException::class);

        $sut->validate($filePathStub);
    }

    protected function getSut(
        ?SvgScannerInterface $svgScanner = null,
        ?LoggerInterface $logger = null,
    ): SvgContentValidator {
        return new SvgContentValidator(
            svgScanner: $svgScanner ?? $this->createStub(SvgScannerInterface::class),
            logger: $logger ?? new NullLogger(),
        );
    }
}
