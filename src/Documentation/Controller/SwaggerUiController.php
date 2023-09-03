<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Documentation\Controller;

use SwagIndustries\Melodiia\Exception\MelodiiaLogicException;
use SwagIndustries\Melodiia\Exception\MelodiiaRuntimeException;
use SwagIndustries\Melodiia\MelodiiaConfiguration;
use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class SwaggerUiController
{
    /** @deprecated use melodiia configuration instead */
    public const PATH_TO_OPEN_API_FILE_OPTION = 'documentation_file_path';

    /**
     * @var Environment
     */
    private $templating;

    /** @var MelodiiaConfigurationInterface */
    private $configuration;

    public function __construct(Environment $templating, MelodiiaConfigurationInterface $configuration)
    {
        $this->templating = $templating;
        $this->configuration = $configuration;
    }

    public function __invoke(Request $request)
    {
        $response = new Response();

        $path = $this->configuration->getApiConfigFor($request)[MelodiiaConfiguration::CONFIGURATION_OPENAPI_PATH] ?? null;

        if (null === $path) {
            $path = $request->attributes->get(self::PATH_TO_OPEN_API_FILE_OPTION, null);
            if (null !== $path) {
                @trigger_error('Using a route attribute to define the documentation path is deprecated since Melodiia 0.9.0 and will be removed in 1.0.0.', \E_USER_DEPRECATED);
            }
        }

        if (null === $path) {
            throw new MelodiiaLogicException(sprintf('Either you forgot to specify the option %s on your API endpoint configuration or this route is not under the path of you configuration', MelodiiaConfiguration::CONFIGURATION_OPENAPI_PATH));
        }

        if (!file_exists($path)) {
            throw new MelodiiaLogicException(sprintf('The documentation file "%s" does not exist', $path));
        }

        try {
            $documentationArray = Yaml::parseFile(
                $path,
                Yaml::PARSE_OBJECT | Yaml::PARSE_OBJECT_FOR_MAP
            );
        } catch (ParseException $exception) {
            throw new MelodiiaRuntimeException('Impossible to parse YAML definition file. See previous exception', 0, $exception);
        }

        $json = \json_encode($documentationArray, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $response->setContent($this->templating->render(
            '@Melodiia/openapi.html.twig',
            ['json' => $json]
        ));

        return $response;
    }
}
