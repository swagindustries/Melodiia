<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\EventListener;

use SwagIndustries\Melodiia\Error\OnError;
use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;

final class ExceptionListener
{
    /** @var MelodiiaConfigurationInterface */
    private $config;

    /** @var ErrorListener */
    private $errorListener;

    public function __construct(
        MelodiiaConfigurationInterface $config,
        OnError $controller,
        bool $debug,
        ErrorListener $errorListener = null
    ) {
        $this->errorListener = $errorListener ?? new ErrorListener($controller, null, $debug);
        $this->config = $config;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->config->getApiConfigFor($request)) {
            return;
        }

        $this->errorListener->onKernelException($event);
    }
}
