<?php
declare(strict_types = 1);
namespace CPSIT\AdmiralCloudConnector\Http\Middleware;

/**
 * This file extends the redirect handler of the TYPO3 CMS project.
 *
 * 1. Option to keep request uri path which are builded with slugs
 */

use CPSIT\AdmiralCloudConnector\Service\AdmiralCloudService;
use CPSIT\AdmiralCloudConnector\Utility\ConfigurationUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hooks into the frontend request, and checks if a redirect should apply,
 * If so, a redirect response is triggered.
 *
 * @internal
 */
class ReadableLinkResolver extends \TYPO3\CMS\Redirects\Http\Middleware\RedirectHandler
{
    /**
     * First hook within the Frontend Request handling
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (0 === strpos($path, ConfigurationUtility::getLocalFileUrl())) {
            preg_match('/.*?\/.*?\/([a-z0-9]{8}\-[a-z0-9]{4}\-[a-z0-9]{4}\-[a-z0-9]{4}\-[a-z0-9]{12})\/.*/',$path,$matches);
            if(isset($matches[1])){
                /** @var AdmiralCloudService $admiralCloudService */
                $admiralCloudService = GeneralUtility::makeInstance(AdmiralCloudService::class);
                $url = $admiralCloudService->getDirectPublicUrlForHash($matches[1], (bool) GeneralUtility::_GP('download'));
                header("Location: " . $url);
                die();
            }
        }

        return $handler->handle($request);
    }
}
