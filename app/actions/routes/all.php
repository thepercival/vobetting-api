<?php

// Routes
$app->any('/voetbal/external/{resourceType}[/{id}]', \Voetbal\Action\Slim\ExternalHandler::class );
$app->any('/voetbal/{resourceType}[/{id}]', \Voetbal\Action\Slim\Handler::class );

$app->group('/auth', function () use ($app) {
    $app->post('/register', 'App\Action\Auth:register');
    $app->post('/login', 'App\Action\Auth:login');
    /*$app->post('/auth/activate', 'App\Action\Auth:activate');*/
    $app->post('/passwordreset', 'App\Action\Auth:passwordreset');
    $app->post('/passwordchange', 'App\Action\Auth:passwordchange');
});

$app->group('/users', function () use ($app) {
    $app->get('', 'App\Action\User:fetch');
    $app->get('/{id}', 'App\Action\User:fetchOne');
});

$app->get('/betlines', 'App\Action\BetLine:fetch');
$app->get('/laybacks', 'App\Action\LayBack:fetch');

$app->group('/bookmakers', function () use ($app) {
    $app->post('', 'App\Action\Bookmaker:add');
    $app->get('', 'App\Action\Bookmaker:fetch');
    $app->get('/{id}', 'App\Action\Bookmaker:fetchOne');
    $app->put('/{id}', 'App\Action\Bookmaker:edit');
    $app->delete('/{id}', 'App\Action\Bookmaker:remove');
});