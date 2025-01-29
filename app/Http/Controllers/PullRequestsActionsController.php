<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Http;
use Illuminate\Http\Request;
use Storage;

class PullRequestsActionsController extends Controller
{
    private $pullRequestURL = "https://api.github.com/repos/woocommerce/woocommerce/pulls";
    private $issuesULR = "https://api.github.com/search/issues";


    private function saveToFile($filename, $data)
    {
        $filePath = "github_reports/" . $filename;
        $content = "Total Pull Requests: " . count($data) . "\n\n";

        foreach ($data as $pr) {
            $content .= "* PR# " . $pr["number"] . " - " . $pr["title"] . " - " . "URL: " . $pr["html_url"] . "\n";
        }

        Storage::put($filePath, $content);
        return response()->json(["message" => "File saved: " . $filePath]);
    }
    public function getOldRequests() {
        try {
            $page = 1;
            $data = [];
            $cutoffTimestamp = strtotime(gmdate("Y-m-d H:i:s")) - (14 * 24 * 60 * 60);

            do {
                $response = Http::withHeaders([
                    "Authorization" => "Bearer " . env("GITHUB_ACCESS_TOKEN"),
                    "Accept" => "application/vnd.github.v3+json"
                ])->get($this->pullRequestURL, [
                    "per_page" => 100,
                    "page" => $page,
                ]);
                $currentPageData = $response->json();
    
                $data = array_merge($data, $currentPageData);
                $page++;
            } while (!empty($currentPageData));
            
            $oldPRs = array_filter($data, function ($pr) use ($cutoffTimestamp) {
                if (!isset($pr["created_at"])) {
                    return false;
                }
                
                $createdTimestamp = strtotime($pr["created_at"]);
                if ($createdTimestamp === false) {
                    return false;
                }
                
                return $createdTimestamp < $cutoffTimestamp;
            });
    
            return $this->saveToFile("1-old-pull-requests.txt", $oldPRs);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error while fetching old pull requests", "error" => $e->getMessage()]);
        }
    }


    public function getReviewRequiredRequests() {
        try {
            $page = 1;
            $data = [];
            $query = "repo:woocommerce/woocommerce type:pr is:open review:required";
    
            do {
                $response = Http::withHeaders([
                    "Authorization" => "Bearer " . env("GITHUB_ACCESS_TOKEN"),
                    "Accept" => "application/vnd.github.v3+json"
                ])->get($this->issuesULR, [
                    "q" => $query,
                    "per_page" => 100,
                    "page" => $page,
                ]);
    
                $currentPageData = $response->json()["items"];
                $data = array_merge($data, $currentPageData);
                $page++;
    
            } while (!empty($currentPageData));
    
            return $this->saveToFile("2-review-required-pull-requests.txt", $data);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error fetching pull requests requiring review", "error" => $e->getMessage()]);
        }
    }
    
    public function getSuccessfulReview() {
        try {
            $page = 1;
            $data = [];
            $query = "repo:woocommerce/woocommerce type:pr is:open status:success";
    
            do {
                $response = Http::withHeaders([
                    "Authorization" => "Bearer " . env("GITHUB_ACCESS_TOKEN"),
                    "Accept" => "application/vnd.github.v3+json"
                ])->get($this->issuesULR, [
                    "q" => $query,
                    "per_page" => 100,
                    "page" => $page,
                ]);
    
                $currentPageData = $response->json()["items"];
                $data = array_merge($data, $currentPageData);
                $page++;
    
            } while (!empty($currentPageData));
    
            return $this->saveToFile("3-successful-review-pull-requests.txt", $data);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error fetching pull requests with successful review", "error" => $e->getMessage()]);
        }
    }


    public function getNoReviewPRs() {
        try {
            $page = 1;
            $data = [];
    
            do {
                $response = Http::withHeaders([
                    "Authorization" => "Bearer " . env("GITHUB_ACCESS_TOKEN"),
                    "Accept" => "application/vnd.github.v3+json"
                ])->get($this->pullRequestURL, [
                    "per_page" => 100,
                    "state"=>"open",
                    "page" => $page,
                ]);
    
                $currentPageData = $response->json();
                $data = array_merge($data, $currentPageData);
                $page++;
    
            } while (!empty($currentPageData));

            $unassignedPRs = array_filter($data, function ($pr) {
                return empty($pr['requested_reviewers']) && empty($pr['requested_teams']);
            });
    
    
            return $this->saveToFile("4-no-reviewer-pull-requests.txt", $unassignedPRs);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error fetching pull requests with successful review", "error" => $e->getMessage()]);
        }
    }
}
