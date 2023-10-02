<?php
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

if (is_readable(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . "//..//");
    $dotenv->load();
}

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../includes/settings.php';
$app = new \Slim\App( $settings );

error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
});

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    'ignore' => ['/qrcode'],
    'users' => [
        'wctokyo2023' => getenv('WCT2023_PASSWD'),
    ]
]));


// Set up dependencies
require __DIR__ . '/../includes/dependencies.php';

// Register middleware
require __DIR__ . '/../includes/middleware.php';

// Register routes
require __DIR__ . '/../includes/routes.php';

// Run app
$app->run();
