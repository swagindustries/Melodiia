<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\WrongDataInput;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Zend\Json\Exception\RuntimeException;
use Zend\Json\Json;

abstract class BaseCrudController implements CrudControllerInterface
{
    /**
     * @return ApiResponse|FormInterface
     */
    protected function decodeInputData(FormFactoryInterface $formFactory, string $form, Request $request, bool $clearMissing = null)
    {
        if (null === $clearMissing) {
            $clearMissing = !in_array($request->getMethod(), ['POST', 'PUT']);
        }
        try {
            $form = $formFactory->createNamed('', $form);
            $inputData = Json::decode($request->getContent(), Json::TYPE_ARRAY);
            $form->submit($inputData, $clearMissing);

            if (!$form->isSubmitted()) {
                return new WrongDataInput();
            }

            if (!$form->isValid()) {
                return new FormErrorResponse($form);
            }
        } catch (RuntimeException $e) {
            return new WrongDataInput();
        }

        return $form;
    }
}
