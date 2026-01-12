<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ForwardCompatibility\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;

class PreloadMediaRepository implements PreloadMediaRepositoryInterface
{
    /** @var array<string> */
    protected array $idsToPreload = [];

    /** @var array<string, MediaInterface> */
    protected array $preloadedMedia = [];

    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
        private readonly MediaFactoryInterface $mediaFactory,
        private readonly LanguageInterface $language,
    ) {
    }

    public function registerForPreload(string ...$mediaIds): void
    {
        foreach ($mediaIds as $oneMediaId) {
            $this->registerOneMediaIdForPreload($oneMediaId);
        }
    }

    private function registerOneMediaIdForPreload(string $oneMediaId): void
    {
        if (!in_array($oneMediaId, $this->idsToPreload) && !isset($this->preloadedMedia[$oneMediaId])) {
            $this->idsToPreload[] = $oneMediaId;
        }
    }

    /**
     * @inheritDoc
     */
    public function getMediaById(string $mediaId): MediaInterface
    {
        $this->registerForPreload($mediaId);
        $this->preload();

        return $this->getPreloadedMedia($mediaId);
    }

    private function preload(): void
    {
        if (empty($this->idsToPreload)) {
            return;
        }

        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('m.*', 'j.DDFILENAME as FOLDERNAME', 't.OXALTSHORTTEXT')
            ->from('ddmedia', 'm')
            ->leftJoin('m', 'ddmedia', 'j', "j.OXID = m.DDFOLDERID AND m.DDFOLDERID <> ''")
            ->leftJoin('m', 'ddmedia_translations', 't', 't.OXOBJECTID = m.OXID AND t.OXLANGUAGEID = :OXLANGUAGEID')
            ->where('m.OXID IN (:OXIDLIST)')
            ->setParameter('OXLANGUAGEID', $this->language->getBaseLanguage())
            ->setParameter('OXIDLIST', $this->idsToPreload, Connection::PARAM_STR_ARRAY);

        /** @var Result $result */
        $result = $queryBuilder->execute();

        $this->idsToPreload = [];

        while ($data = $result->fetchAssociative()) {
            $this->preloadedMedia[(string)$data['OXID']] = $this->mediaFactory->fromDatabaseArray($data);
        }
    }

    private function getPreloadedMedia(string $mediaId): MediaInterface
    {
        if (!isset($this->preloadedMedia[$mediaId])) {
            throw new MediaNotFoundException(sprintf('Media with id "%s" not found.', $mediaId));
        }

        return $this->preloadedMedia[$mediaId];
    }
}
