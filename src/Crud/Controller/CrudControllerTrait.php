<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Controller;

use SwagIndustries\Melodiia\Crud\CrudableModelInterface;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Exception\MelodiiaLogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait CrudControllerTrait
{
    /**
     * @param string $modelClass FQCN of the model class
     *
     * @throws MelodiiaLogicException if the model class don't match Melodiia requirements
     */
    public function assertModelClassInvalid(string $modelClass): void
    {
        if (empty($modelClass) || !class_exists($modelClass) || !is_subclass_of($modelClass, CrudableModelInterface::class)) {
            throw new MelodiiaLogicException('If you use melodiia CRUD classes, you need to specify a model.');
        }
    }

    private function assertResourceRights(Request $request, $data = null)
    {
        $securityCheck = $request->attributes->get(CrudControllerInterface::SECURITY_CHECK, null);
        $modelClass = $request->attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE);

        if (null === $securityCheck) {
            return;
        }

        if (!$this->checker) {
            throw new MelodiiaLogicException('The security component of Symfony seems to not be enabled');
        }

        if (($data && !$this->checker->isGranted($securityCheck, $data)) || !$this->checker->isGranted($securityCheck)) {
            throw new AccessDeniedException(\sprintf('Access denied to data of type "%s".', $modelClass));
        }
    }
}
