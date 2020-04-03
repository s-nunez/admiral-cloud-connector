<?php


namespace CPSIT\AdmiralCloudConnector\Form\Element;

use CPSIT\AdmiralCloudConnector\Traits\AdmiralCloudStorage;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use CPSIT\AdmiralCloudConnector\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AdmiralCloudImageManipulationElement
 * @package CPSIT\AdmiralCloudConnector\Form\Element
 */
class AdmiralCloudImageManipulationElement extends AbstractFormElement
{
    use AdmiralCloudStorage;

    /**
     * Default element configuration
     *
     * @var array
     */
    protected static $defaultConfig = [
        'file_field' => 'uid_local',
    ];

    /**
     * Default field information enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldInformation = [
        'tcaDescription' => [
            'renderType' => 'tcaDescription',
        ],
    ];

    /**
     * Default field wizards enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldWizard = [
        'localizationStateSelector' => [
            'renderType' => 'localizationStateSelector',
        ],
        'otherLanguageContent' => [
            'renderType' => 'otherLanguageContent',
            'after' => [
                'localizationStateSelector'
            ],
        ],
        'defaultLanguageDifferences' => [
            'renderType' => 'defaultLanguageDifferences',
            'after' => [
                'otherLanguageContent',
            ],
        ],
    ];

    /**
     * @var StandaloneView
     */
    protected $templateView;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @param NodeFactory $nodeFactory
     * @param array $data
     */
    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        parent::__construct($nodeFactory, $data);
        // Would be great, if we could inject the view here, but since the constructor is in the interface, we can't
        $this->templateView = GeneralUtility::makeInstance(StandaloneView::class);
        $this->templateView->setLayoutRootPaths([GeneralUtility::getFileAbsFileName('EXT:admiral_cloud_connector/Resources/Private/Backend/Layouts/')]);
        $this->templateView->setPartialRootPaths([GeneralUtility::getFileAbsFileName('EXT:admiral_cloud_connector/Resources/Private/Backend/Partials/ImageManipulation/')]);
        $this->templateView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:admiral_cloud_connector/Resources/Private/Backend/Templates/ImageManipulation/ImageManipulationElement.html'));
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $resultArray = $this->initializeResultArray();
        $parameterArray = $this->data['parameterArray'];
        $config = $this->populateConfiguration($parameterArray['fieldConf']['config']);

        $file = $this->getFile($this->data['databaseRow'], $config['file_field']);
        if (!$file || $file->getStorage()->getUid() !== $this->getAdmiralCloudStorage()->getUid()) {
            // Early return in case we do not find a file or does not come from AdmiralCloud
            return $resultArray;
        }

        // If sys_file_reference is new, add crop information from BE session.
        // Crop information was stored in \CPSIT\AdmiralCloudConnector\Controller\Backend\BrowserController
        if ($this->data['command'] === 'new') {
            $sessionData = $this->getBackendUser()->getSessionData('admiralCloud') ?? [];
            if (!empty($sessionData['cropInformation'][$file->getUid()])) {
                $parameterArray['itemFormElValue'] = json_encode($sessionData['cropInformation'][$file->getUid()]);
                unset($sessionData['cropInformation'][$file->getUid()]);
                $this->getBackendUser()->setAndSaveSessionData('admiralCloud', $sessionData);
            }
        }

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $fieldControlResult = $this->renderFieldControl();
        $fieldControlHtml = $fieldControlResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldControlResult, false);

        $fieldWizardResult = $this->renderFieldWizard();
        $fieldWizardHtml = $fieldWizardResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);

        $arguments = [
            'fieldInformation' => $fieldInformationHtml,
            'fieldControl' => $fieldControlHtml,
            'fieldWizard' => $fieldWizardHtml,
            'isAllowedFileExtension' => in_array(
                strtolower($file->getExtension()), GeneralUtility::trimExplode(',', strtolower($config['allowedExtensions']), true)
            ),
            'image' => $file,
            'formEngine' => [
                'field' => [
                    'value' => $parameterArray['itemFormElValue'],
                    'name' => $parameterArray['itemFormElName']
                ],
                'validation' => '[]'
            ],
        ];

        $this->templateView->assignMultiple($arguments);
        $resultArray['html'] = $this->templateView->render();

        return $resultArray;
    }

    /**
     * Get file object
     *
     * @param array $row
     * @param string $fieldName
     * @return File|null
     */
    protected function getFile(array $row, $fieldName): ?File
    {
        $file = null;
        $fileUid = !empty($row[$fieldName]) ? $row[$fieldName] : null;
        if (is_array($fileUid) && isset($fileUid[0]['uid'])) {
            $fileUid = $fileUid[0]['uid'];
        }
        if (MathUtility::canBeInterpretedAsInteger($fileUid)) {
            try {
                $file = ResourceFactory::getInstance()->getFileObject($fileUid);
            } catch (FileDoesNotExistException $e) {
            } catch (\InvalidArgumentException $e) {
            }
        }
        return $file;
    }

    /**
     * @param array $baseConfiguration
     * @return array
     */
    protected function populateConfiguration(array $baseConfiguration): array
    {
        $defaultConfig = self::$defaultConfig;

        $config = array_replace_recursive($defaultConfig, $baseConfiguration);

        // By default we allow all image extensions that can be handled by the GFX functionality
        if ($config['allowedExtensions'] === null) {
            $config['allowedExtensions'] = implode(
                ', ',
                GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], true)
            );
        }

        return $config;
    }
}
