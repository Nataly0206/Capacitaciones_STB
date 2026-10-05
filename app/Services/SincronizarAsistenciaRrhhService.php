<?php

namespace App\Services;

use App\Models\EmpleadoCapacitacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SincronizarAsistenciaRrhhService
{
    public function registrarAprobacion(EmpleadoCapacitacion $miCapacitacion): bool
    {
        if ($miCapacitacion->estado !== 'aprobada' || (int) $miCapacitacion->aprobado !== 1) {
            return false;
        }

        try {
            $miCapacitacion->loadMissing('capacitacion');
            $idOferta = $miCapacitacion->capacitacion?->id_capacitacion_instructor;

            if (!$idOferta || !$miCapacitacion->id_empleado || !$miCapacitacion->fecha_finalizacion) {
                Log::warning('Asistencia RRHH pendiente: falta empleado, oferta vinculada o fecha de finalización.', [
                    'id_empleado_capacitacion' => $miCapacitacion->getKey(),
                ]);

                return false;
            }

            $fechaRecibida = $miCapacitacion->fecha_finalizacion->format('d/m/Y');
            $conexion = DB::connection('rrhh');
            $conexion->transaction(function () use ($conexion, $miCapacitacion, $idOferta, $fechaRecibida) {
                // En SQL Server, lockForUpdate usa UPDLOCK y HOLDLOCK para
                // serializar la comprobación y evitar duplicados concurrentes.
                $existe = $conexion->table('asistencia_capacitacion')
                    ->where('id_empleado', $miCapacitacion->id_empleado)
                    ->where('id_capacitacion_instructor', $idOferta)
                    ->where('fecha_recibida', $fechaRecibida)
                    ->lockForUpdate()
                    ->first(['id_asistencia_capacitacion']);

                if (!$existe) {
                    $conexion->table('asistencia_capacitacion')->insert([
                        'id_empleado' => $miCapacitacion->id_empleado,
                        'id_capacitacion_instructor' => $idOferta,
                        'fecha_recibida' => $fechaRecibida,
                    ]);
                }
            }, 3);

            return true;
        } catch (Throwable $e) {
            Log::error('Asistencia RRHH pendiente: no se pudo registrar la aprobación.', [
                'id_empleado_capacitacion' => $miCapacitacion->getKey(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
