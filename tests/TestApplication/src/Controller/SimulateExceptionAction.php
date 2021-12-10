<?php

declare(strict_types=1);

namespace TestApplication\Controller;

use Doctrine\ORM\EntityManagerInterface;

class SimulateExceptionAction
{
    /**
     * Simulate an exception while processing a controller.
     */
    public function __invoke(EntityManagerInterface $manager, $word)
    {
        throw new \Exception('oupsii, it\'s broken ! :D');
    }
}
