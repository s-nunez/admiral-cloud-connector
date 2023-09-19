<?php

namespace CPSIT\AdmiralCloudConnector\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
        $tsfe = self::getTypoScriptFrontendController();
        if ($tsfe === null) {
            return '';
        }
        $page = $pageRepository->getPage($tsfe->id);
        $feGroup = $page['fe_group'] ?? null;
        if($feGroup == '-1'){
            return '';
        }
        return $feGroup;
    }

    /**
     * get content fe group from reference
     *
     * @param int $uid
     */
    public static function getContentFeGroupFromReference(int $uid){
        $content = self::getContent($uid);
        if($content){
            $feGroup = $content['fe_group'] ?? null;
            if($feGroup == '-1'){
                return '';
            }
            return $feGroup;
        }
        return '';
    }

    public static function getContent(int $uid){
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $result = $queryBuilder
        ->select('fe_group')
        ->from('tt_content')
        ->where('uid=' . $uid)
        ->execute();
        return $result->fetch();
    }

    private static function getTypoScriptFrontendController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['tsfe'] ?? null;
    }
}
