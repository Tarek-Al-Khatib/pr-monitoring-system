<?php

namespace App\Console\Commands;

use App\Http\Controllers\PullRequestsActionsController;
use Illuminate\Console\Command;

class FetchPullRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:pullrequests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch pull requests and save reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new PullRequestsActionsController();
        
        $controller->getOldRequests();
        $controller->getReviewRequiredRequests();
        $controller->getSuccessfulReview();
        $controller->getNoReviewPRs();
        $this->info('GitHub Pull Request Reports updated successfully!');
    }
}
