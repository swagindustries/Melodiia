<?php
declare(strict_types=1);

namespace SwagIndustries\Melodiia\Tests\Behat\Context;

use Webmozart\Assert\Assert;

class ErrorContext extends AbstractContext
{
    /**
     * @Given I create a todo without required information
     */
    public function iCreateATodoWithoutRequiredInformations()
    {
        $this->request('/todos', 'POST', '{}');
    }

    /**
     * @Then a violation for :field should exist
     */
    public function aViolationForShouldExists($field)
    {
        $responseDecoded = $this->lastResponseAsArray();
        Assert::keyExists($responseDecoded, 'violations', 'No "validations" key inside response');
        Assert::keyExists($responseDecoded['violations'], $field, "The key $field is missing in violations");
    }
}
