<?php

use App\Http\Controllers\PullRequestsActionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("pull-requests")->group(function(){
    Route::get("/old", [PullRequestsActionsController::class,"getOldRequests"]);
});
