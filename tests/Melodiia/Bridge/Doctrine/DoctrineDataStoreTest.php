<?php

namespace SwagIndustries\Melodiia\Test\Bridge\Doctrine;

use SwagIndustries\Melodiia\Bridge\Doctrine\DoctrineDataStore;
use SwagIndustries\Melodiia\Crud\FilterCollection;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

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
