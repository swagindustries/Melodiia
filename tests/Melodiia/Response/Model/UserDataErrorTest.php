<?php

namespace SwagIndustries\Melodiia\Test\Response\Model;

use SwagIndustries\Melodiia\Response\Model\UserDataError;
use PHPUnit\Framework\TestCase;

class UserDataErrorTest extends TestCase
{
    public function testItGivesBackData()
    {
        $error = new UserDataError('foo.bar', ['error1']);

        $this->assertEquals('foo.bar', $error->getPropertyPath());
        $this->assertEquals(['error1'], $error->getErrors());

        $error->addError('hello');

        $this->assertEquals(['error1', 'hello'], $error->getErrors());
    }
}
