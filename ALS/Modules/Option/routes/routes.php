<?php

// Authentication required routes
$app->group(['middleware' => ['auth']], function () use ($app) {
});

// Guest routes