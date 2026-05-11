<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\ContentValidatorInterface;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\RasterImageContentValidator;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RasterImageContentValidator::class)]
class RasterImageContentValidatorTest extends TestCase
{
    #[DataProvider('rasterExtensionProvider')]
    #[Test]
    public function supportsReturnsTrueForRasterExtension(string $extension): void
    {
        $formatStub = $this->createConfiguredStub(FileFormatInterface::class, [
            'getExtension' => $extension,
        ]);

        $sut = $this->getSut();
        $isSupported = $sut->supports($formatStub);

        $this->assertTrue($isSupported);
    }

    public static function rasterExtensionProvider(): \Generator
    {
        yield 'jpg' => ['extension' => 'jpg'];
        yield 'jpeg' => ['extension' => 'jpeg'];
        yield 'gif' => ['extension' => 'gif'];
        yield 'png' => ['extension' => 'png'];
        yield 'webp' => ['extension' => 'webp'];
        yield 'avif' => ['extension' => 'avif'];
    }

    #[DataProvider('nonRasterExtensionProvider')]
    #[Test]
    public function supportsReturnsFalseForNonRasterExtension(string $extension): void
    {
        $formatStub = $this->createConfiguredStub(FileFormatInterface::class, [
            'getExtension' => $extension,
        ]);

        $sut = $this->getSut();
        $isSupported = $sut->supports($formatStub);

        $this->assertFalse($isSupported);
    }

    public static function nonRasterExtensionProvider(): \Generator
    {
        yield 'svg' => ['extension' => 'svg'];
        yield 'pdf' => ['extension' => 'pdf'];
        yield 'zip' => ['extension' => 'zip'];
        yield 'doc' => ['extension' => 'doc'];
    }

    #[Test]
    public function validatePassesForGenuinePngBytes(): void
    {
        $fileName = uniqid() . '.png';
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => $this->genuinePngBytes()]);

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getPath' => $vfs->url() . '/' . $fileName,
            'getFileName' => $fileName,
        ]);

        $sut = $this->getSut();

        $this->expectNotToPerformAssertions();
        $sut->validate($filePathStub);
    }

    #[Test]
    public function validateThrowsWhenContentIsNotARecognizableImage(): void
    {
        $fileName = uniqid() . '.png';
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => '<?php phpinfo(); ?>']);

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getPath' => $vfs->url() . '/' . $fileName,
            'getFileName' => $fileName,
        ]);

        $sut = $this->getSut();

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_INVALID_IMAGE_CONTENT');

        $sut->validate($filePathStub);
    }

    #[Test]
    public function validateThrowsWhenPolyglotPngHasNonImageBytesAppended(): void
    {
        $fileName = uniqid() . '.broken.png';
        $genuineBytes = $this->genuinePngBytes();
        // Keep only the PNG signature so getimagesize() rejects the file as not parseable.
        $truncatedBytes = substr($genuineBytes, 0, 8) . random_bytes(16);
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => $truncatedBytes]);

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getPath' => $vfs->url() . '/' . $fileName,
            'getFileName' => $fileName,
        ]);

        $sut = $this->getSut();

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_INVALID_IMAGE_CONTENT');

        $sut->validate($filePathStub);
    }

    private function genuinePngBytes(): string
    {
        $image = imagecreatetruecolor(4, 4);
        ob_start();
        imagepng($image);
        $bytes = (string)ob_get_clean();
        imagedestroy($image);
        return $bytes;
    }

    private function getSut(): ContentValidatorInterface
    {
        return new RasterImageContentValidator();
    }
}
