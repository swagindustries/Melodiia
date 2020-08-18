<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\TestFixtures;

use SwagIndustries\Melodiia\Crud\MelodiiaModel;

class FakeMelodiiaModel implements MelodiiaModel
{
    public function getId()
    {
        return 1;
    }
}
