<?php

// Authentication required routes
$app->group([
    'middleware' => ['auth', 'role:manage-driver']
], function () use ($app){
    $app->get('/{shipmentId}', 'ShipmentController@get');
});

// Guest routes