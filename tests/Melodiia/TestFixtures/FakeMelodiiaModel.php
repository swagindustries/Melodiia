<?php

namespace Biig\Melodiia\Test\TestFixtures;

use Biig\Melodiia\Crud\CrudableModelInterface;

class FakeMelodiiaModel implements CrudableModelInterface
{
    public function getId()
    {
        return 1;
    }
}
