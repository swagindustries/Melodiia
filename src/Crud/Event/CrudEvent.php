<?php

namespace Biig\Melodiia\Crud\Event;

use Symfony\Component\EventDispatcher\Event;

class CrudEvent extends Event
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
