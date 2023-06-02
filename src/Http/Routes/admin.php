<?php

use Wakazunn\GoogleAuthor\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('google-author', Controllers\GoogleAuthorController::class.'@index');

//增加简体路由地址
Route::get('auth/login', Controllers\GoogleAuthorController::class.'@getLogin1');

//增加简体路由地址
Route::post('auth/login', Controllers\GoogleAuthorController::class.'@postLogin1');