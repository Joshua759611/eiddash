<?php

use Illuminate\Foundation\Inspiring;

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

Artisan::command('synch:test {limit}', function () {
	$limit = $this->argument('limit');
    $this->info($limit);
    $samples = \App\OldSampleView::limit($limit)->offset(0)->get();
    $viralsamples = \App\OldViralsampleView::limit($limit)->offset(0)->get();
})->describe('Test synch limit.');

Artisan::command('synch:eid', function () {
    \App\Synch::synch_eid();
})->describe('Synch Eid results.');

Artisan::command('synch:vl', function () {
    \App\Synch::synch_eid();
})->describe('Synch Vl results.');
