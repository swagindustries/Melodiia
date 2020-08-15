<?php

namespace SwagIndustries\Melodiia\Bridge\Symfony\EventListener;

use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Nekland\Tools\StringTools;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
// BC Layer for Symfony 4
// This class is removed in Symfony5 in favor of ErrorListener
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as LegacyExceptionListener;

final class ExceptionListener
{
    /** @var MelodiiaConfigurationInterface */
    private $config;

    /** @var ErrorListener|LegacyExceptionListener */
    private $errorListener;

    public function __construct(
        MelodiiaConfigurationInterface $config,
        $controller, LoggerInterface $logger = null,
        $debug = false,
        ErrorListener $errorListener = null // PHP will not fail if the class does not exist and null is passed here
    ) {
        // In Sf 4.3 errorListener will not be provide, it will in Sf > 4.3
        // But we want to redefine it anyway.
        $this->errorListener = $errorListener ?
            new ErrorListener($controller, $logger, $debug) :
            new LegacyExceptionListener($controller, $logger, $debug);
        $this->config = $config;
    }

    public function onKernelException(ExceptionEvent $event): void
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

        $this->errorListener->onKernelException($event);
    }
}
