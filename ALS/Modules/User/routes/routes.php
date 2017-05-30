<?php

/**
 * Authentication and Authorization
 */

// Authentication required routes
$app->group(['middleware' => ['auth']], function () use ($app) {
    // Logout
    $app->get('auth/logout', 'AuthController@logout');
    $app->post('auth/logout', 'AuthController@logout');

    // Summary
    $app->get('summary/{userID}', 'UserController@summary');
    $app->get('{userID}/summary', 'UserController@summary');
    $app->get('summary', 'UserController@summary');

    // Managers only
    $app->group(['middleware' => ['role:manage-driver']], function () use ($app) {
        // Get Users
        $app->get('/', 'UserController@list');
    });
});

// Guest routes
$app->post('auth/login', 'AuthController@login');