<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Breadcrumb\Service\BreadcrumbServiceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\FrontendMediaFactoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaServiceInterface;
use OxidEsales\MediaLibrary\Service\FolderServiceInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequestInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;

/**
 * Class MediaController
 */
class MediaController extends AdminDetailsController
{
    protected ?MediaServiceInterface $mediaService = null;

    /**
     * Overrides oxAdminDetails::init()
     */
    public function init()
    {
        parent::init();
        $this->setTemplateName('@ddoemedialibrary/dialog/ddoemedia');

        $this->mediaService = $this->getService(MediaServiceInterface::class);
    }

    /**
     * Overrides oxAdminDetails::render
     *
     * @return string
     */
    public function render()
    {
        $uiRequest = $this->getService(UIRequestInterface::class);
        $imageResource = $this->getService(ImageResourceRefactoredInterface::class);
        $thumbnailResource = $this->getService(ThumbnailResourceInterface::class);

        $folderId = $uiRequest->getFolderId();
        $folderName = '';
        if ($folderId) {
            $folder = $this->mediaService->getMediaById($folderId);
            $folderName = $folder->getFileName();
        }

        $mediaRepository = $this->getService(MediaRepositoryInterface::class);
        $this->addTplParam('iFileCount', $mediaRepository->getFolderMediaCount($folderId));

        $this->addTplParam('sResourceUrl', $imageResource->getUrlToMediaFiles($folderName));
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
    public function upload()
    {
        $uiRequest = $this->getService(UIRequestInterface::class);
        $responseService = $this->getService(ResponseInterface::class);

        $sId = null;
        $sFileName = '';
        $sThumb = '';

        try {
            if ($_FILES) {
                $aAllowedUploadTypes = (array)Registry::getConfig()->getConfigParam('aAllowedUploadTypes');
                $allowedExtensions = array_map("strtolower", $aAllowedUploadTypes);

                $sSourcePath = $_FILES['file']['name'];
                $path_parts = pathinfo($sSourcePath);
                $extension = strtolower($path_parts['extension']);
                if (!in_array($extension, $allowedExtensions)) {
                    header('HTTP/1.1 415 Invalid File Type Upload');
                    $responseService->responseAsJson(['error' => "Invalid file type"]);
                }

                $sFileSize = $_FILES['file']['size'];
                $sFileType = $_FILES['file']['type'];

                $uploadResult = $this->mediaService->upload(
                    uploadedFilePath: $_FILES['file']['tmp_name'],
                    folderId: $uiRequest->getFolderId(),
                    fileName: $_FILES['file']['name']
                );

                $sId = $uploadResult->getOxid();
                $sFileName = $uploadResult->getFileName();
                $sImageSize = $uploadResult->getImageSize()->getInFormat('%dx%d', '');

                $thumbnailService = $this->getService(ThumbnailServiceInterface::class);
                $sThumb = $thumbnailService->ensureAndGetThumbnailUrl(
                    folderName: $uploadResult->getFolderName(),
                    fileName: $uploadResult->getFileName()
                );
            }

            $responseService->responseAsJson([
                'success' => true,
                'id' => $sId,
                'file' => $sFileName,
                'filetype' => $sFileType ?? '',
                'filesize' => $sFileSize ?? '',
                'imagesize' => $sImageSize ?? '',
                'thumb' => $sThumb,
            ]);
        } catch (\Exception $e) {
            $responseService->responseAsJson([
                'success' => false,
                'id' => $sId,
                'errorMessage' => $e->getMessage(),
            ]);
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
     * @return void
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function rename()
    {
        $blReturn = false;
        $sMsg = '';

        $oRequest = Registry::getRequest();

        $sId = $oRequest->getRequestEscapedParameter('id');
        $sNewName = $oRequest->getRequestEscapedParameter('newname');

        if ($sId && $sNewName) {
            $blReturn = true;
            $newMedia = $this->mediaService->rename($sId, $sNewName);
            $sNewName = $newMedia->getFileName();
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson([
            'success' => $blReturn,
            'msg' => $sMsg,
            'name' => $sNewName,
            'id' => $sId
        ]);
    }

    /**
     * Remove file
     */
    public function remove()
    {
        $blReturn = false;
        $sMsg = 'DD_MEDIA_REMOVE_ERR';

        $request = Registry::getRequest();

        $aIDs = $request->getRequestParameter('ids');
        if ($aIDs && count($aIDs)) {
            $this->mediaService->delete($aIDs);
            $blReturn = true;
            $sMsg = '';
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson(['success' => $blReturn, 'msg' => $sMsg]);
    }

    public function movefile()
    {
        $blReturn = false;
        $sMsg = '';

        $oRequest = Registry::getRequest();

        $sSourceFileID = $oRequest->getRequestEscapedParameter('sourceid');
        $sTargetFolderID = $oRequest->getRequestEscapedParameter('targetid');

        if ($sSourceFileID && $sTargetFolderID) {
            $this->mediaService->moveToFolder($sSourceFileID, $sTargetFolderID);
            $blReturn = true;
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson(['success' => $blReturn, 'msg' => $sMsg]);
    }

    /**
     * Load more files
     */
    public function moreFiles()
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
