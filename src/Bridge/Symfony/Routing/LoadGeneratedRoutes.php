<?php

namespace Biig\Happii\Bridge\Symfony\Routing;

use BiiG\Happii\Exception\HappiiRuntimeIssueException;
use Biig\Happii\HappiiConfigurationInterface;
use Nekland\Tools\StringTools;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class LoadGeneratedRoutes
{
    private $config;
    private $isLoaded = false;
    public function __construct(HappiiConfigurationInterface $config)
    {
        $this->config = $config;
    }

    public function loadRoutes(): RouteCollection
    {
        if (true === $this->isLoaded) {
            throw new HappiiRuntimeIssueException(
                'The application tried to load routing 2 times; which is not allowed and could result from an infinite loop'
            );
        }
        $routes = new RouteCollection();

        foreach($this->config->getDocumentationConfig() as $apiName => $conf) {
            $path = $conf['base_path'];

            if (!StringTools::endsWith($path, '/')) {
                $path .= '/';
            }

            $path .= 'docs';

            $htmlRoute = new Route(
                $path . '.html',
                [ '_controller' => 'happii.' . $apiName . '.open_api_view_controller']
            );
            $jsonRoute = new Route(
                $path . '.json',
                [ '_controller' => 'happii.' . $apiName . '.open_api_json_controller']
            );

            $routes->add('happii_' . $apiName . '_docs_json', $jsonRoute);
            $routes->add('happii_' . $apiName . '_docs_html', $htmlRoute);
        }

        $this->isLoaded = true;

        return $routes;
    }
}
