<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Compatibility\Repository;

use Doctrine\DBAL\ForwardCompatibility\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Exception\MediaNotFoundByFileInformationException;

class PathMappingRepository implements PathMappingRepositoryInterface
{
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getMediaIdByInformation(MediaFileInformationInterface $fileInformation): string
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder->select('m.OXID')
            ->from('ddmedia', 'm')
            ->leftJoin('m', 'ddmedia', 'j', 'j.OXID = m.DDFOLDERID AND m.DDFOLDERID <> ""')
            ->where('m.DDFILENAME = :filename')
            ->andWhere('j.DDFILENAME = :foldername')
            ->setParameter('filename', $fileInformation->getFileName())
            ->setParameter('foldername', $fileInformation->getFolderName());

        /** @var Result $result */
        $result = $queryBuilder->execute();

        if ($mediaId = $result->fetchOne()) {
            return (string)$mediaId;
        }

        throw new MediaNotFoundByFileInformationException();
    }
}
