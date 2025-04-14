<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use SwagIndustries\Melodiia\Crud\FilterCollectionInterface;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Exception\ImpossibleToPaginateWithDoctrineRepository;

class DoctrineDataStore implements DataStoreInterface
{
    /** @var ManagerRegistry */
    protected $doctrineRegistry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->doctrineRegistry = $registry;
    }

    public function save(object $model)
    {
        $this->getEntityManager()->persist($model);
        $this->getEntityManager()->flush();
    }

    public function find(string $type, $id): ?object
    {
        return $this->getEntityManager()->getRepository($type)->find($id);
    }

    public function getPaginated(string $type, int $page, FilterCollectionInterface $filters, $maxPerPage = 30): Pagerfanta
    {
        $doctrineRepository = $this->getEntityManager()->getRepository($type);

        if (!method_exists($doctrineRepository, 'createQueryBuilder')) {
            throw new ImpossibleToPaginateWithDoctrineRepository('Data cannot be paginated because your repository can\'t give a Doctrine query builder, please define method createQueryBuilder.');
        }

        $qb = $doctrineRepository->createQueryBuilder('item');
        $filters->filter($qb);

        // Keep compatibility with pager fanta 2.X
        if (class_exists(QueryAdapter::class)) {
            $pager = new Pagerfanta(new QueryAdapter($qb));
        } else {
            $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        }
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function remove(object $model)
    {
        $this->getEntityManager()->remove($model);
        $this->getEntityManager()->flush();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrineRegistry->getManager();
    }
}
