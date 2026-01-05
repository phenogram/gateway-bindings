<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);

require $projectRoot . '/vendor/autoload.php';

// Load .env file if it exists (useful for local integration tests)
if (file_exists($projectRoot . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
    $dotenv->safeLoad();
}
