<?php

namespace Biig\Melodiia\Test;


use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;

trait MockDispatcherTrait
{
    private function mockDispatch(ObjectProphecy $prophecy, $event, $eventName): MethodProphecy
    {
        // LegacyEventDispatcherProxy exists in Symfony >= 4.3
        if (class_exists(LegacyEventDispatcherProxy::class)) {
            // New Symfony 4.3 EventDispatcher signature
            return $prophecy->dispatch($event, $eventName)->willReturn();
        }

        // Old EventDispatcher signature
        return $prophecy->dispatch($eventName, $event)->willReturn();
    }
}
