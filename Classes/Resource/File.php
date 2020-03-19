<?php


namespace CPSIT\AdmiralcloudConnector\Resource;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class File
 * @package CPSIT\AdmiralcloudConnector\Resource
 */
class File extends \TYPO3\CMS\Core\Resource\File
{
    /**
     * Link hash to generate AdmiralCloud public url
     *
     * @var string
     */
    protected $txAdmiralcloudconnectorLinkhash = '';

    /**
     * @return string
     */
    public function getTxAdmiralcloudconnectorLinkhash(): string
    {
        if (!$this->txAdmiralcloudconnectorLinkhash && !empty($this->properties['tx_admiralcloudconnector_linkhash'])) {
            $this->txAdmiralcloudconnectorLinkhash = $this->properties['tx_admiralcloudconnector_linkhash'];
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
                $this->txAdmiralcloudconnectorLinkhash = $row['tx_admiralcloudconnector_linkhash'];
            }
        }

        return $this->txAdmiralcloudconnectorLinkhash;
    }

    /**
     * @param string $txAdmiralcloudconnectorLinkhash
     */
    public function setTxAdmiralcloudconnectorLinkhash(string $txAdmiralcloudconnectorLinkhash): void
    {
        $this->txAdmiralcloudconnectorLinkhash = $txAdmiralcloudconnectorLinkhash;
        $this->properties['tx_admiralcloudconnector_linkhash'] = $txAdmiralcloudconnectorLinkhash;

        $this->updatedProperties[] = 'tx_admiralcloudconnector_linkhash';
    }

    /**
     * @param array $mediaContainer
     * @return string
     */
    public function setTxAdmiralcloudconnectorLinkhashFromMediaContainer(array $mediaContainer): string
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
            $this->setTxAdmiralcloudconnectorLinkhash($linkhash);
        }

        return $linkhash;
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
     * @return Index\FileIndexRepository
     */
    protected function getFileIndexRepository()
    {
        return GeneralUtility::makeInstance(Index\FileIndexRepository::class);
    }
}
