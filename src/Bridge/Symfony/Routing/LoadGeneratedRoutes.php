<?php

namespace Biig\Melodiia\Bridge\Symfony\Routing;

use BiiG\Melodiia\Exception\MelodiiaRuntimeIssueException;
use Biig\Melodiia\MelodiiaConfigurationInterface;
use Nekland\Tools\StringTools;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class LoadGeneratedRoutes
{
    private $config;
    private $isLoaded = false;

    public function __construct(MelodiiaConfigurationInterface $config)
    {
        $this->config = $config;
    }

    public function loadRoutes(): RouteCollection
    {
        if (true === $this->isLoaded) {
            throw new MelodiiaRuntimeIssueException(
                'The application tried to load routing 2 times; which is not allowed and could result from an infinite loop'
            );
        }
        $routes = new RouteCollection();

        foreach ($this->config->getDocumentationConfig() as $apiName => $conf) {
            $path = $conf['base_path'];

            if (!StringTools::endsWith($path, '/')) {
                $path .= '/';
            }

            $path .= 'docs';

            $htmlRoute = new Route(
                $path . '.html',
                ['_controller' => 'melodiia.' . $apiName . '.open_api_view_controller']
            );
            $jsonRoute = new Route(
                $path . '.json',
                ['_controller' => 'melodiia.' . $apiName . '.open_api_json_controller']
            );

            $routes->add('melodiia_' . $apiName . '_docs_json', $jsonRoute);
            $routes->add('melodiia_' . $apiName . '_docs_html', $htmlRoute);
        }

        $this->isLoaded = true;

        return $routes;
    }
}
