<?php

use Illuminate\Support\Facades\Route;

Route::get('demo', fn () => 'demo')->can('demo.view')->name('demo.index');
