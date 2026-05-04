<?php

use Illuminate\Support\Facades\Route;


Route::post('/login', function () {
    return [
        'token' => 'demo-token'
    ];
});