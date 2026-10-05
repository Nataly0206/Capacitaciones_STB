<?php

use App\Services\AvisoCorreoService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('avisos:procesar', function () {
    $servicio = app(AvisoCorreoService::class);

    $generados = $servicio->generarAvisos();
    $enviados = $servicio->enviarPendientes();

    $this->info('Avisos generados: ' . $generados['total']);
    $this->info('Por vencer: ' . $generados['por_vencer']);
    $this->info('Retrasados: ' . $generados['vencida']);
    $this->info('Finalizados: ' . $generados['terminada']);
    $this->info('Procesados: ' . $enviados['procesados']);
    $this->info('Enviados: ' . $enviados['enviados']);
    $this->info('Errores: ' . $enviados['errores']);
})->purpose('Generar y enviar automáticamente avisos pendientes de capacitaciones');

Schedule::command('avisos:procesar')
    ->dailyAt('09:00')
    ->withoutOverlapping();

Artisan::command('rrhh:sincronizar-asistencias', function () {
    $correctas = 0;
    $pendientes = 0;
    \App\Models\EmpleadoCapacitacion::where('estado', 'aprobada')
        ->where('aprobado', 1)
        ->with('capacitacion')
        ->chunkById(100, function ($asignaciones) use (&$correctas, &$pendientes) {
            foreach ($asignaciones as $asignacion) {
                if (app(\App\Services\SincronizarAsistenciaRrhhService::class)->registrarAprobacion($asignacion)) {
                    $correctas++;
                } else {
                    $pendientes++;
                }
            }
        }, 'id_empleado_capacitacion');
    $this->info("Asistencias registradas o existentes: {$correctas}. Pendientes: {$pendientes}.");
    return $pendientes > 0 ? 1 : 0;
})->purpose('Sincronizar aprobaciones con RRHH sin duplicar asistencias');

Schedule::command('rrhh:sincronizar-asistencias')->everyTenMinutes()->withoutOverlapping();
