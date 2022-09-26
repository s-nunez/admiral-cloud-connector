.. include:: /Includes.txt

.. _quickStart:

==============
Installation
==============

#. Install the extension:

Install the extension with composer:

    .. code-block:: bash

        composer require cpsit/admiral-cloud-connector

Afterwards run the following SQL statement:

    .. code-block:: bash

        INSERT INTO `sys_file_storage` (`pid`, `cruser_id`, `deleted`, `description`, `name`, `driver`, `configuration`, `is_default`, `is_browsable`, `is_public`, `is_writable`, `is_online`, `auto_extract_metadata`, `processingfolder`) VALUES
        (0, 0, 0, 'Automatically created during the installation of EXT:admiral_cloud_connector', 'AdmiralCloud', 'AdmiralCloud', '', 0, 1, 1, 0, 1, 1, '1:/_processed_/');

    Alternatively you can use the database analyzer in the Maintanance backend module.

#. LocalConfiguration.php:

   - Once you have setup a contract with AdmiralCloud you will receive your login credential by mail and SMS
   - Add the required configuration to LocalConfiguration.php

    :ref:`Learn more about how configure the <LocalConfiguration>`

#. Intitial setup of user groups

    - send a list of your user groups to AdmiralCloud
    - setup AC SecurityGroups in the backend

    :ref:`Learn more about how to setup the <AcSecGroup>`

#. Setting up a file mount:

    - You have to create fileMount "AdmiralCloud" for the storage.

    :ref:`Learn more about how to setup the <FileStorage>`

#. User configuration:

   - No configuration needed for editors
   - Administrators need the Security Group for confirmation

   :ref:`Learn more about the user management <UserConfiguration>`

#. LinkHandler Configuration:

   -  A LinkHandler configuration is included automatically
   -  no manual configuration steps

   You can find the LinkHandler Configuration here: EXT:admiral_cloud_connector/Configuration/TSconfig/LinkHandler.ts



.. toctree::
   :maxdepth: 2
   :titlesonly:

   LocalConfiguration
   AcSecGroup
   FileStorage
   UserConfiguration
