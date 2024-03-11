<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Codeception\Step;

use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\MediaLibrary\Tests\Codeception\Support\AcceptanceTester;

class MediaLibraryAcceptanceTester extends AcceptanceTester
{
	// @codingStandardsIgnoreStart
    private string $createFolderButton = "//button[contains(@class, 'dd-media-folder-action')]";
    private string $createDirectoryModel = "//div[contains(@class, 'dd-modal-confirm') and contains(@style, 'display: block')]";
    private string $createDirectoryField = "//div[contains(@class, 'dd-modal-confirm')]//input[@name='prompt']";
    private string $directoryTitle = "oxid";
    private string $modelConfirmButton = "//div[contains(@class, 'dd-modal-confirm')]//button[contains(@class, 'btn-primary')]";
    private string $uploadGrid = "//div[contains(@class, 'dz-clickable')]";
    private string $uploadHolder = "//input[@class='dz-hidden-input'][last()]";
    private string $mediaDetails = "//input[contains(@class, 'dd-media-details-input-url')]";
    private string $uploadTab = "//a[@href='#mediaUpload']";
    private string $listTab = "//a[@href='#mediaList']";
    private string $imageSelect = "//div[@class='dd-media-list']//div[contains(@class, 'dz-image-preview')]//a[contains(@class, 'dd-media-item')][1]";
    public string $mediaSearchField = '#mediaSearchField';
    private string $imageWrapper = "//div[@class='dd-media-list']//div[contains(@class, 'dd-media-item-preview')]";
    private string $removeImageButton = "//div[@class='dd-media-list-toolbar']//button[contains(@class, 'dd-media-remove-action')][1]";
    private string $removeImageConfirmButton = "//div[@class='modal-content']//button[contains(@class, 'btn-primary')][1]";
    private string $directorySelector = "//div[@class='dd-media-list']//div[contains(@class, 'dd-media-col')][%d]//a[contains(@class, 'dd-media-item')]";
    private string $removeDirectorySelector = "//div[@class='dd-media-list']//div[contains(@class, 'dd-media-col')][%d]//a[contains(@class, 'dd-media-item') and contains(@class, 'ui-droppable')]";
    private string $directoryLevelUp = "//div[@class='dd-media-list-folder-up']//button[contains(@class, 'dd-media-folder-up-action')]";
    private string $removeDirectoryButton = "//div[@class='dd-media-list-toolbar']//button[contains(@class, 'dd-media-remove-action')][1]";
    private string $removeDirectoryConfirmButton = "//div[@class='modal-content']//button[contains(@class, 'btn-primary')]";
	private string $searchFieldKeyUpScript = "document.querySelector('%s').dispatchEvent(new KeyboardEvent('keyup'));";
	// @codingStandardsIgnoreEnd

    public function openMediaLibrary(): self
    {
        $I = $this;
        $I->selectNavigationFrame();
        $I->retryClick(Translator::translate('mxcustnews'));
        $I->retryClick(Translator::translate('DD_MEDIA_DIALOG'));

        $I->selectBaseFrame();

        $I->see(Translator::translate('DD_MEDIA_DIALOG'));
        $I->see(Translator::translate('DD_MEDIA_LIST'));
        $I->see(Translator::translate('DD_MEDIA_UPLOAD'));

        return $this;
    }

    public function createDirectory(): self
    {
        $I = $this;
        $I->see(Translator::translate('DD_MEDIA_NEW_FOLDER'));

        $I->waitForElement($this->createFolderButton);
        $I->waitForAjax();

        $I->retryClick($this->createFolderButton);
        $I->waitForElement($this->createDirectoryModel);

        $I->fillField($this->createDirectoryField, $this->directoryTitle);
        $I->click($this->modelConfirmButton);

        $I->waitForAjax();

        return $this;
    }

    public function moveInsideDirectory(): self
    {
        $I = $this;
        $locator = sprintf($this->directorySelector, 2);
        $I->doubleClick($locator);
        return $this;
    }

    public function moveOutsideDirectory(): self
    {
        $I = $this;
        $I->click($this->directoryLevelUp);
        $I->waitForAjax();
        return $this;
    }

    public function deleteDirectory(int $directoryNumber): self
    {
        $I = $this;
        $locator = sprintf($this->removeDirectorySelector, $directoryNumber);
        $I->waitForElement($locator);
        $I->click($locator);
        $I->click($this->removeDirectoryButton);
        $I->click($this->removeDirectoryConfirmButton);

        return $this;
    }

    public function switchToUploadTab(): self
    {
        $I = $this;
        $I->waitForElement($this->uploadTab);
        $I->retryClick($this->uploadTab);

        $I->see(strip_tags(Translator::translate('DD_MEDIA_DRAG_INFO')));
        $I->waitForElement($this->uploadGrid);

        return $this;
    }

    public function uploadImage(string $uploadImage): self
    {
        $I = $this;
        $I->attachFile($this->uploadHolder, $uploadImage);
        $I->waitForElement($this->uploadHolder);
        $I->waitForElementVisible($this->mediaDetails);

        return $this;
    }

    public function switchToMediaListTab(): self
    {
        $I = $this;
        $I->waitForElementVisible($this->listTab);
        $I->click($this->listTab);

        return $this;
    }

    public function searchImage(string $imageTitle = ''): self
    {
        $I = $this;
        $I->pressKey($this->mediaSearchField, $imageTitle);
        $I->waitForAjax();

        $script = sprintf($this->searchFieldKeyUpScript, $this->mediaSearchField);
        $I->executeJS($script);

        $I->seeNumberOfElements($this->imageWrapper, 1);

        return $this;
    }

    public function searchReset(): self
    {
        $I = $this;
        $I->fillField($this->mediaSearchField, '');
        $I->pressKey($this->mediaSearchField, '');
        $I->waitForAjax();
        $script = sprintf($this->searchFieldKeyUpScript, $this->mediaSearchField);
        $I->executeJS($script);

        return $this;
    }

    public function deleteImage(): self
    {
        $I = $this;
        $I->waitForElementVisible($this->imageSelect);
        $I->click($this->imageSelect);
        $I->waitForElementVisible($this->mediaDetails);

        $I->click($this->removeImageButton);
        $I->waitForElementVisible($this->removeImageConfirmButton);
        $I->click($this->removeImageConfirmButton);
        $I->waitForElementNotVisible($this->mediaDetails);

        return $this;
    }
}
