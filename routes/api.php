<?php

use Illuminate\Http\Request;

// user routes
Route::post('user/create', 'ApiController@createUser');
Route::post('user/login', 'ApiController@userLogin');
Route::post('user/loan', 'ApiController@userLoan');
