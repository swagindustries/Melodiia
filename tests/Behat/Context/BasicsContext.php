<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Tests\Behat\Context;

use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\Tools\SchemaTool;
use Webmozart\Assert\Assert;

class BasicsContext extends AbstractContext
{
    /**
     * @When I make a GET request on :uri
     *
     * @Given I make a :verb request on :uri with the content:
     */
    public function iMakeARequestOn($uri, $verb = 'GET', ?PyStringNode $content = null)
    {
        $this->request($uri, $verb, (string) $content);
    }

    /**
     * @Then the status code is :statusCode
     */
    public function theStatusCodeIs($statusCode)
    {
        $this->assertResponseCode($statusCode);
    }

    /**
     * @Then I should retrieve:
     *
     * @Given the last response contains:
     */
    public function iShouldRetrieve(PyStringNode $string)
    {
        $json = $this->getLastResponse()->getContent();
        $expected = \json_encode(\json_decode($string->getRaw(), true, 512, JSON_THROW_ON_ERROR));

        if ($json !== $expected) {
            if (json_decode($json)) {
                echo "Expected: $expected\n";
                echo "Actual: $json\n";
            }

            throw new \Exception('Expected request result does not match actual result.');
        }
    }

    /**
     * @BeforeScenario
     */
    public function resetDb()
    {
        @unlink(dirname(__DIR__) . '/../TestApplication/var/data.db');

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($metadatas);
    }

    /**
     * @Then I should retrieve a stacktrace formatted in JSON
     */
    public function iShouldRetrieveAStacktraceFormattedInJson()
    {
        $json = $this->getLastResponse()->getContent();
        $content = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        Assert::keyExists($content, 'title');
        Assert::keyExists($content, 'detail');
        Assert::keyExists($content, 'trace');
    }
}
