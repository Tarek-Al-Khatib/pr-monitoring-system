<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Http;
use Illuminate\Http\Request;

class PullRequestsActionsController extends Controller
{
    private $repoUrl = "https://api.github.com/repos/woocommerce/woocommerce/pulls";

    public function getOldRequests() {
        try {
            $page = 1;
            $data = [];
            $cutoffTimestamp = strtotime(gmdate('Y-m-d H:i:s')) - (14 * 24 * 60 * 60);

            do {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('GITHUB_ACCESS_TOKEN'),
                    'Accept' => 'application/vnd.github.v3+json'
                ])->get($this->repoUrl, [
                    'per_page' => 30,
                    'page' => $page
                ]);
                $currentPageData = $response->json();
    
                $data = array_merge($data, $currentPageData);
                $page++;
            } while (!empty($currentPageData));
            
            $oldPRs = array_filter($data, function ($pr) use ($cutoffTimestamp) {
                if (!isset($pr['created_at'])) {
                    return false;
                }
                
                $createdTimestamp = strtotime($pr['created_at']);
                if ($createdTimestamp === false) {
                    return false;
                }
                
                return $createdTimestamp < $cutoffTimestamp;
            });
    
            return response()->json(array_values($oldPRs));
        } catch (\Exception $e) {
            return response()->json(["message" => "Error while fetching old pull requests", "error" => $e->getMessage()]);
        }
    }
    
}
