<?php

namespace Biig\Melodiia\Bridge\Symfony;

use Biig\Melodiia\MelodiiaConfigurationInterface;

/**
 * Class MelodiiaConfiguration.
 */
final class MelodiiaConfiguration implements MelodiiaConfigurationInterface
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentationConfig(): array
    {
        $apis = $this->config['apis'];
        $docConf = [];

        foreach ($apis as $name => $api) {
            if ($api['enable_doc']) {
                $docConf[$name] = [
                    'paths' => $api['paths'],
                    'base_path' => $api['base_path'],
                ];
            }
        }

        return $docConf;
    }

    /**
     * {@inheritdoc}
     */
    public function getApiEndpoints(): array
    {
        $endpoints = [];
        $apis = $this->config['apis'];

        foreach ($apis as $api) {
            $endpoints[] = $api['base_path'];
        }

        return $endpoints;
    }
}
