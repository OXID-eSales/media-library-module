<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Breadcrumb\Service\BreadcrumbServiceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\FilePath;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\FrontendMediaFactoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaServiceInterface;
use OxidEsales\MediaLibrary\Service\FolderServiceInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequestInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChainInterface;
use OxidEsales\MediaLibrary\Validation\Validator\FileExtensionValidator;

/**
 * Class MediaController
 */
class MediaController extends AdminDetailsController
{
    protected ?MediaServiceInterface $mediaService = null;

    /**
     * Overrides oxAdminDetails::init()
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->setTemplateName('@ddoemedialibrary/dialog/ddoemedia');
    }

    /**
     * Overrides oxAdminDetails::render
     *
     * @return string
     */
    public function render()
    {
        $uiRequest = $this->getService(UIRequestInterface::class);
        $mediaResource = $this->getService(MediaResourceInterface::class);
        $thumbnailResource = $this->getService(ThumbnailResourceInterface::class);
        $mediaService = $this->getService(MediaServiceInterface::class);

        $folderId = $uiRequest->getFolderId();
        $folderName = '';
        if ($folderId) {
            $folder = $mediaService->getMediaById($folderId);
            $folderName = $folder->getFileName();
        }

        $mediaRepository = $this->getService(MediaRepositoryInterface::class);
        $this->addTplParam('iFileCount', $mediaRepository->getFolderMediaCount($folderId));

        $this->addTplParam('sResourceUrl', $mediaResource->getUrlToMediaFiles($folderName));
        $this->addTplParam('sThumbsUrl', $thumbnailResource->getUrlToThumbnailFiles($folderName));
        $this->addTplParam('sFolderId', $folderId);
        $this->addTplParam('sFoldername', $folderName);

        $this->addTplParam('request', $uiRequest);
        $this->addTplParam('sTab', $uiRequest->getTabName());

        return parent::render();
    }

    /**
     * Upload files
     */
    public function upload(): void
    {
        $uiRequest = $this->getService(UIRequestInterface::class);
        $responseService = $this->getService(ResponseInterface::class);
        $fileValidatorChain = $this->getService(UploadedFileValidatorChainInterface::class);
        $thumbnailService = $this->getService(ThumbnailServiceInterface::class);
        $mediaService = $this->getService(MediaServiceInterface::class);

        try {
            $uploadedFile = $uiRequest->getUploadedFile();
            $fileValidatorChain->validateFile($uploadedFile);

            $uploadResult = $mediaService->upload(
                uploadedFilePath: $uploadedFile->getPath(),
                folderId: $uiRequest->getFolderId(),
                fileName: $uploadedFile->getFileName()
            );

            $responseService->responseAsJson([
                'success' => true,
                'id' => $uploadResult->getOxid(),
                'file' => $uploadResult->getFileName(),
                'filetype' => $uploadedFile->getFileType(),
                'filesize' => $uploadedFile->getSize(),
                'imagesize' => $uploadResult->getImageSize()->getInFormat('%dx%d', ''),
                'thumb' => $thumbnailService->ensureAndGetThumbnailUrl(
                    folderName: $uploadResult->getFolderName(),
                    fileName: $uploadResult->getFileName()
                ),
            ]);
        } catch (ValidationFailedException $e) {
            $responseService->errorResponseAsJson(
                code: 415,
                message: $e->getMessage(),
                valueArray: ['error' => $e->getMessage()]
            );
        }
    }

    public function addFolder(): void
    {
        $addFolderRequest = $this->getService(AddFolderRequestInterface::class);

        $responseData = [];
        if ($folderName = $addFolderRequest->getName()) {
            $folderService = $this->getService(FolderServiceInterface::class);
            $newDirectoryInformation = $folderService->createCustomDir($folderName);

            $responseData = [
                'id' => $newDirectoryInformation->getOxid(),
                'name' => $newDirectoryInformation->getFileName()
            ];
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson($responseData);
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function rename(): void
    {
        $oRequest = Registry::getRequest();
        $mediaService = $this->getService(MediaServiceInterface::class);
        $responseService = $this->getService(ResponseInterface::class);

        try {
            $sId = $oRequest->getRequestEscapedParameter('id');
            $sNewName = $oRequest->getRequestEscapedParameter('newname');

            $validator = new FileExtensionValidator();
            $validator->validateFile(new FilePath($sNewName));

            //todo: empty name check through validation
            if ($sId && $sNewName) {
                $newMedia = $mediaService->rename($sId, $sNewName);
                $sNewName = $newMedia->getFileName();
            }

            $responseService->responseAsJson([
                'name' => $sNewName,
                'id' => $sId
            ]);
        } catch (ValidationFailedException $exception) {
            $responseService->errorResponseAsJson(
                code: 400,
                message: $exception->getMessage(),
                valueArray: ['error' => $exception->getMessage()]
            );
        }
    }

    /**
     * Remove file
     */
    public function remove(): void
    {
        $blReturn = false;
        $sMsg = 'DD_MEDIA_REMOVE_ERR';

        $request = Registry::getRequest();
        $mediaService = $this->getService(MediaServiceInterface::class);

        $aIDs = $request->getRequestParameter('ids');
        if ($aIDs && count($aIDs)) {
            $mediaService->delete($aIDs);
            $blReturn = true;
            $sMsg = '';
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson(['success' => $blReturn, 'msg' => $sMsg]);
    }

    public function movefile(): void
    {
        $blReturn = false;
        $sMsg = '';

        $oRequest = Registry::getRequest();
        $mediaService = $this->getService(MediaServiceInterface::class);

        $sSourceFileID = $oRequest->getRequestEscapedParameter('sourceid');
        $sTargetFolderID = $oRequest->getRequestEscapedParameter('targetid');

        if ($sSourceFileID && $sTargetFolderID) {
            $mediaService->moveToFolder($sSourceFileID, $sTargetFolderID);
            $blReturn = true;
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson(['success' => $blReturn, 'msg' => $sMsg]);
    }

    /**
     * Load more files
     */
    public function moreFiles(): void
    {
        $uiRequest = $this->getService(UIRequestInterface::class);

        $pageSize = 18;
        $folderId = $uiRequest->getFolderId();
        $listStartIndex = $uiRequest->getMediaListStartIndex();

        $mediaRepository = $this->getService(MediaRepositoryInterface::class);
        $folderMediaCount = $mediaRepository->getFolderMediaCount($folderId);

        $isThereMoreToLoad = ($listStartIndex + $pageSize < $folderMediaCount);

        $frontendMediaFactory = $this->getService(FrontendMediaFactoryInterface::class);

        $files = [];
        foreach ($mediaRepository->getFolderMedia($folderId, $listStartIndex, $pageSize) as $oneMediaItem) {
            $files[] = $frontendMediaFactory->createFromMedia($oneMediaItem);
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson(['files' => $files, 'more' => $isThereMoreToLoad]);
    }

    public function getBreadcrumb(): array
    {
        $breadcrumbService = $this->getService(BreadcrumbServiceInterface::class);
        return $breadcrumbService->getBreadcrumbsByRequest();
    }
}
