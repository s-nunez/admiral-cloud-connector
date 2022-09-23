.. include:: /Includes.txt

.. _LocalConfiguration:

======================================================
Setting up the LocalConfiguration.php for your system
======================================================

We suggest to put the code into a CustomConfiguration.php file.


First add the following lines to LocalConfiguration.php

..  code-block:: php
    :caption: Code

        if (is_file(__DIR__ . '/CustomConfiguration.php')) {
           require_once __DIR__ . '/CustomConfiguration.php';
        }

Then create a file typo3conf/CustomConfiguration.php with following content:

..  code-block:: php
    :caption: Code

       <?php

       putenv('ADMIRALCLOUD_ACCESS_SECRET=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
       putenv('ADMIRALCLOUD_ACCESS_KEY=xxxxxxxxxxxxxxxxxxxxxx');
       putenv('ADMIRALCLOUD_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
       putenv('ADMIRALCLOUD_DISABLE_FILEUPLOAD=0');
       putenv('ADMIRALCLOUD_FLAG_CONFIG_ID=0');

       putenv('ADMIRALCLOUD_DISABLE_FILEUPLOAD=1');
       putenv('ADMIRALCLOUD_IS_PRODUCTION=1');
       putenv('ADMIRALCLOUD_IMAGE_CONFIG_ID=238');
       putenv('ADMIRALCLOUD_VIDEO_CONFIG_ID=239');
       putenv('ADMIRALCLOUD_DOCUMENT_CONFIG_ID=240');
       putenv('ADMIRALCLOUD_AUDIO_CONFIG_ID=241');
       putenv('ADMIRALCLOUD_FLAG_CONFIG_ID=10');
       putenv('ADMIRALCLOUD_IFRAMEURL=https://t3intpoc.admiralcloud.com/');


        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['admiral_cloud_connector'] = [
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend::class,
            'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
            'groups' => [
                'all',
                'system',
            ],
            'options' => [
                'defaultLifetime' => 0,
            ]
        ];

You have ro replace the credentials for ADMIRALCLOUD_ACCESS_SECRET, ADMIRALCLOUD_ACCESS_KEY and ADMIRALCLOUD_CLIENT_ID with your own. These will be send to you by AdmiralCloud via Mails and SMS.

.. toctree::
   :maxdepth: 5
   :titlesonly:

   AcSecGroup
   FileStorage
   UserConfiguration