<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Controller;

use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Crud\Tools\IdResolverInterface;
use SwagIndustries\Melodiia\Crud\Tools\SimpleIdResolver;
use SwagIndustries\Melodiia\Exception\IdMissingException;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Response\NotFound;
use SwagIndustries\Melodiia\Response\OkContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class Get implements CrudControllerInterface
{
    use CrudControllerTrait;

    /** @var DataStoreInterface */
    private $dataStore;

    /** @var AuthorizationCheckerInterface|null */
    private $checker;

    /** @var IdResolverInterface */
    private $idResolver;

    public function __construct(DataStoreInterface $dataStore, IdResolverInterface $idResolver = null, AuthorizationCheckerInterface $checker = null)
    {
        $this->dataStore = $dataStore;
        $this->idResolver = $idResolver ?? new SimpleIdResolver();
        $this->checker = $checker;
    }

    public function __invoke(Request $request): ApiResponse
    {
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $groups = $request->attributes->get(self::SERIALIZATION_GROUP, []);
        try {
            $id = $this->idResolver->resolveId($request, $modelClass);
        } catch (IdMissingException $e) {
            throw new NotFoundHttpException('No id found', $e);
        }

        $this->assertModelClassInvalid($modelClass);

        if (null === $data = $this->dataStore->find($modelClass, $id)) {
            return new NotFound();
        }

        $this->assertResourceRights($request, $data);

        return new OkContent($data, $groups);
    }
}
