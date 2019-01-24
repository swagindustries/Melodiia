<?php

namespace Biig\Melodiia\Response;

use Biig\Melodiia\Response\Model\SerializationContext;

class OkContent implements ApiResponse, SerializationContextAwareInterface
{
    use SerializationContextAwareTrait;

    /** @var mixed */
    private $content;

    public function __construct($content, $serializationGroups = [])
    {
        $this->content = $content;
        $this->serializationContext = new SerializationContext($serializationGroups);
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    public function httpStatus(): int
    {
        return 200;
    }
}
