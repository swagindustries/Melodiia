<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractContext implements Context
{
    /** @var KernelInterface */
    private $kernel;
    /** @var Generator */
    protected $faker;

    /** @var Response */
    private static $response;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->faker = Factory::create();
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }

    final protected function request(string $uri, string $verb, ?string $rawContent): Response
    {
        $client = $this->getContainer()->get('test.client');

        if ('GET' === $verb) {
            $client->request($verb, $uri);

            return self::$response = $client->getResponse();
        }

        if (null !== $rawContent) {
            json_decode($rawContent, true, 512, \JSON_THROW_ON_ERROR);
        }

        $client->request($verb, $uri, [], [], [], $rawContent);

        return self::$response = $client->getResponse();
    }

    public function assertResponseCode($statusCode)
    {
        $statusCode = (int) $statusCode;
        if ($this->getLastResponse()->getStatusCode() !== $statusCode) {
            throw new \Exception(sprintf('Expected status code "%s" but got "%s"', $statusCode, $this->getLastResponse()->getStatusCode()));
        }
    }

    final protected static function getLastResponse(): Response
    {
        if (null === self::$response) {
            throw new \LogicException('It seems you didn\'t do any requests yet');
        }

        return self::$response;
    }

    protected function lastResponseAsArray()
    {
        return \json_decode(self::getLastResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
