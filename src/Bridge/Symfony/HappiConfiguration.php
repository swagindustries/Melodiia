<?php

namespace Biig\Happii\Bridge\Symfony;

use Biig\Happii\HappiiConfigurationInterface;

/**
 * Class HappiConfiguration
 */
final class HappiConfiguration implements HappiiConfigurationInterface
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
                    'base_path' => $api['base_path']
                ];
            }
        }

        return $docConf;
    }
}
