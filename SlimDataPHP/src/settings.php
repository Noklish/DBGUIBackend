<?php
header("Access-Control-Allow-Origin: *");
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // jwt settings
        "jwt" => [
            'secret' => 'KEY'
        ],
		
		// Database connection settings
		"db" => ["host" => "127.0.0.1",
		"dbname" => "AnchorManAgement",
		"user" => "root",
		"pass" => ""
		],
    ],
];
