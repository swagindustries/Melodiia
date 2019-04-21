<?php

namespace Biig\Melodiia\Bridge\Symfony\EventListener;

use Biig\Melodiia\MelodiiaConfigurationInterface;
use Nekland\Tools\StringTools;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as BaseExceptionListener;

final class ExceptionListener extends BaseExceptionListener
{
    /** @var MelodiiaConfigurationInterface */
    private $config;

    public function __construct(MelodiiaConfigurationInterface $config, $controller, LoggerInterface $logger = null, $debug = false, string $charset = null, $fileLinkFormat = null)
    {
        parent::__construct($controller, $logger, $debug, $charset, $fileLinkFormat);
        $this->config = $config;
    }

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // Normalize exceptions only for routes managed by Melodiia
        $endpoints = $this->config->getApiEndpoints();
        $basePath = $request->getRequestUri();

        $matchUrl = false;
        foreach ($endpoints as $endpoint) {
            if (StringTools::startsWith($basePath, $endpoint)) {
                $matchUrl = true;
            }
        }

        if (!$matchUrl) {
            return;
        }

        parent::onKernelException($event);
    }
}
