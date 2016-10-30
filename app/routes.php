<?php

$app->get('/', '\App\Http\Controllers\IndexController:get');

$app->get('/api/accounts', '\App\Http\Controllers\Api\AccountsController:get');
$app->post('/api/alexa', '\App\Http\Controllers\Api\AlexaController:post');
