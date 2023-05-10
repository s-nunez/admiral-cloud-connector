<?php

namespace CPSIT\AdmiralCloudConnector\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class PermissionUtility
{
    /**
     * @return bool
     */
    public static function userHasPermissionForAdmiralCloud(): bool
    {
        // If user is admin or has access to file with AdmiralCloud
        if (isset($GLOBALS['BE_USER']) && (static::getBackendUser()->isAdmin()
            || (static::getBackendUser()->getFilePermissions()['addFileViaAdmiralCloud'] ?? false))) {
            return true;
        }

        return false;
    }

    /**
     * @return BackendUserAuthentication
     */
    protected static function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
