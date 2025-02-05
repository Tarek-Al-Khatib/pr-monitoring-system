<?php

namespace App\Http\Controllers;
use Http;
use Revolution\Google\Sheets\Facades\Sheets;
use Storage;

class PullRequestsActionsController extends Controller
{
    private $pullRequestURL = "https://api.github.com/repos/woocommerce/woocommerce/pulls";
    private $issuesULR = "https://api.github.com/search/issues";

    // private function getPullRequests(){
    //     $spreadsheet = Sheets::spreadsheet(env("PRs_SPREADSHEET_ID"))->sheet("pull_requests");
    //     $sheetData = $spreadsheet->all();
    //     $data = array_values($sheetData);
    //     dd($sheetData);
    // }

    private function loopThroughResponse($q, $url) {
        $data = [];
        $page = 1;
        do {
            $params = [
                "per_page" => 100,
                "page" => $page,
            ];
            
            if ($q != null) {
                $params["q"] = $q;
            }

            $response = Http::withHeaders([
                "Authorization" => "Bearer " . env("GITHUB_ACCESS_TOKEN"),
                "Accept" => "application/vnd.github.v3+json"
                ])->get($url, $params);
                

            if($q == null) {
                $currentPageData = $response->json();
            }
            $responseData = $response->json();

            if (isset($responseData["items"])) {
                $currentPageData = $responseData["items"];
            } else {
                $currentPageData = $responseData;
            }

            $data = array_merge($data, $currentPageData);
            $page++;
        } while (!empty($currentPageData));

        return $data;
    }

    private function saveToSpreadSheet($sheetname, $data){
        try{
            $spreadsheet = Sheets::spreadsheet(env("SPREADSHEET_ID"));
            $allSheets = $spreadsheet->sheetList();

            if(!in_array($sheetname, $allSheets)){
                $spreadsheet->addSheet($sheetname);
            }

            $spreadsheet->sheet($sheetname)->clear();

            $appendableData = [["PR#", "PR Title", "URL"]];
            foreach($data as $pr){
                $appendableData[] = [$pr["number"], $pr["title"], $pr["url"]];
            }

            $sheet = $spreadsheet->sheet($sheetname)->append($appendableData);
            return true;
        }catch(\Exception $e) {
            return "Error saving to spreadsheet" . $e->__tostring();
        }
    }
    private function saveToFile($filename, $data)
    {
        try{      
            $filePath = "github_reports/" . $filename;
            $content = "Total Pull Requests: " . count($data) . "\n\n";

            foreach ($data as $pr) {
                $content .= "* PR# " . $pr["number"] . " - " . $pr["title"] . " - " . "URL: " . $pr["html_url"] . "\n";
            }

            Storage::put($filePath, $content);
            return "File saved: " . $filePath;
        }catch(\Exception $e) {
            return "Error saving to file" . $e->__tostring();
        }
    }
    public function getOldRequests() {
        try {
            $query = "repo:woocommerce/woocommerce type:pr is:open created:<" . now()->subDays(14)->format("Y-m-d");
            $data = $this->loopThroughResponse($query, $this->issuesULR);
            $fileSavingResponse = $this->saveToFile("1-old-pull-requests.txt", $data);
            $spreadsheetSavingResponse = $this->saveToSpreadSheet("old", $data);
            return response()->json(["file-saving"=> $fileSavingResponse, "spreadsheetSaving"=> $spreadsheetSavingResponse]);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error while fetching old pull requests", "error" => $e->getMessage()]);
        }
    }


    public function getReviewRequiredRequests() {
        try {
            $query = "repo:woocommerce/woocommerce type:pr is:open review:required";;
            $data = $this->loopThroughResponse($query, $this->issuesULR);
            $fileSavingResponse = $this->saveToFile("2-review-required-pull-requests.txt", $data);
            $spreadsheetSavingResponse = $this->saveToSpreadSheet("review-required", $data);
            return response()->json(["file-saving"=> $fileSavingResponse, "spreadsheetSaving"=> $spreadsheetSavingResponse]);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error fetching pull requests requiring review", "error" => $e->getMessage()]);
        }
    }
    
    public function getSuccessfulReview() {
        try {
            $query = "repo:woocommerce/woocommerce type:pr is:open status:success";
            $data = $this->loopThroughResponse($query, $this->issuesULR);
            $fileSavingResponse = $this->saveToFile("3-successful-review-pull-requests.txt", $data);
            $spreadsheetSavingResponse = $this->saveToSpreadSheet("successful-review", $data);
            return response()->json(["file-saving"=> $fileSavingResponse, "spreadsheetSaving"=> $spreadsheetSavingResponse]);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error fetching pull requests with successful review", "error" => $e->getMessage()]);
        }
    }


    public function getNoReviewPRs() {
        try {
            $data = $this->loopThroughResponse(null, $this->pullRequestURL);
            $unassignedPRs = array_filter($data, function ($pr) {
                return empty($pr['requested_reviewers']) && empty($pr['requested_teams']);
            });
            $fileSavingResponse = $this->saveToFile("4-no-reviewer-pull-requests.txt", $unassignedPRs);
            $spreadsheetSavingResponse = $this->saveToSpreadSheet("no-review", $unassignedPRs);
            return response()->json(["file-saving"=> $fileSavingResponse, "spreadsheetSaving"=> $spreadsheetSavingResponse]);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error fetching pull requests with no reviews assigned", "error" => $e->getMessage()]);
        }
    }
}
