<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Tests\Behat\Context;

use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class BasicsContext extends AbstractContext
{
    /** @var Response */
    private $response;

    /**
     * @When I make a GET request on :uri
     * @Given I make a :verb request on :uri with the content:
     */
    public function iMakeARequestOn($uri, $verb = 'GET', PyStringNode $content = null)
    {
        $client = $this->getContainer()->get('test.client');

        if ('GET' === $verb) {
            $client->request($verb, $uri);
            $this->response = $client->getResponse();

            return;
        }

        if (empty($content)) {
            throw new \Exception(sprintf('Cannot process request "%s" with no content.', $verb));
        }

        // Just validate json
        json_decode($content = $content->getRaw(), true, 512, JSON_THROW_ON_ERROR);

        $client->request($verb, $uri, [], [], [], $content);
        $this->response = $client->getResponse();
    }

    /**
     * @Then I should retrieve:
     * @Given the last response contains:
     */
    public function iShouldRetrieve(PyStringNode $string)
    {
        $json = $this->response->getContent();
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
     * @Then the status code of last response should be :statusCode
     */
    public function theStatusCodeOfLastResponseShouldBe($statusCode)
    {
        $statusCode = (int) $statusCode;
        if ($this->response->getStatusCode() !== $statusCode) {
            throw new \Exception(sprintf('Expected status code "%s" but got "%s"', $statusCode, $this->response->getStatusCode()));
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
        $json = $this->response->getContent();
        $content = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        Assert::keyExists($content, 'title');
        Assert::keyExists($content, 'detail');
        Assert::keyExists($content, 'trace');
    }
}
