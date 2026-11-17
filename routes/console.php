<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Revisa cada hora las tareas pendientes que vencerán dentro de 48 horas.
Schedule::command('tareas:notificar-por-vencer')->hourly();
