<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Tests\Behat\Context;

use TestApplication\Entity\Todo;

class TodoContext extends AbstractContext
{
    /**
     * @Given there are some todos
     */
    public function thereAreSome()
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $todo1 = new Todo();
        $todo1->setContent('foo');
        $todo2 = new Todo();
        $todo2->setContent('bar');
        $todo3 = new Todo();
        $todo3->setContent('baz');

        $entityManager->persist($todo1);
        $entityManager->persist($todo2);
        $entityManager->persist($todo3);

        $entityManager->flush();
    }
}
