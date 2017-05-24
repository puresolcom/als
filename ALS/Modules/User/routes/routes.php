<?php

/**
 * Authentication and Authorization
 */

// Authentication required routes
$app->group(['middleware' => ['auth']], function () use ($app){

    // Logout
    $app->get('auth/logout', 'AuthController@logout');
    $app->post('auth/logout', 'AuthController@logout');


});

// Guest routes
$app->post('auth/login', 'AuthController@login');