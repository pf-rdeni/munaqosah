<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================================
// ROUTE PUBLIK (TANPA LOGIN)
// =====================================================================

// Halaman Login
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');

// Halaman utama redirect ke login
$routes->get('/', 'AuthController::login');

// =====================================================================
// ROUTE BACKEND (MEMBUTUHKAN LOGIN)
// =====================================================================

$routes->group('backend', ['namespace' => 'App\Controllers\Backend'], static function($routes) {
    
    // Dashboard
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    
    // Data Siswa
    $routes->get('siswa', 'Siswa::index');
    $routes->get('siswa/create', 'Siswa::create');
    $routes->post('siswa/store', 'Siswa::store');
    $routes->get('siswa/edit/(:num)', 'Siswa::edit/$1');
    $routes->post('siswa/update/(:num)', 'Siswa::update/$1');
    $routes->get('siswa/delete/(:num)', 'Siswa::delete/$1');
});
