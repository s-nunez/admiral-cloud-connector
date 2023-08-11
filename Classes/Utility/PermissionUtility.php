<?php

namespace CPSIT\AdmiralCloudConnector\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

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

    /**
     * is site secured check
     */
    public static function getPageFeGroup(){
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $page = $pageRepository->getPage($GLOBALS['TSFE']->id);
        if($page['fe_group'] == '-1'){
            return '';
        } 
        return $page['fe_group'];
    }

    /**
     * get content fe group from reference
     *
     * @param int $uid
     * @return void
     */
    public static function getContentFeGroupFromReference(int $uid){
        $content = self::getContent($uid);
        if($content){
            if($content['fe_group'] == '-1'){
                return '';
            } 
            return $content['fe_group'];
        }
        return '';
    }

    public function getContent(int $uid){
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $result = $queryBuilder
        ->select('fe_group')
        ->from('tt_content')
        ->where('uid=' . $uid)
        ->execute();
        return $result->fetch();
    }
}
