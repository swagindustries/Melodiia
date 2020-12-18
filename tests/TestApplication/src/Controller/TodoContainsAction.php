<?php

declare(strict_types=1);

namespace TestApplication\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nekland\Tools\StringTools;
use SwagIndustries\Melodiia\Response\Model\Collection;
use SwagIndustries\Melodiia\Response\OkContent;
use TestApplication\Entity\Todo;

class TodoContainsAction
{
    /**
     * This is just a test. To achieve that easily you should use filters and crud controllers.
     */
    public function __invoke(EntityManagerInterface $manager, $word)
    {
        $todos = array_filter($manager->getRepository(Todo::class)->findAll(), function (Todo $todo) use ($word) {
            return StringTools::contains($todo->getContent(), $word);
        });

        return new OkContent(new Collection($todos));
    }
}
