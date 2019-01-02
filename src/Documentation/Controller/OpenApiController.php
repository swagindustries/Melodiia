<?php

namespace Biig\Melodiia\Documentation\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Really. Nelmio api docs would be great.
 * But.... https://github.com/api-platform/core/issues/1757.
 */
class OpenApiController
{
    /**
     * @var \Twig_Environment
     */
    private $templating;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(\Twig_Environment $templating, RouterInterface $router)
    {
        $this->templating = $templating;
        $this->router = $router;
    }

    public function __invoke(Request $request)
    {
        $response = new Response();

        $queryParams = '';
        if (!empty($request->query)) {
            foreach ($request->query as $key => $param) {
                empty($queryParams) ? $queryParams = '?' : $queryParams .= '&';
                $queryParams .= $key . '=' . $param;
            }
        }

        $response->setContent($this->templating->render(
            '@Melodiia/openapi.html.twig',
            [
                'url_to_json' => $this->router->generate(
                    str_replace('html', 'json', $request->get('_route')),
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ) . $queryParams,
            ]
        ));

        return $response;
    }
}
