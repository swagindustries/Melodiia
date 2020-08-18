<?php

namespace SwagIndustries\Melodiia\Form;

use SwagIndustries\Melodiia\Bridge\Symfony\Form\Type\ApiType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiRequestHandler implements RequestHandlerInterface
{
    public function handleRequest(FormInterface $form, $request = null)
    {
        if (!$request instanceof Request) {
            throw new UnexpectedTypeException($request, 'Symfony\Component\HttpFoundation\Request');
        }

        $clearMissing = $form->getConfig()->getOption(ApiType::CLEAR_MISSING_OPTION);
        if (null === $clearMissing) {
            $clearMissing = !in_array($request->getMethod(), ['POST', 'PUT']);
        }

        try {
            $payload = \json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $form->submit($payload, $clearMissing);
        } catch (\JsonException $e) {
            $form->addError(new FormError(
                'Invalid request content',
            ));
            $form->submit(null, false);
        }
    }

    /**
     * Notice: this impacts allow_file_upload field.
     * {@inheritdoc}
     */
    public function isFileUpload($data)
    {
        return false;
    }
}
