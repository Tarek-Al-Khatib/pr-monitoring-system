<?php

use App\Http\Controllers\PullRequestsActionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("pull-requests")->group(function(){
    Route::get("/old", [PullRequestsActionsController::class,"getOldRequests"]);
    Route::get("/requied-review", [PullRequestsActionsController::class, "getReviewRequiredRequests"]);
    Route::get("/successful-review", [PullRequestsActionsController::class, "getSuccessfulReview"]);
});
