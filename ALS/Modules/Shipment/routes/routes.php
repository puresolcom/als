<?php

// Authentication required routes
$app->group([
    'middleware' => [ 'auth', 'role:manage-driver' ],
], function() use ($app) {
    $app->get('/', 'ShipmentController@list');
});

// Guest routes