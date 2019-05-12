<?php

namespace Biig\Melodiia\Crud;

interface CrudControllerInterface
{
    /**
     * This attribute should contain the FQCN to the model.
     */
    public const MODEL_ATTRIBUTE = 'melodiia_model';

    /**
     * This attribute should contains the FQCN to the form type to use.
     */
    public const FORM_ATTRIBUTE = 'melodiia_form';

    /**
     * This attribute should contains the serialization group to use for serialization concern of the items to return.
     */
    public const SERIALIZATION_GROUP = 'melodiia_serialization_group';

    /**
     * If specified, it will process a security check using the value of this attribute.
     */
    public const SECURITY_CHECK = 'melodiia_security_check';

    /**
     * Only available for controllers that returns collections of items, it is the number of max item by page.
     * Controllers MUST define a default value. So you don't have to specify it.
     */
    public const MAX_PER_PAGE_ATTRIBUTE = 'melodiia_max_per_page';
}
