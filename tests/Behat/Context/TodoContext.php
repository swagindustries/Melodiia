<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Tests\Behat\Context;

use TestApplication\Entity\Todo;
use Webmozart\Assert\Assert;

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

    /**
     * @Given there is one todo ":content"
     */
    public function thereIsOneTodo($content)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $todo = new Todo();
        $todo->setContent($content);
        $entityManager->persist($todo);

        $entityManager->flush();
    }

    /**
     * @Then todo with content ":content" should not exists
     */
    public function todoWithContentShouldNotExists($content)
    {
        $repo = $this->getTodoRepo();
        $todo = $repo->findOneBy(['content' => $content]);

        Assert::isEmpty($todo, "todo with content $content exists !");
    }

    private function getTodoRepo()
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        return $entityManager->getRepository(Todo::class);
    }
}
