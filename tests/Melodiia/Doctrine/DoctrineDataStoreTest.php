<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use SwagIndustries\Melodiia\Crud\FilterCollection;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Doctrine\DoctrineDataStore;

class DoctrineDataStoreTest extends TestCase
{
    public function testItIsAMelodiiaDataStore()
    {
        $datastore = new DoctrineDataStore($this->prophesize(ManagerRegistry::class)->reveal());

        $this->assertInstanceOf(DataStoreInterface::class, $datastore);
    }

    public function testItSaveUsingTheEntityManager()
    {
        $obj = new \stdClass();
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->persist($obj)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManager()->willReturn($entityManager->reveal());

        $datastore = new DoctrineDataStore($managerRegistry->reveal());
        $datastore->save($obj);
    }

    public function testItPaginate()
    {
        $qb = $this->prophesize(QueryBuilder::class)->reveal();
        $repo = $this->prophesize(EntityRepository::class);
        $repo->createQueryBuilder(Argument::any())->willReturn($qb);
        $manager = $this->prophesize(EntityManagerInterface::class);
        $manager->getRepository(Argument::any())->willReturn($repo->reveal());
        $registry = $this->prophesize(ManagerRegistry::class);
        $registry->getManager()->willReturn($manager->reveal());

        $filters = $this->prophesize(FilterCollection::class);
        $filters->filter($qb)->shouldBeCalled();

        $dataStore = new DoctrineDataStore($registry->reveal());
        $pager = $dataStore->getPaginated('DummyEntity', 1, $filters->reveal(), 30);
        $this->assertInstanceOf(Pagerfanta::class, $pager);
    }

    public function testItDeletesUsingTheEntityManager()
    {
        $obj = new \stdClass();
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->remove($obj)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManager()->willReturn($entityManager->reveal());

        $datastore = new DoctrineDataStore($managerRegistry->reveal());
        $datastore->remove($obj);
    }
}
