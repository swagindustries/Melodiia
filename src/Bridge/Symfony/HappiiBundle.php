<?php

namespace Biig\Happii\Bridge\Symfony;

use Biig\Happii\Bridge\Symfony\DependencyInjection\HappiiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HappiiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new HappiiExtension();
    }
}
