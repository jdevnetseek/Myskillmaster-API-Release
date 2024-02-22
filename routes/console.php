<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('app:initialize:dev', function () {
    if (filled(config('app.key')) && !$this->confirm('It seems the app was already initialize. Do you wish to continue?')) {
        return;
    }

    $commands = collect([
        'key:generate',
        'migrate',
        'app:acl:sync',
        'storage:link',
        'optimize:clear',
    ]);

    foreach ($commands as $command) {
        $this->info('');
        try {
            $this->comment("Executing command: $command");
            $this->call($command);
        } catch (Exception $e) {
            $this->error("Failed: $command" );
        }
        $this->info('');
    }

    $this->comment('App has been initialize. Happy Coding!');
})->describe('Runs all the command needed to kickstart development.');
