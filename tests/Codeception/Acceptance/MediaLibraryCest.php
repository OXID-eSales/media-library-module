<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Codeception\Acceptance;

use OxidEsales\MediaLibrary\Tests\Codeception\Step\MediaLibraryAcceptanceTester;

/**
 * @group ddoe_medialibrary
 */
final class MediaLibraryCest
{
    private string $testImage = 'dummy.png';
    private string $searchImage = 'dummy';
    private const TEST_IMAGES = [
        'dummy.png',
        'sample.png'
    ];

    public function testMediaLibraryAvailable(MediaLibraryAcceptanceTester $I): void
    {
        $I->wantToTest('Media library available and accessible');

        $I->loginAdmin();

        $I->openMediaLibrary();
    }

    public function testCreationAndRemovalOfDirectory(MediaLibraryAcceptanceTester $I): void
    {
        $I->wantToTest('Creation and remove directory.');

        $I->loginAdmin();

        $I->openMediaLibrary()
        ->createDirectory()
        ->deleteDirectory(2);
    }

    public function testUploadAndDeleteImageMainDirectory(MediaLibraryAcceptanceTester $I): void
    {
        $I->wantToTest('Upload and delete image in main directory');

        $I->loginAdmin();

        $I->openMediaLibrary()
            ->switchToUploadTab()
            ->uploadImage($this->testImage)
            ->switchToMediaListTab()
            ->deleteImage();
    }

    public function testUplaodAndSearchFunctionality(MediaLibraryAcceptanceTester $I): void
    {
        $I->wantToTest('Upload multiple image and test search functionality');

        $I->loginAdmin();

        $I->openMediaLibrary()
            ->switchToUploadTab();

        foreach ($this::TEST_IMAGES as $uploadImage) {
            $I->uploadImage($uploadImage);
        }

        $I->searchImage($this->searchImage)
            ->searchReset();

        for ($i = 0; $i < count($this::TEST_IMAGES); $i++) {
            $I->deleteImage();
        }
    }

    public function testUploadAndDeleteImageSubDirectory(MediaLibraryAcceptanceTester $I): void
    {
        $I->wantToTest('Upload and delete image in sub directory');

        $I->loginAdmin();

        $I->openMediaLibrary()
            ->createDirectory()
            ->moveInsideDirectory()
            ->switchToUploadTab()
            ->uploadImage($this->testImage)
            ->switchToMediaListTab()
            ->deleteImage()
            ->moveOutsideDirectory()
            ->deleteDirectory(1);
    }
}
