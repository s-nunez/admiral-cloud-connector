<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace CPSIT\AdmiralCloudConnector\Backend;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for link handlers displayed in the LinkBrowser
 */
interface LinkHandlerInterface
{
    /**
     * @return array
     */
    public function getLinkAttributes();

    /**
     * @param string[] $fieldDefinitions Array of link attribute field definitions
     * @return string[]
     */
    public function modifyLinkAttributes(array $fieldDefinitions);

    /**
     * Initialize the handler
     *
     * @param \CPSIT\AdmiralCloudConnector\Controller\Backend\AbstractLinkBrowserController $linkBrowser
     * @param string $identifier
     * @param array $configuration Page TSconfig
     */
    public function initialize(\CPSIT\AdmiralCloudConnector\Controller\Backend\AbstractLinkBrowserController $linkBrowser, $identifier, array $configuration);

    /**
     * Checks if this is the handler for the given link
     *
     * The handler may store this information locally for later usage.
     *
     * @param array $linkParts Link parts as returned from TypoLinkCodecService
     *
     * @return bool
     */
    public function canHandleLink(array $linkParts);

    /**
     * Format the current link for HTML output
     *
     * @return string
     */
    public function formatCurrentUrl();

    /**
     * Render the link handler. Ideally this modifies the view, but it can also render content directly.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function render(ServerRequestInterface $request);

    /**
     * Return TRUE if the handler supports to update a link.
     *
     * This is useful for file or page links, when only attributes are changed.
     *
     * @return bool
     */
    public function isUpdateSupported();

    /**
     * @return string[] Array of body-tag attributes
     */
    public function getBodyTagAttributes();
}
