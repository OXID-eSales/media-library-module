<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Breadcrumb\Service\BreadcrumbServiceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Service\Media;
use OxidEsales\MediaLibrary\Transput\Request\UIRequestInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use Symfony\Component\Filesystem\Path;

/**
 * Class MediaController
 */
class MediaController extends AdminDetailsController
{
    protected UIRequestInterface $request;
    protected ?Media $mediaService = null;
    protected ?ImageResourceInterface $imageResource = null;

    /**
     * Overrides oxAdminDetails::init()
     */
    public function init()
    {
        parent::init();
        $this->setTemplateName('@ddoemedialibrary/dialog/ddoemedia');

        $this->request = $this->getService(UIRequestInterface::class);
        $this->mediaService = $this->getService(Media::class);
        $this->imageResource = $this->getService(ImageResourceInterface::class);

        if (Registry::getRequest()->getRequestEscapedParameter('folderid')) {
            $this->imageResource->setFolder(Registry::getRequest()->getRequestEscapedParameter('folderid'));
        }
    }

    /**
     * Overrides oxAdminDetails::render
     *
     * @return string
     */
    public function render()
    {
        $request = Registry::getRequest();

        $mediaRepository = $this->getService(MediaRepositoryInterface::class);
        $this->addTplParam('iFileCount', $mediaRepository->getFolderMediaCount($this->request->getFolderId()));

        $this->addTplParam('sResourceUrl', $this->imageResource->getMediaUrl());
        $this->addTplParam('sThumbsUrl', $this->imageResource->getThumbnailUrl());
        $this->addTplParam('sFoldername', $this->imageResource->getFolderName());
        $this->addTplParam('sFolderId', $this->imageResource->getFolderId());
        $this->addTplParam('sTab', $request->getRequestEscapedParameter('tab'));
        $this->addTplParam('request', $this->getService(UIRequestInterface::class));

        return parent::render();
    }

    /**
     * Upload files
     */
    public function upload()
    {
        $request = Registry::getRequest();
        $responseService = $this->getService(ResponseInterface::class);

        $sId = null;
        $sFileName = '';
        $sThumb = '';

        try {
            if ($_FILES) {
                $aAllowedUploadTypes = (array) Registry::getConfig()->getConfigParam('aAllowedUploadTypes');
                $allowedExtensions = array_map("strtolower", $aAllowedUploadTypes);

                $sSourcePath = $_FILES['file']['name'];
                $path_parts = pathinfo($sSourcePath);
                $extension = strtolower($path_parts['extension']);
                if (!in_array($extension, $allowedExtensions)) {
                    header('HTTP/1.1 415 Invalid File Type Upload');
                    $responseService->responseAsJson(['error' => "Invalid file type"]);
                }

                $this->mediaService->createDirs();

                $sFileSize = $_FILES['file']['size'];
                $sFileType = $_FILES['file']['type'];

                $sSourcePath = $_FILES['file']['tmp_name'];
                $sDestPath = Path::join($this->imageResource->getMediaPath(), $_FILES['file']['name']);

                $aResult = $this->mediaService->uploadMedia($sSourcePath, $sDestPath, $sFileSize, $sFileType, true);
                $sId = $aResult['id'];
                $sFileName = $aResult['filename'];
                $sImageSize = $aResult['imagesize'];
                $sThumb = $aResult['thumb'];
            }

            $responseService->responseAsJson([
                'success'   => true,
                'id'        => $sId,
                'file'      => $sFileName ?? '',
                'filetype'  => $sFileType ?? '',
                'filesize'  => $sFileSize ?? '',
                'imagesize' => $sImageSize ?? '',
                'thumb'     => $sThumb ?? '',
            ]);
        } catch (\Exception $e) {
            $responseService->responseAsJson([
                'success'      => false,
                'id'           => $sId,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return void
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function addFolder()
    {
        $oRequest = Registry::getRequest();

        if (($sName = $oRequest->getRequestEscapedParameter('name'))) {
            $aCustomDir = $this->mediaService->createCustomDir($sName);

            // todo: catch exception and return appropriate result

            $responseData = [
                'success'   => true,
                'id'        => $aCustomDir['id'],
                'file'      => $aCustomDir['dir'],
                'filetype'  => 'directory',
                'filesize'  => 0,
                'imagesize' => '',
            ];
        } else {
            $responseData = ['success' => false];
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

        $sNewId = $sId = $oRequest->getRequestEscapedParameter('id');
        $sOldName = $oRequest->getRequestEscapedParameter('oldname');
        $sNewName = $oRequest->getRequestEscapedParameter('newname');
        $sFiletype = $oRequest->getRequestEscapedParameter('filetype');

        if ($sId && $sOldName && $sNewName) {
            $aResult = $this->mediaService->rename(
                $sOldName,
                $sNewName,
                $sId,
                $sFiletype
            );
            $blReturn = $aResult['success'];
            $sNewName = $aResult['filename'];
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson([
            'success' => $blReturn,
            'msg'     => $sMsg,
            'name'    => $sNewName,
            'id'      => $sNewId,
            'thumb'   => $this->imageResource->getThumbnailUrl($sNewName),
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
        $sFileName = $oRequest->getRequestEscapedParameter('file');
        $sTargetFolderID = $oRequest->getRequestEscapedParameter('targetid');
        $sTargetFolderName = $oRequest->getRequestEscapedParameter('folder');

        if ($sSourceFileID && $sFileName && $sTargetFolderID && $sTargetFolderName) {
            if ($this->mediaService->moveFileToFolder($sSourceFileID, $sTargetFolderID)) {
                $blReturn = true;
            } else {
                $sMsg = 'DD_MEDIA_MOVE_FILE_ERR';
            }
        }

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson(['success' => $blReturn, 'msg' => $sMsg]);
    }

    /**
     * Load more files
     */
    public function moreFiles()
    {
        $pageSize = 18;
        $folderId = $this->request->getFolderId();
        $listStartIndex = $this->request->getMediaListStartIndex();

        $mediaRepository = $this->getService(MediaRepositoryInterface::class);
        $folderMediaCount = $mediaRepository->getFolderMediaCount($folderId);

        $isThereMoreToLoad = ($listStartIndex + $pageSize < $folderMediaCount);

        $files = array_map(
            fn($item) => $item->getFrontendData(),
            $mediaRepository->getFolderMedia($folderId, $listStartIndex, $pageSize)
        );

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson(['files' => $files, 'more' => $isThereMoreToLoad]);
    }

    public function getBreadcrumb(): array
    {
        $breadcrumbService = $this->getService(BreadcrumbServiceInterface::class);
        return $breadcrumbService->getBreadcrumbsByRequest();
    }
}
