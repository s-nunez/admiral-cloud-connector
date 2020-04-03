<?php


namespace CPSIT\AdmiralCloudConnector\Resource;

use CPSIT\AdmiralCloudConnector\Traits\AdmiralCloudStorage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class File
 * @package CPSIT\AdmiralCloudConnector\Resource
 */
class File extends \TYPO3\CMS\Core\Resource\File
{
    use AdmiralCloudStorage;

    /**
     * Link hash to generate AdmiralCloud public url
     *
     * @var string
     */
    protected $txAdmiralCloudConnectorLinkhash = '';

    /**
     * @var string
     */
    protected $txAdmiralCloudConnectorCrop = '';

    /**
     * @return string
     */
    public function getTxAdmiralCloudConnectorLinkhash(): string
    {
        if (!$this->txAdmiralCloudConnectorLinkhash && !empty($this->properties['tx_admiralcloudconnector_linkhash'])) {
            $this->txAdmiralCloudConnectorLinkhash = $this->properties['tx_admiralcloudconnector_linkhash'];
        } else {
            // Load field "tx_admiralcloudconnector_linkhash" from DB
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file');

            $row = $queryBuilder
                ->select('tx_admiralcloudconnector_linkhash')
                ->from('sys_file')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($this->getUid(), \PDO::PARAM_INT))
                )
                ->execute()
                ->fetch();

            if (!empty($row['tx_admiralcloudconnector_linkhash'])) {
                $this->properties['tx_admiralcloudconnector_linkhash'] = $row['tx_admiralcloudconnector_linkhash'];
                $this->txAdmiralCloudConnectorLinkhash = $row['tx_admiralcloudconnector_linkhash'];
            }
        }

        return $this->txAdmiralCloudConnectorLinkhash;
    }

    /**
     * @param string $txAdmiralCloudConnectorLinkhash
     */
    public function setTxAdmiralCloudConnectorLinkhash(string $txAdmiralCloudConnectorLinkhash): void
    {
        $this->txAdmiralCloudConnectorLinkhash = $txAdmiralCloudConnectorLinkhash;
        $this->properties['tx_admiralcloudconnector_linkhash'] = $txAdmiralCloudConnectorLinkhash;

        $this->updatedProperties[] = 'tx_admiralcloudconnector_linkhash';
    }

    /**
     * @param array $mediaContainer
     * @return string
     */
    public function setTxAdmiralCloudConnectorLinkhashFromMediaContainer(array $mediaContainer): string
    {
        $links = $mediaContainer['links'];

        $linkhash = '';

        foreach ($links as $link) {
            // TODO make this numbers from configuration
            if (isset($link['playerConfigurationId']) && isset($link['flag'])
                && $link['playerConfigurationId'] == 3 && $link['flag'] == 0) {
                $linkhash = $link['link'];
                break;
            }
        }

        if ($linkhash) {
            $this->setTxAdmiralCloudConnectorLinkhash($linkhash);
        }

        return $linkhash;
    }

    /**
     * @return string
     */
    public function getTxAdmiralCloudConnectorCrop(): string
    {
        if (!$this->txAdmiralCloudConnectorCrop && !empty($this->properties['tx_admiralcloudconnector_crop'])) {
            $this->txAdmiralCloudConnectorCrop = $this->properties['tx_admiralcloudconnector_crop'];
        }

        return $this->txAdmiralCloudConnectorCrop;
    }

    public function getTxAdmiralCloudConnectorCropUrlPath(): string
    {
        $cropArray = json_decode($this->getTxAdmiralCloudConnectorCrop(), true);

        if (!$cropArray) {
            return '';
        }

        return implode(',', $cropArray['cropData']) . '/' . implode(',', $cropArray['focusPoint']);
    }

    /**
     * @param string $txAdmiralCloudconnectorLinkhashCrop
     */
    public function setTxAdmiralCloudConnectorCrop(string $txAdmiralCloudconnectorLinkhashCrop): void
    {
        $this->txAdmiralCloudConnectorCrop = $txAdmiralCloudconnectorLinkhashCrop;
    }

    public function setTypeFromMimeType(string $mimeType)
    {
        // this basically extracts the mimetype and guess the filetype based
        // on the first part of the mimetype works for 99% of all cases, and
        // we don't need to make an SQL statement like EXT:media does currently
        list($fileType) = explode('/', $mimeType);
        switch (strtolower($fileType)) {
            case 'text':
                $this->properties['type'] = self::FILETYPE_TEXT;
                break;
            case 'image':
                $this->properties['type'] = self::FILETYPE_IMAGE;
                break;
            case 'audio':
                $this->properties['type'] = self::FILETYPE_AUDIO;
                break;
            case 'video':
                $this->properties['type'] = self::FILETYPE_VIDEO;
                break;
            case 'application':

            case 'software':
                $this->properties['type'] = self::FILETYPE_APPLICATION;
                break;
            default:
                $this->properties['type'] = self::FILETYPE_UNKNOWN;
        }

        $this->updatedProperties[] = 'type';
        return (int)$this->properties['type'];
    }

    /**
     * Returns a modified version of the file.
     *
     * @param string $taskType The task type of this processing
     * @param array $configuration the processing configuration, see manual for that
     * @return ProcessedFile The processed file
     */
    public function process($taskType, array $configuration)
    {
        if ($taskType === ProcessedFile::CONTEXT_IMAGEPREVIEW
            && $this->getStorage()->getUid() === $this->getAdmiralCloudStorage()->getUid()) {

            // Return admiral cloud url for previews
            return GeneralUtility::makeInstance(ProcessedFile::class, $this, $taskType, $configuration);
        }

        return $this->getStorage()->processFile($this, $taskType, $configuration);
    }

    /**
     * @return Index\FileIndexRepository
     */
    protected function getFileIndexRepository()
    {
        return GeneralUtility::makeInstance(Index\FileIndexRepository::class);
    }
}
