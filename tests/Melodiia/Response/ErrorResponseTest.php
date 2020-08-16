<?php

namespace SwagIndustries\Melodiia\Test\Response;

use PHPUnit\Framework\TestCase;
use SwagIndustries\Melodiia\Response\AbstractUserDataErrorResponse;
use SwagIndustries\Melodiia\Response\ErrorResponse;
use Symfony\Component\HttpFoundation\Response;

class ErrorResponseTest extends TestCase
{
    public function testItIsABadRequestHttpResponse()
    {
        $errorResponse = new ErrorResponse([]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $errorResponse->httpStatus());
    }

    public function testItReturnAResponseExtendingRightClass()
    {
        $errorResponse = new ErrorResponse([]);
        $this->assertInstanceOf(AbstractUserDataErrorResponse::class, $errorResponse);
    }

    public function testItContainErrorAtSameLevel()
    {
        $errorResponse = new ErrorResponse(['hello', 'im', 'an', 'error']);
        $this->assertCount(1, $errorResponse->getErrors());
        $errors = $errorResponse->getErrors()[0]->getErrors();
        $this->assertCount(4, $errors);
    }

    public function ifIGiveAnErrorKeyItShouldUseIt()
    {
        $errorResponse = new ErrorResponse([], 'angry_error');
        $this->assertEquals('angry_error', $errorResponse->getErrors()[0]->getPropertyPath());
    }
}
