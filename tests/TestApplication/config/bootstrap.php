<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/../../vendor/autoload.php';

$dotenv = new Dotenv();
if (method_exists($dotenv, 'bootEnv')) {
    $dotenv->bootEnv(dirname(__DIR__) . '/.env');
} else {
    $dotenv->load(dirname(__DIR__) . '/.env');
}
