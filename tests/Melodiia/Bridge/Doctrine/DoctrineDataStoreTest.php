<?php

namespace Biig\Melodiia\Test\Bridge\Doctrine;

use Biig\Melodiia\Bridge\Doctrine\DoctrineDataStore;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DoctrineDataStoreTest extends TestCase
{
    public function testItIsAMelodiiaDataStore()
    {
        $datastore = new DoctrineDataStore($this->prophesize(EntityManagerInterface::class)->reveal());

        $this->assertInstanceOf(DataStoreInterface::class, $datastore);
    }

    public function testItSaveUsingTheEntityManager()
    {
        $obj = new \stdClass();
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->persist($obj)->shouldBeCalled();
        $entityManager->flush($obj)->shouldBeCalled();

        $datastore = new DoctrineDataStore($entityManager->reveal());
        $datastore->save($obj);
    }
}
