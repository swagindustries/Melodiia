<?php

namespace Biig\Melodiia\Bridge\Symfony;

use Biig\Melodiia\Bridge\Symfony\DependencyInjection\MelodiiaExtension;
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
