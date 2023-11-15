<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require 'libs/db_config.php';

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
$app = AppFactory::create();
$app->setBasePath("/apirest");
// Add Routing Middleware
$app->addRoutingMiddleware();

/*
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.

 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello");
    return $response;
});
$app->post('/TE', function (Request $request, Response $response, $args) {
    require ("controller/InvoiceController.php");
    $TE = new InvoiceController();

    $result = $TE->saveTE($request);
    $response->getBody()->write(json_encode($result));
    return $response
                    ->withHeader('Content-Type', 'application/json');
});
$app->post('/FE', function (Request $request, Response $response, $args) {
    require ("controller/InvoiceController.php");
    $FE = new InvoiceController();

    $result = $FE->saveFE($request);
     $response->getBody()->write(json_encode($result));
    return $response
                    ->withHeader('Content-Type', 'application/json');
});
$app->post('/NC', function (Request $request, Response $response, $args) {
    require ("controller/CreditMemoController.php");
    $NC = new CreditMemoController();

    $result = $NC->saveNC($request);
    $response->getBody()->write(json_encode($result));
    return $response
                    ->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();