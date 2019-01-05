<?php

namespace Biig\Melodiia\Bridge\Doctrine;

use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineDataStore implements DataStoreInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(object $model)
    {
        $this->entityManager->persist($model);
        $this->entityManager->flush($model);
    }
}
