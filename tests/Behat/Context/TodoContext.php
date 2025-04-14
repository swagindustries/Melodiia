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
        $todo1->setPublishDate(new \DateTimeImmutable('2050-01-02'));
        $todo2 = new Todo();
        $todo2->setContent('bar');
        $todo2->setPublishDate(new \DateTimeImmutable('2050-01-02'));
        $todo3 = new Todo();
        $todo3->setContent('baz');
        $todo3->setPublishDate(new \DateTimeImmutable('2050-01-02'));

        $entityManager->persist($todo1);
        $entityManager->persist($todo2);
        $entityManager->persist($todo3);

        $entityManager->flush();
    }

    /**
     * @Given there are many todos
     */
    public function thereAreMany()
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $todos = [
            'foo', 'bar', 'baz', 'bak', 'baf', 'bas', 'bab', 'bap',
        ];
        foreach ($todos as $todo) {
            $todox = new Todo();
            $todox->setContent($todo);
            $todox->setPublishDate(new \DateTimeImmutable('2050-01-02'));
            $entityManager->persist($todox);
        }

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
        $todo->setPublishDate(new \DateTimeImmutable('2050-01-02'));
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
