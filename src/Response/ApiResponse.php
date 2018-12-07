<?php

namespace Biig\Happii\Response;

interface ApiResponse
{
    public function httpStatus(): int;
}
