<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Controller;

use OxidEsales\EshopCommunity\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Media\Factory\MediaAltTextFactoryInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaAltRepositoryInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;

class MediaAltTextController extends AdminDetailsController
{
    public function __construct(
        private readonly MediaAltRepositoryInterface $mediaAltRepository,
        private readonly MediaAltTextFactoryInterface $mediaAltTextFactory,
        private readonly RequestInterface $request,
        private readonly ResponseInterface $response,
        private readonly ShopAdapterInterface $shopAdapter,
    ) {
        parent::__construct();
    }

    public function getAltTexts(): void
    {
        $objectId = $this->request->get('objectId');
        $altTexts = $this->mediaAltRepository->getObjectAltTexts($objectId);
        $result = [];
        foreach ($altTexts as $altText) {
            $result[$altText->getLanguageId()] = $altText->getText();
        }

        $this->response->responseAsJson(['success' => true, 'altTexts' => $result]);
    }

    public function saveAltText(): void
    {
        $objectId = $this->request->get('objectId');
        $altTexts = $this->request->get('altTexts');
        foreach ($altTexts as $languageId => $altText) {
            $mediaAltText = $this->mediaAltTextFactory->create($objectId, (int)$languageId, $altText);
            $this->mediaAltRepository->saveAltText($mediaAltText);
        }
        $successMsg = $this->shopAdapter->translateString('DD_MEDIA_ALT_TEXT_SAVE_SUCCESS');
        $this->response->responseAsJson(['success' => true, 'message' => $successMsg]);
    }
}
