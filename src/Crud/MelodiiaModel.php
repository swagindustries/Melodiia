<?php

namespace SwagIndustries\Melodiia\Crud;

interface MelodiiaModel
{
    /**
     * @return string|object that have a __toString() method
     */
    public function getId();
}
