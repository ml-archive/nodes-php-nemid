<?php

Route::group(['prefix' => '/nemid'], function () {
    Route::get('/', ['uses' => 'NemIdController@view', 'as' => 'nemid.login']);
    Route::post('/callback', ['uses' => 'NemIdController@callback', 'as' => 'nemid.callback']);
});
