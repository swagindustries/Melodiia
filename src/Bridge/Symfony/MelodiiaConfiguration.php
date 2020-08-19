<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Bridge\Symfony;

use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

final class MelodiiaConfiguration implements MelodiiaConfigurationInterface
{
    public const PREFIX_CONTROLLER = 'melodiia.crud.controller';

    /**
     * @var array
     */
    private $config;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(array $config, RouterInterface $router)
    {
        $this->config = $config;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getApiEndpoints(): array
    {
        $endpoints = [];
        foreach ($this->getApis() as $api) {
            $endpoints[] = $api['base_path'];
        }

        return $endpoints;
    }

    /**
     * If the given request is under a route handle by melodiia, find the first api with it "base_path" that match request path info.
     * Otherwise it return null.
     */
    public function getApiConfigFor(Request $request): ?array
    {
        foreach ($this->getApis() as $apiKey => $apiConfig) {
            $apiBasePath = $apiConfig['base_path'] ?? null;
            if (null === $apiBasePath) {
                continue;
            }

            if (false !== strpos($request->getPathInfo(), $apiBasePath)) {
                $apiConfig['name'] = $apiKey;

                return $apiConfig;
            }
        }

        return null;
    }

    private function getApis(): array
    {
        return $this->config['apis'];
    }
}
