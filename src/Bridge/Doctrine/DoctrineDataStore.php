<?php

namespace Biig\Melodiia\Bridge\Doctrine;

use Biig\Melodiia\Crud\FilterCollectionInterface;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Exception\ImpossibleToPaginateWithDoctrineRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

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

    public function getPaginated(string $type, int $page, FilterCollectionInterface $filters, $maxPerPage = 30): PagerFanta
    {
        $doctrineRepository = $this->getEntityManager()->getRepository($type);

        if (!method_exists($doctrineRepository, 'createQueryBuilder')) {
            throw new ImpossibleToPaginateWithDoctrineRepository('Data cannot be paginated because your repository can\'t give a Doctrine query builder, please define method createQueryBuilder.');
        }

        $qb = $doctrineRepository->createQueryBuilder('item');
        $filters->filter($qb);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($maxPerPage);

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
