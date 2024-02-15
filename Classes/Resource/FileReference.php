<?php

namespace CPSIT\AdmiralCloudConnector\Resource;

use CPSIT\AdmiralCloudConnector\Utility\PermissionUtility;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Representation of a specific usage of a file with possibilities to override certain
 * properties of the original file just for this usage of the file.
 *
 * It acts as a decorator over the original file in the way that most method calls are
 * directly passed along to the original file object.
 *
 * All file related methods are directly passed along; only meta data functionality is adopted
 * in this decorator class to priorities possible overrides for the metadata for this specific usage
 * of the file.
 */
class FileReference extends \TYPO3\CMS\Core\Resource\FileReference
{
    /*****************
     * SPECIAL METHODS
     *****************/
    /**
     * Returns a publicly accessible URL for this file
     *
     * WARNING: Access to the file may be restricted by further means, e.g.
     * some web-based authentication. You have to take care of this yourself.
     *
     * @param bool  $relativeToCurrentScript   Determines whether the URL returned should be relative to the current script, in case it is relative at all (only for the LocalDriver)
     * @return string|null NULL if file is missing or deleted, the generated url otherwise
     */
    public function getPublicUrl($relativeToCurrentScript = false)
    {
        $file = $this->originalFile;
        if(str_starts_with($file->getMimeType(), 'admiralCloud/')){
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
                $fe_group = PermissionUtility::getPageFeGroup();

                if($this->getProperty('tablenames') == 'tt_content' && $this->getProperty('uid_foreign') && !$fe_group){
                    $fe_group = PermissionUtility::getContentFeGroupFromReference($this->getProperty('uid_foreign'));
                }
                $GLOBALS['admiralcloud']['fe_group'][$file->getIdentifier()] = $fe_group;

            }
        }
        $publicUrl = $file->getPublicUrl($relativeToCurrentScript);
        unset($GLOBALS['admiralcloud']['fe_group'][$file->getIdentifier()]);
        return $publicUrl;
    }



}
