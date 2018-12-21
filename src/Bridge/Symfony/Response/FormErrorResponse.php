<?php

namespace Biig\Happii\Bridge\Symfony\Response;

use Biig\Happii\Exception\InvalidResponseException;
use Biig\Happii\Response\Model\UserDataError;
use Biig\Happii\Response\AbstractUserDataErrorResponse;
use Nekland\Tools\StringTools;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class FormErrorResponse extends AbstractUserDataErrorResponse
{
    /** @var FormInterface */
    private $form;

    /** @var UserDataError[] */
    private $errors;

    public function __construct(FormInterface $form)
    {
        if (!$form->isSubmitted()) {
            throw new InvalidResponseException('The given form is not submitted.');
        }

        if ($form->isValid()) {
            throw new InvalidResponseException('The given form is valid.');
        }

        $this->form = $form;
    }

    /**
     * @return UserDataError[]
     */
    public function getErrors(): array
    {
        if (null !== $this->errors) {
            return $this->errors;
        }

        $errors = $this->form->getErrors(true, true);

        foreach ($errors as $error) {
            [$message, $propertyPath] = $this->getCause($error);
            if (!isset($this->errors[$propertyPath])) {
                $this->errors[$propertyPath] = new UserDataError(
                    $propertyPath,
                    [$message]
                );
            } else {
                $this->errors[$propertyPath]->addError($message);
            }
        }

        return $this->errors;
    }

    private function resolvePropertyPath(FormInterface $form)
    {
        $propertyPath = '';
        do {
            $part = (string) $form->getPropertyPath();

            // Basically this condition means the data is object so we need a dot
            if (!empty($propertyPath) && !StringTools::startsWith($propertyPath, '[')) {
                $propertyPath = '.' . $propertyPath;
            }
            $propertyPath = $part . $propertyPath;
        } while($form = $form->getParent());

        // Because of the condition in the while, it may ends with a dot at
        // the start, which is wrong, let's remove it.
        $propertyPath = StringTools::removeStart($propertyPath, '.');

        return $propertyPath;
    }

    /**
     * @param FormError $formError
     *
     * @return array [$message, $propertyPath]
     */
    protected function getCause(FormError $formError): array
    {
        // Resolve manually the property path because
        // $form->getCause()->getPropertyPath() does not
        // represent the data but the form.
        $propertyPath = $this->resolvePropertyPath($formError->getOrigin());

        return [$formError->getCause()->getMessage(), $propertyPath];
    }
}
