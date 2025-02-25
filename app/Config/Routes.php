<?php

namespace Config;

$routes = Services::routes();

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Coaster routes
    $routes->post('coasters', 'CoasterController::create');
    $routes->put('coasters/(:segment)', 'CoasterController::update/$1');
    $routes->get('coasters/(:segment)', 'CoasterController::show/$1');
    $routes->get('statistics', 'CoasterController::statistics');

    // Wagon routes
    $routes->post('coasters/(:segment)/wagons', 'WagonController::create/$1');
    $routes->delete('coasters/(:segment)/wagons/(:segment)', 'WagonController::delete/$1/$2');
});

// CLI Routes
$routes->cli('monitor', 'App\CLI\MonitoringService::run');

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
