<?php

namespace SwagIndustries\Melodiia\Bridge\Symfony\Form;

use Symfony\Component\Form\DataMapperInterface;

interface DomainObjectDataMapperInterface extends DataMapperInterface
{
    /**
     * @return mixed
     */
    public function createObject(iterable $form, string $dataClass = null);
}
