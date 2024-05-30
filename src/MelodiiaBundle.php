<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia;

use SwagIndustries\Melodiia\DependencyInjection\MelodiiaExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MelodiiaBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MelodiiaExtension();
    }
}
