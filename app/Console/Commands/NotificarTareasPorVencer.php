<?php

namespace App\Console\Commands;

use App\Services\Notificaciones\RecordatorioTareasService;
use Illuminate\Console\Command;

class NotificarTareasPorVencer extends Command
{
    protected $signature = 'tareas:notificar-por-vencer';

    protected $description = 'Notifica a estudiantes sobre tareas pendientes que vencen dentro de 48 horas';

    public function handle(RecordatorioTareasService $recordatorios): int
    {
        $cantidad = $recordatorios->generar();
        $this->info("Se generaron {$cantidad} notificaciones de tareas próximas a vencer.");

        return self::SUCCESS;
    }
}
