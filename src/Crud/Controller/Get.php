<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Crud\Tools\IdResolverInterface;
use Biig\Melodiia\Crud\Tools\SimpleIdResolver;
use Biig\Melodiia\Exception\IdMissingException;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\NotFound;
use Biig\Melodiia\Response\OkContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class Get implements CrudControllerInterface
{
    use CrudControllerTrait;

    /** @var DataStoreInterface */
    private $dataStore;

    /** @var AuthorizationCheckerInterface */
    private $checker;

    /** @var IdResolverInterface */
    private $idResolver;

    public function __construct(DataStoreInterface $dataStore, AuthorizationCheckerInterface $checker, IdResolverInterface $idResolver = null)
    {
        $this->dataStore = $dataStore;
        $this->checker = $checker;
        $this->idResolver = $idResolver ?? new SimpleIdResolver();
    }

    public function __invoke(Request $request): ApiResponse
    {
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $groups = $request->attributes->get(self::SERIALIZATION_GROUP, []);
        $securityCheck = $request->attributes->get(self::SECURITY_CHECK, null);
        try {
            $id = $this->idResolver->resolveId($request, $modelClass);
        } catch (IdMissingException $e) {
            throw new NotFoundHttpException('No id found', $e);
        }

        $this->assertModelClassInvalid($modelClass);

        if (null === $data = $this->dataStore->find($modelClass, $id)) {
            return new NotFound();
        }

        if ($securityCheck && !$this->checker->isGranted($securityCheck, $data)) {
            throw new AccessDeniedException(\sprintf('Access denied to data of type "%s".', get_class($data)));
        }

        return new OkContent($data, $groups);
    }
}
