<?php

use App\Controllers\AuthController;
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('api', ['filter' => 'cors'], static function ($routes) {
    $routes->get('auth/user', [AuthController::class, 'getUser']);
    $routes->post('auth/token', [AuthController::class, 'getToken']);

    $routes->group('', ['filter' => 'app-jwt'], static function ($routes) {
        // Add protected API routes here when starting a new project.
    });
});
