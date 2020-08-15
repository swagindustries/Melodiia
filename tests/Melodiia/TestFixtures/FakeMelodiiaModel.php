<?php

namespace SwagIndustries\Melodiia\Test\TestFixtures;

use SwagIndustries\Melodiia\Crud\CrudableModelInterface;

class FakeMelodiiaModel implements CrudableModelInterface
{
    public function getId()
    {
        return 1;
    }
}
