<?php

namespace SwagIndustries\Melodiia\Response\Model;

class SerializationContext
{
    /** @var array */
    private $groups;

    /**
     * @param string|array $groups
     */
    public function __construct($groups)
    {
        $this->groups = [];
        $this->addGroups($groups);
    }

    /**
     * @param array|string $groups
     */
    public function addGroups($groups): void
    {
        $groups = (array) $groups;
        foreach ($groups as $group) {
            $this->groups[] = $group;
        }
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}
