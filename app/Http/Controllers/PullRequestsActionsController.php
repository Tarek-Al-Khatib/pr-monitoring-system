<?php

namespace App\Http\Controllers;

use Http;
use Illuminate\Http\Request;

class PullRequestsActionsController extends Controller
{
    private $repoUrl = "https://api.github.com/repos/woocommerce/woocommerce/pulls";

    public function getOldRequests() {
        try {
            $response = Http::get($this->repoUrl);
            $data = $response->json();

            $oldPRs = array_filter($data, function($pr){
                return now()->diffInDays($pr['created_at'] > 14);
            });

            return response()->json($oldPRs);
            
        }catch(\Exception $e){
            return response()->json(["message"=>"Error while fetching old messages", "error"=>$e->getMessage()]);
        }
    }
}
