<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use Doctrine\DBAL\ForwardCompatibility\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaAltText;
use OxidEsales\MediaLibrary\Media\DataType\MediaAltTextInterface;

class MediaAltRepository implements MediaAltRepositoryInterface
{
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
    ) {
    }

    public function saveAltText(MediaAltTextInterface $mediaAltText): void
    {
        $sql = 'INSERT INTO ddmedia_translations (OXOBJECTID, OXLANGUAGEID, OXALTSHORTTEXT) '
            . 'VALUES (:objectId, :languageId, :altText) '
            . 'ON DUPLICATE KEY UPDATE OXALTSHORTTEXT = :altText, OXTIMESTAMP = CURRENT_TIMESTAMP';
        $this->queryBuilderFactory->create()->getConnection()->executeStatement(
            $sql,
            [
                'objectId' => $mediaAltText->getObjectId(),
                'languageId' => $mediaAltText->getLanguageId(),
                'altText' => $mediaAltText->getText(),
            ]
        );
    }

    public function getObjectAltTexts(string $objectId): array
    {
        $qb = $this->queryBuilderFactory->create();
        $qb->select('OXOBJECTID', 'OXLANGUAGEID', 'OXALTSHORTTEXT')
            ->from('ddmedia_translations')
            ->where('OXOBJECTID = :objectId')
            ->setParameter('objectId', $objectId);

        /** @var Result<mixed> $result */
        $result = $qb->execute();

        $altTexts = [];
        while ($row = $result->fetchAssociative()) {
            $altTexts[] = new MediaAltText(
                $row['OXOBJECTID'],
                (int)$row['OXLANGUAGEID'],
                $row['OXALTSHORTTEXT']
            );
        }
        return $altTexts;
    }

    public function deleteMediaAltTexts(string $mediaId): void
    {
        $qb = $this->queryBuilderFactory->create();
        $qb->delete('ddmedia_translations')
            ->where('OXOBJECTID = :mediaId')
            ->setParameter('mediaId', $mediaId)
            ->execute();
    }
}
