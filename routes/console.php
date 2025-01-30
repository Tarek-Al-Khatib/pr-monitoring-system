<?php

use Illuminate\Support\Facades\Schedule; 

  
Schedule::command('fetch:pullrequests')->everyMinute();