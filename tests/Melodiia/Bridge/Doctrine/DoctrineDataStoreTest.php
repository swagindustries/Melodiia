<?php

namespace Biig\Melodiia\Test\Bridge\Doctrine;

use Biig\Melodiia\Bridge\Doctrine\DoctrineDataStore;
use Biig\Melodiia\Crud\FilterInterface;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Persistence\ManagerRegistry;
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
        $entityManager->flush($obj)->shouldBeCalled();
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

        $filter = $this->prophesize(FilterInterface::class);
        $filter->filter($qb)->shouldBeCalled();

        $dataStore = new DoctrineDataStore($registry->reveal());
        $pager = $dataStore->getPaginated('DummyEntity', 1, 30, [$filter->reveal()]);
        $this->assertInstanceOf(Pagerfanta::class, $pager);
    }
}
