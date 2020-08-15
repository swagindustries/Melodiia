<?php

namespace SwagIndustries\Melodiia\Crud;

interface CrudableModelInterface
{
    /**
     * @return string|object that have a __toString() method
     */
    public function getId();
}
