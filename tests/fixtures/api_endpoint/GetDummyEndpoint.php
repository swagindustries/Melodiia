<?php

namespace App\Tests\fixtures\api_endpoint;

use Biig\Happii\Response\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class GetDummyEndpoint
{
    /**
     * @OA\Get(
     *     path="/callback",
     *     description="Validate that an email was sent successfully.",
     *     @OA\Response(response="200", description="Everything went good.")
     * )
     *
     * @return ApiResponse
     */
    public function __invoke()
    {
        return new class(new Dummy('hello')) implements ApiResponse {
            private $dummy;

            public function __construct($dummy)
            {
                $this->dummy = $dummy;
            }

            public function getDummy()
            {
                return $this->dummy;
            }

            public function httpStatus(): int
            {
                return Response::HTTP_OK;
            }
        };
    }
}

class Dummy
{
    private $foo;

    /**
     * Dummy constructor.
     *
     * @param $foo
     */
    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return mixed
     */
    public function getFoo()
    {
        return $this->foo;
    }

    /**
     * @param mixed $foo
     */
    public function setFoo($foo): void
    {
        $this->foo = $foo;
    }
}
