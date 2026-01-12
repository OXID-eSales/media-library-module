<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ForwardCompatibility\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Exception\WrongMediaIdGivenException;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;

readonly class MediaRepository implements MediaRepositoryInterface
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
        private ContextInterface $context,
        private MediaFactoryInterface $mediaFactory,
        private LanguageInterface $language,
        private MediaAltRepositoryInterface $mediaAltRepository,
    ) {
    }

    public function getFolderMediaCount(string $folderId): int
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('count(*)')
            ->from('ddmedia')
            ->where('OXSHOPID = :OXSHOPID')
            ->andWhere('DDFOLDERID = :DDFOLDERID')
            ->setParameters([
                'OXSHOPID' => $this->context->getCurrentShopId(),
                'DDFOLDERID' => $folderId,
            ]);

        /** @var Result $result */
        $result = $queryBuilder->execute();
        return (int)$result->fetchOne();
    }

    public function getFolderMedia(string $folderId, int $start, int $limit = 18): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('m.*', 'j.DDFILENAME as FOLDERNAME', 't.OXALTSHORTTEXT')
            ->from('ddmedia', 'm')
            ->leftJoin('m', 'ddmedia', 'j', "j.OXID = m.DDFOLDERID AND m.DDFOLDERID <> ''")
            ->leftJoin('m', 'ddmedia_translations', 't', 't.OXOBJECTID = m.OXID AND t.OXLANGUAGEID = :OXLANGUAGEID')
            ->where('m.OXSHOPID = :OXSHOPID')
            ->andWhere('m.DDFOLDERID = :DDFOLDERID')
            ->orderBy('m.OXTIMESTAMP', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->setParameters([
                'OXSHOPID' => $this->context->getCurrentShopId(),
                'DDFOLDERID' => $folderId,
                'OXLANGUAGEID' => $this->language->getBaseLanguage(),
            ]);

        /** @var Result $queryResult */
        $queryResult = $queryBuilder->execute();

        $result = [];
        while ($data = $queryResult->fetchAssociative()) {
            $result[] = $this->mediaFactory->fromDatabaseArray($data);
        }

        return $result;
    }

    public function getMediaById(string $mediaId): MediaInterface
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('m.*', 'j.DDFILENAME as FOLDERNAME', 't.OXALTSHORTTEXT')
            ->from('ddmedia', 'm')
            ->leftJoin('m', 'ddmedia', 'j', "j.OXID = m.DDFOLDERID AND m.DDFOLDERID <> ''")
            ->leftJoin('m', 'ddmedia_translations', 't', 't.OXOBJECTID = m.OXID AND t.OXLANGUAGEID = :OXLANGUAGEID')
            ->where('m.OXID = :OXID')
            ->setParameters([
                'OXID' => $mediaId,
                'OXLANGUAGEID' => $this->language->getBaseLanguage(),
            ]);

        /** @var Result $queryResult */
        $queryResult = $queryBuilder->execute();
        $data = $queryResult->fetchAssociative();
        if ($data) {
            return $this->mediaFactory->fromDatabaseArray($data);
        }

        throw new MediaNotFoundException();
    }

    public function addMedia(MediaInterface $exampleMedia): void
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->insert('ddmedia')
            ->values([
                'OXID' => ':OXID',
                'OXSHOPID' => ':OXSHOPID',
                'DDFILENAME' => ':DDFILENAME',
                'DDFILESIZE' => ':DDFILESIZE',
                'DDFILETYPE' => ':DDFILETYPE',
                'DDIMAGESIZE' => ':DDIMAGESIZE',
                'DDFOLDERID' => ':DDFOLDERID',
            ])
            ->setParameters([
                'OXID' => $exampleMedia->getOxid(),
                'OXSHOPID' => $this->context->getCurrentShopId(),
                'DDFILENAME' => $exampleMedia->getFileName(),
                'DDFILESIZE' => $exampleMedia->getFileSize(),
                'DDFILETYPE' => $exampleMedia->getFileType(),
                'DDIMAGESIZE' => $exampleMedia->getImageSize()->getInFormat("%dx%d", ""),
                'DDFOLDERID' => $exampleMedia->getFolderId(),
            ]);

        $queryBuilder->execute();
    }

    /**
     * @throws Exception
     */
    public function renameMedia(string $mediaIdToRename, string $newName): MediaInterface
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->update('ddmedia')
            ->set('DDFILENAME', ':DDFILENAME')
            ->where('OXID = :OXID')
            ->setParameters([
                'DDFILENAME' => $newName,
                'OXID' => $mediaIdToRename,
            ]);

        $queryBuilder->execute();

        return $this->getMediaById($mediaIdToRename);
    }

    /**
     * @throws WrongMediaIdGivenException
     * @throws Exception
     */
    public function deleteMedia(string $idToRemove): void
    {
        if (!$idToRemove) {
            throw new WrongMediaIdGivenException();
        }

        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXID')
            ->from('ddmedia')
            ->where('OXID = :OXID')
            ->orWhere('DDFOLDERID = :OXID')
            ->setParameter('OXID', $idToRemove);

        /** @var Result $queryResult */
        $queryResult = $queryBuilder->execute();
        $mediaToDelete = $queryResult->fetchAllAssociative();

        foreach ($mediaToDelete as $media) {
            $this->mediaAltRepository->deleteMediaAltTexts($media['OXID']);
        }

        $deleteBuilder = $this->queryBuilderFactory->create();
        $deleteBuilder
            ->delete('ddmedia')
            ->where('OXID = :OXID')
            ->orWhere('DDFOLDERID = :OXID')
            ->setParameter('OXID', $idToRemove);

        $deleteBuilder->execute();
    }

    public function changeMediaFolderId(string $mediaIdToUpdate, string $newFolderId): void
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->update('ddmedia')
            ->set('DDFOLDERID', ':DDFOLDERID')
            ->where('OXID = :OXID')
            ->setParameters([
                'DDFOLDERID' => $newFolderId,
                'OXID' => $mediaIdToUpdate,
            ]);

        $queryBuilder->execute();
    }
}
