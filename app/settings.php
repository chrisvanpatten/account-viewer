<?php

return [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
		'determineRouteBeforeAppMiddleware' => true,

		// Twig settings
		'view' => [
			'template_path' => __DIR__ . '/../resources/views',
			'twig' => [
				'cache' => false,
				'debug' => true,
				'autoescape' => false,
			],
		],
    ],
];
