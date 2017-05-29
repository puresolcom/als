<?php

// Authentication required routes
$app->group([
    'middleware' => ['auth', 'role:manage-driver|drivers'],
], function () use ($app) {
    $app->get('/list', 'ShipmentController@list');
    $app->get('/{id}', 'ShipmentController@getSingle');
    $app->get('/', 'ShipmentController@get');
});

// Guest routes