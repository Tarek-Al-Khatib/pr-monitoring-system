# PR Watchdog

## Overview

> PR Watchdog is a Laravel-based tool designed to keep track of pull requests in GitHub repositories. It automates the process of identifying old, unreviewed, or successful PRs and generates easy-to-read reports as txt files and a specific Spreadsheet.

## Why do you need this?

> Maybe you don't if you are a normal user, creating simple repositories for small projects, but!

Imagine working alongside 300 members on the same repository. I would bet 100$ you will not find a PR that is assigned to you as a reviewer! The PRs become messy and unorganizable, so you will surely need this app.

## What does this tool offer?

-   Easy to use API calls to fetch pull requests from your repository of choice.
-   Filter all PRs based on your needs and get an instant report as a .TXT file AND appended to your spreadsheet on Google Docs.
-   No headache :\) All the PRs from the repo are already sinced with the data you will receive. You will not have to worry about being outdated.

## What PRs does it show?

-   Open PRs with successful review status.
-   Open PRs that are created more than 14 days ago.
-   Open PRs that have no reviewers assigned with.
-   Open PRs that requires a review.

## Ok? Awesome, but how do I run it?

1. ```sh
   composer install
   ```
2. Add your api keys in the `.env` file:

```env
  GITHUB_ACCESS_TOKEN="Your Github access token"
  GOOGLE_SERVICE_ENABLED=true
  GOOGLE_CLIENT_ID="Google client id"
  GOOGLE_CLIENT_SECRET="Google client secret"
  SPREADSHEET_ID="Spreadsheet id" # Found in the URL
```

3. And guess what:

```sh
   php artisan server
```
