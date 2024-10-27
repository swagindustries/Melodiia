<?php

declare(strict_types=1);

namespace TestApplication\Controller;

use Doctrine\ORM\EntityManagerInterface;
use SwagIndustries\Melodiia\Response\OkNoContent;
use TestApplication\Entity\Todo;

class ArchiveTodoAction
{
    public function __invoke(Todo $todo, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($todo);
        $entityManager->flush();

        return new OkNoContent();
    }
}
