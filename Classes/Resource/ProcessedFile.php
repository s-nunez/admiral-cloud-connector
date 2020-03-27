<?php


namespace CPSIT\AdmiralcloudConnector\Resource;

use CPSIT\AdmiralcloudConnector\Service\AdmiralcloudService;
use CPSIT\AdmiralcloudConnector\Traits\AdmiralcloudStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ProcessedFile
 * @package CPSIT\AdmiralcloudConnector\Resource
 */
class ProcessedFile extends \TYPO3\CMS\Core\Resource\ProcessedFile
{
    use AdmiralcloudStorage;

    /**
     * @inheritDoc
     */
    public function getPublicUrl($relativeToCurrentScript = false): ?string
    {
        if ($this->getOriginalFile()->getProperties()['mime_type'] === 'admiracloud/image/jpg') {

            $this->properties['width'] = (int) $this->getProcessingConfiguration()['width'];
            $this->properties['height'] = (int) $this->getProcessingConfiguration()['height'];

            // TODO get crop and focus information

            return $this->getAdmiralcloudService()->getImagePublicUrl($this->getOriginalFile(), $this->properties['width'], $this->properties['height']);
        }

        return parent::getPublicUrl($relativeToCurrentScript);
    }

    /**
     * @return AdmiralcloudService
     */
    protected function getAdmiralcloudService()
    {
        return GeneralUtility::makeInstance(AdmiralcloudService::class);
    }
}
