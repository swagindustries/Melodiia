<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Bridge\Symfony;

use SwagIndustries\Melodiia\Bridge\Symfony\DependencyInjection\MelodiiaExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MelodiiaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new MelodiiaExtension();
    }
}
