<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Repository;

use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaFactory;
use OxidEsales\LocaleMapper\Service\LocaleMapperInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaFactory::class)]
class MediaFactoryTest extends TestCase
{
    public function testFromDatabaseArray(): void
    {
        $sut = $this->getSut();
        $fileNameValue = 'filenameValue';
        $fileTypeValue = 'filetypeValue';

        $data = [
            'OXID' => 'oxidValue',
            'OXSHOPID' => 2,
            'DDFILENAME' => $fileNameValue,
            'DDFILESIZE' => 123,
            'DDFILETYPE' => $fileTypeValue,
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'OXTIMESTAMP' => '2023-10-30 12:53:10',
            'FOLDERNAME' => 'someFolderName',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertSame('oxidValue', $result->getOxid());
        $this->assertSame($fileNameValue, $result->getFileName());
        $this->assertSame(123, $result->getFileSize());
        $this->assertSame($fileTypeValue, $result->getFileType());
        $this->assertSame('someFolderId', $result->getFolderId());
        $this->assertSame('someFolderName', $result->getFolderName());
        $this->assertSame([], $result->getAltTexts());

        $size = $result->getImageSize();
        $this->assertSame(100, $size->getWidth());
        $this->assertSame(200, $size->getHeight());
    }

    public function testFromDatabaseArrayWithTranslations(): void
    {
        $localeMapperStub = $this->createMock(LocaleMapperInterface::class);
        $localeMapperStub->method('getLocaleIdToLanguageIdMap')
            ->willReturn([
                'localeId1' => 10,
                'localeId2' => 20,
            ]);
        
        $localeMapperStub->method('getLocaleByLanguageId')
            ->willReturnCallback(function($languageId) {
                return match($languageId) {
                    10 => 'de_DE',
                    20 => 'en_EN',
                    default => throw new \Exception('Unknown language ID')
                };
            });
        
        $sut = new MediaFactory($localeMapperStub);

        $data = [
            'OXID' => 'oxidValue',
            'OXSHOPID' => 2,
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'OXTIMESTAMP' => '2023-10-30 12:53:10',
            'FOLDERNAME' => 'someFolderName',
            'TRANSLATIONS' => 'localeId1:German Alt Text|localeId2:English Alt Text',
        ];

        $result = $sut->fromDatabaseArray($data);

        $expectedTranslations = [
            'de_DE' => 'German Alt Text',
            'en_EN' => 'English Alt Text',
        ];

        $this->assertSame($expectedTranslations, $result->getAltTexts());
    }

    public function testFromDatabaseArrayWithEmptyTranslations(): void
    {
        $sut = $this->getSut();

        $data = [
            'OXID' => 'oxidValue',
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'FOLDERNAME' => 'someFolderName',
            'TRANSLATIONS' => '',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertIsArray($result->getAltTexts());
        $this->assertEmpty($result->getAltTexts());
        $this->assertSame([], $result->getAltTexts());
    }

    public function testFromDatabaseArrayWithNullTranslations(): void
    {
        $sut = $this->getSut();

        $data = [
            'OXID' => 'oxidValue',
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'FOLDERNAME' => 'someFolderName',
            // TRANSLATIONS key is missing (null case)
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertIsArray($result->getAltTexts());
        $this->assertEmpty($result->getAltTexts());
        $this->assertSame([], $result->getAltTexts());
    }

    public function testFromDatabaseArrayWithMalformedTranslations(): void
    {
        $localeMapperStub = $this->createMock(LocaleMapperInterface::class);
        $localeMapperStub->method('getLocaleIdToLanguageIdMap')
            ->willReturn([
                'localeId1' => 10,
            ]);
        
        $localeMapperStub->method('getLocaleByLanguageId')
            ->willReturn('de_DE');
        
        $sut = new MediaFactory($localeMapperStub);

        $data = [
            'OXID' => 'oxidValue',
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'FOLDERNAME' => 'someFolderName',
            'TRANSLATIONS' => 'localeId1:Valid Text|invalidpair||localeId1:Another Valid Text',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertIsArray($result->getAltTexts());
        // Should only contain valid translations, malformed pairs should be ignored
        $this->assertSame(['de_DE' => 'Another Valid Text'], $result->getAltTexts());
        
        // Verify array structure
        foreach ($result->getAltTexts() as $locale => $text) {
            $this->assertIsString($locale);
            $this->assertIsString($text);
            $this->assertNotEmpty($locale);
        }
    }

    public function testFromDatabaseArrayWithColonInTranslationText(): void
    {
        $localeMapperStub = $this->createMock(LocaleMapperInterface::class);
        $localeMapperStub->method('getLocaleIdToLanguageIdMap')
            ->willReturn([
                'localeId1' => 10,
            ]);
        
        $localeMapperStub->method('getLocaleByLanguageId')
            ->willReturn('de_DE');
        
        $sut = new MediaFactory($localeMapperStub);

        $data = [
            'OXID' => 'oxidValue',
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'FOLDERNAME' => 'someFolderName',
            'TRANSLATIONS' => 'localeId1:Text with: colons: in it',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertIsArray($result->getAltTexts());
        $this->assertSame(['de_DE' => 'Text with: colons: in it'], $result->getAltTexts());
    }

    public function testFromDatabaseArrayWithEmptyTranslationText(): void
    {
        $localeMapperStub = $this->createMock(LocaleMapperInterface::class);
        $localeMapperStub->method('getLocaleIdToLanguageIdMap')
            ->willReturn([
                'localeId1' => 10,
                'localeId2' => 20,
            ]);
        
        $localeMapperStub->method('getLocaleByLanguageId')
            ->willReturnCallback(function($languageId) {
                return match($languageId) {
                    10 => 'de_DE',
                    20 => 'en_EN',
                };
            });
        
        $sut = new MediaFactory($localeMapperStub);

        $data = [
            'OXID' => 'oxidValue',
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'FOLDERNAME' => 'someFolderName',
            'TRANSLATIONS' => 'localeId1:|localeId2:Valid Text',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertIsArray($result->getAltTexts());
        $expectedTranslations = [
            'de_DE' => '',
            'en_EN' => 'Valid Text',
        ];
        $this->assertSame($expectedTranslations, $result->getAltTexts());
    }

    public function testFromDatabaseArrayWithUnknownLocaleId(): void
    {
        $localeMapperStub = $this->createMock(LocaleMapperInterface::class);
        $localeMapperStub->method('getLocaleIdToLanguageIdMap')
            ->willReturn([
                'knownLocaleId' => 10,
            ]);
        
        $localeMapperStub->method('getLocaleByLanguageId')
            ->willReturn('de_DE');
        
        $sut = new MediaFactory($localeMapperStub);

        $data = [
            'OXID' => 'oxidValue',
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'FOLDERNAME' => 'someFolderName',
            'TRANSLATIONS' => 'unknownLocaleId:Unknown Text|knownLocaleId:Known Text',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertIsArray($result->getAltTexts());
        $expectedTranslations = [
            'de_DE' => 'Known Text', // Unknown locale IDs are skipped
        ];
        $this->assertSame($expectedTranslations, $result->getAltTexts());
    }

    public function testFromDatabaseArrayWithLocaleMapperException(): void
    {
        $localeMapperStub = $this->createMock(LocaleMapperInterface::class);
        $localeMapperStub->method('getLocaleIdToLanguageIdMap')
            ->willReturn([
                'localeId1' => 10,
            ]);
        
        $localeMapperStub->method('getLocaleByLanguageId')
            ->willThrowException(new \Exception('Locale mapping failed'));
        
        $sut = new MediaFactory($localeMapperStub);

        $data = [
            'OXID' => 'oxidValue',
            'DDFILENAME' => 'filenameValue',
            'DDFILESIZE' => 123,
            'DDFILETYPE' => 'filetypeValue',
            'DDIMAGESIZE' => '100x200',
            'DDFOLDERID' => 'someFolderId',
            'FOLDERNAME' => 'someFolderName',
            'TRANSLATIONS' => 'localeId1:Fallback Text',
        ];

        $result = $sut->fromDatabaseArray($data);

        $this->assertIsArray($result->getAltTexts());
        // Should skip alttext when locale resolution fails
        $this->assertSame([], $result->getAltTexts());
    }

    /**
     * Test that altTexts always returns array with string keys and string values
     */
    public function testAltTextsAlwaysReturnsProperFormat(): void
    {
        $sut = $this->getSut();

        $testCases = [
            ['TRANSLATIONS' => ''],
            ['TRANSLATIONS' => 'single:value'],
            [], // missing TRANSLATIONS key
        ];

        foreach ($testCases as $additionalData) {
            $data = array_merge([
                'OXID' => 'oxidValue',
                'DDFILENAME' => 'filenameValue',
                'DDFILESIZE' => 123,
                'DDFILETYPE' => 'filetypeValue',
                'DDIMAGESIZE' => '100x200',
                'DDFOLDERID' => 'someFolderId',
                'FOLDERNAME' => 'someFolderName',
            ], $additionalData);

            $result = $sut->fromDatabaseArray($data);
            $altTexts = $result->getAltTexts();

            // Must always be an array
            $this->assertIsArray($altTexts);
            
            // Must be associative array (not numeric indexed)
            if (!empty($altTexts)) {
                $this->assertTrue(array_keys($altTexts) !== range(0, count($altTexts) - 1));
            }
            
            // All keys and values must be strings
            foreach ($altTexts as $locale => $text) {
                $this->assertIsString($locale, 'Locale key must be string');
                $this->assertIsString($text, 'Alt text value must be string');
            }
        }
    }

    public function getSut(
        ThumbnailResourceInterface $thumbnailResource = null
    ): MediaFactory {
        $localeMapperStub = $this->createConfiguredStub(LocaleMapperInterface::class, [
            'getLocaleIdToLanguageIdMap' => [],
        ]);
        
        return new MediaFactory($localeMapperStub);
    }
}
