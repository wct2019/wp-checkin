<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
		
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        'logError' => true,
        'logErrorDetails' => true,
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : '/app/logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
];
