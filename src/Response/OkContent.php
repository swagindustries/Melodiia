<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

use SwagIndustries\Melodiia\Response\Model\SerializationContext;

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

    public function isCollection(): bool
    {
        return !is_array($this->content) && is_iterable($this->content);
    }

    public function httpStatus(): int
    {
        return 200;
    }

    public function headers(): array
    {
        return [];
    }
}
