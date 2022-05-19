<?php

namespace CPSIT\AdmiralCloudConnector\Task;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

/**
 * Class ExportSysFileWithMetadataTaskAdditionalFieldProvider
 * @package CPSIT\AdmiralCloudConnector\Task
 */
class ExportSysFileWithMetadataTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * @inheritDoc
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        return [
            $this->getExportFilePathField($taskInfo, $task, $schedulerModule),
            $this->getSecurityGroupMappingField($taskInfo, $task, $schedulerModule),
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateAdditionalFields(
        array &$submittedData,
        SchedulerModuleController $schedulerModule
    ) {
        if (empty($submittedData['scheduler_exportSysFileWithMetadata_exportFilePath'])
            || empty($submittedData['scheduler_exportSysFileWithMetadata_securityGroupMapping'])) {

            $flashMessage = sprintf(
                'Fields "%s" and "%s" cannot be empty.',
                $this->getLanguageService()->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:task.export_sys_file_with_metadata.exportFilePath'),
                $this->getLanguageService()->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:task.export_sys_file_with_metadata.securityGroupMapping')
            );

            $this->addFlashMessage($flashMessage, FlashMessage::ERROR);

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->exportFilePath = $submittedData['scheduler_exportSysFileWithMetadata_exportFilePath'];
        $task->securityGroupMapping = $submittedData['scheduler_exportSysFileWithMetadata_securityGroupMapping'];
    }

    /**
     * Get configuration array for exportFilePath field
     *
     * @param array $taskInfo
     * @param $task
     * @param SchedulerModuleController $schedulerModule
     * @return array
     */
    protected function getExportFilePathField(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    ): array {
        // Initialize selected fields
        if (empty($taskInfo['scheduler_exportSysFileWithMetadata_exportFilePath'])
            && $schedulerModule->getCurrentAction()->equals(Action::EDIT)) {
            $taskInfo['scheduler_exportSysFileWithMetadata_exportFilePath'] = $task->exportFilePath;
        }

        $fieldName = 'tx_scheduler[scheduler_exportSysFileWithMetadata_exportFilePath]';
        $fieldId = 'task_exportSysFileWithMetadata_exportFilePath';

        $fieldHtml = sprintf(
            '<input type="text" class="form-control" name="%s" id="%s" value="%s" />',
            $fieldName,
            $fieldId,
            $task->exportFilePath
        );

        return [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:task.export_sys_file_with_metadata.exportFilePath',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldId
        ];
    }

    /**
     * Get configuration array for securityGroupMapping field
     *
     * @param array $taskInfo
     * @param $task
     * @param SchedulerModuleController $schedulerModule
     * @return array
     */
    protected function getSecurityGroupMappingField(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    ): array {
        // Initialize selected fields
        if (empty($taskInfo['scheduler_exportSysFileWithMetadata_securityGroupMapping'])
            && $schedulerModule->getCurrentAction()->equals(Action::EDIT)) {
            $taskInfo['scheduler_exportSysFileWithMetadata_securityGroupMapping'] = $task->securityGroupMapping;
        }

        $fieldName = 'tx_scheduler[scheduler_exportSysFileWithMetadata_securityGroupMapping]';
        $fieldId = 'task_exportSysFileWithMetadata_securityGroupMapping';

        $fieldHtml = sprintf(
            '<textarea style="min-height: 200px;" class="form-control" name="%s" id="%s">%s</textarea>',
            $fieldName,
            $fieldId,
            $task->securityGroupMapping
        );

        return [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:task.export_sys_file_with_metadata.securityGroupMapping',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldId
        ];
    }

    /**
     * Add flash message to message queue
     *
     * @param string $message
     * @param int $type
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function addFlashMessage(string $message, int $type)
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            '',
            $type,
            true
        );

        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);

        /** @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue $defaultFlashMessageQueue */
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
