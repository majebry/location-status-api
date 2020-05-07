<?php

Route::get('entries', 'EntryController@index');

Route::post('entries', 'EntryController@store');
