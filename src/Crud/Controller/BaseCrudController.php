<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Controller;

use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Response\FormErrorResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseCrudController implements CrudControllerInterface
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * @return ApiResponse|FormInterface
     */
    protected function decodeInputData(FormFactoryInterface $formFactory, string $form, Request $request, bool $clearMissing = null, object $data = null)
    {
        $form = $formFactory->createNamed('', $form, $data, ['clear_missing' => $clearMissing]);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return new FormErrorResponse($form);
        }

        return $form;
    }

    protected function dispatch($event, string $eventName)
    {
        $this->dispatcher->dispatch($event, $eventName);
    }
}
