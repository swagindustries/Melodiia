<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Documentation\Controller;

use SwagIndustries\Melodiia\Exception\MelodiiaLogicException;
use SwagIndustries\Melodiia\Exception\MelodiiaRuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class SwaggerUiController
{
    public const PATH_TO_OPEN_API_FILE_OPTION = 'documentation_file_path';
    /**
     * @var Environment
     */
    private $templating;

    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    public function __invoke(Request $request)
    {
        $response = new Response();
        $path = $request->attributes->get(self::PATH_TO_OPEN_API_FILE_OPTION, null);

        if (null === $path) {
            throw new MelodiiaLogicException(sprintf('The option %s is missing on the documentation route', self::PATH_TO_OPEN_API_FILE_OPTION));
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
