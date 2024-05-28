# TYPO3 Extension `admiral_cloud_connector`

This extension connects the AdmiralCloud with Typo3. It adds a seperate file_storage for AdmiralCloud files. At every place sys_file_reference is used, you can use AdmiralCloud files

# Installation

composer require cpsit/admiral-cloud-connector

run following SQL to install file_storage
```
INSERT INTO `sys_file_storage` (`pid`, `cruser_id`, `deleted`, `description`, `name`, `driver`, `configuration`, `is_default`, `is_browsable`, `is_public`, `is_writable`, `is_online`, `auto_extract_metadata`, `processingfolder`) VALUES
(0, 0, 0, 'Automatically created during the installation of EXT:admiral_cloud_connector', 'AdmiralCloud', 'AdmiralCloud', '', 0, 1, 1, 0, 1, 1, '1:/_processed_/');
```

and create the corresponding fileMount "AdmiralCloud" for the storage.

Add following to AdditionalConfiguration.php
```
if (is_file(__DIR__ . '/CustomConfiguration.php')) {
    require_once __DIR__ . '/CustomConfiguration.php';
}
```

Create file typo3conf/CustomConfiguration.php with following content:
```
<?php


putenv('ADMIRALCLOUD_ACCESS_SECRET=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
putenv('ADMIRALCLOUD_ACCESS_KEY=xxxxxxxxxxxxxxxxxxxxxx');
putenv('ADMIRALCLOUD_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
putenv('ADMIRALCLOUD_DISABLE_FILEUPLOAD=0');
putenv('ADMIRALCLOUD_FLAG_CONFIG_ID=0');

putenv('ADMIRALCLOUD_DISABLE_FILEUPLOAD=1');
putenv('ADMIRALCLOUD_IS_PRODUCTION=1');
putenv('ADMIRALCLOUD_IMAGE_CONFIG_ID=238');
putenv('ADMIRALCLOUD_IMAGE_PNG_CONFIG_ID=321');
putenv('ADMIRALCLOUD_VIDEO_CONFIG_ID=239');
putenv('ADMIRALCLOUD_DOCUMENT_CONFIG_ID=240');
putenv('ADMIRALCLOUD_AUDIO_CONFIG_ID=241');
putenv('ADMIRALCLOUD_FLAG_CONFIG_ID=10');
putenv('ADMIRALCLOUD_IFRAMEURL=https://t3intpoc.admiralcloud.com/');


$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['admiral_cloud_connector'] = [
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
    'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
    'groups' => [
        'all',
        'system',
    ],
    'options' => [
        'defaultLifetime' => 0,
    ]
];
```
replace Credentials with yours

Create Backenduser with e-mail, first-name, last-name and Security group the user has in AdmiralCloud.
The E-Mail must be the same the user is using in Admiralcloud. If the User is Admin, the Securitygroup is ignored but must be set to random number (e.g. 13)

# TYPO3 editor permissions

To enable editors for AdmiralCloud functions, please add at least the following permission:

## Mounts & Workspaces

* Add "AdmiralCloud" to the list of accessible FileMounts
* Fileoperation permissions / File: check permission for [addFileViaAdmiralCloud] 

## optional

Allow cropping tool for AdmiralCloud images: 
check permission for (tx_admiralcloudconnector_crop) on tab „Access Lists“ / „Allowed excludefields” in “File Reference”.


# known bugs
* InstallSlot for Storage SQL isnt working, so the SQL need to run manually

### TODO documentation
* add information about authentification / security groups
