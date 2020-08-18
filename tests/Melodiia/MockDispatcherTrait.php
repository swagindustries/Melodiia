<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test;

use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

trait MockDispatcherTrait
{
    private function mockDispatch(ObjectProphecy $prophecy, $event, $eventName): MethodProphecy
    {
        return $prophecy->dispatch($event, $eventName)->willReturn($event);
    }
}
