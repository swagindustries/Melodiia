<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Form;

use Symfony\Component\Form\DataMapperInterface;

interface DomainObjectDataMapperInterface extends DataMapperInterface
{
    /**
     * @return mixed
     */
    public function createObject(iterable $form, string $dataClass = null);
}
