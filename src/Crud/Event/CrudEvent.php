<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CrudEvent extends Event
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
