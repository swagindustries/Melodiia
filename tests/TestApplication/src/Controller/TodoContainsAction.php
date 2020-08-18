<?php

namespace TestApplication\Controller;

use Doctrine\ORM\EntityManagerInterface;
use SwagIndustries\Melodiia\Response\Model\Collection;
use SwagIndustries\Melodiia\Response\OkContent;
use Symfony\Component\String\UnicodeString;
use TestApplication\Entity\Todo;

class TodoContainsAction
{
    /**
     * This is just a test. To achieve that easily you should use filters and crud controllers.
     */
    public function __invoke(EntityManagerInterface $manager, $word)
    {
        $todos = array_filter($manager->getRepository(Todo::class)->findAll(), function (Todo $todo) use ($word) {
            $str = new UnicodeString($todo->getContent());

            return $str->containsAny($word);
        });

        return new OkContent(new Collection($todos));
    }
}
