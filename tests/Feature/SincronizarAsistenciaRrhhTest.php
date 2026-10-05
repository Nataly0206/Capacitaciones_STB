<?php

namespace Tests\Feature;

use App\Models\Capacitacion;
use App\Models\EmpleadoCapacitacion;
use App\Services\SincronizarAsistenciaRrhhService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SincronizarAsistenciaRrhhTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.rrhh' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::purge('rrhh');
        Schema::connection('rrhh')->create('asistencia_capacitacion', function (Blueprint $table) {
            $table->increments('id_asistencia_capacitacion');
            $table->integer('id_empleado');
            $table->integer('id_capacitacion_instructor');
            $table->string('instructor_temporal')->nullable();
            $table->string('fecha_recibida');
        });
    }

    private function asignacion(): EmpleadoCapacitacion
    {
        $capacitacion = new Capacitacion(['id_capacitacion_instructor' => 42]);
        $asignacion = new EmpleadoCapacitacion([
            'id_empleado' => 7, 'estado' => 'aprobada', 'aprobado' => 1,
            'fecha_finalizacion' => '2026-09-15 10:30:00',
        ]);
        $asignacion->setRelation('capacitacion', $capacitacion);

        return $asignacion;
    }

    public function test_registra_fecha_de_finalizacion_y_no_duplica(): void
    {
        $servicio = new SincronizarAsistenciaRrhhService;
        $this->assertTrue($servicio->registrarAprobacion($this->asignacion()));
        $this->assertTrue($servicio->registrarAprobacion($this->asignacion()));
        $registros = DB::connection('rrhh')->table('asistencia_capacitacion')->get();
        $this->assertCount(1, $registros);
        $this->assertSame('15/09/2026', $registros->first()->fecha_recibida);
        $this->assertSame(7, $registros->first()->id_empleado);
        $this->assertSame(42, $registros->first()->id_capacitacion_instructor);
        $this->assertNull($registros->first()->instructor_temporal);
    }

    public function test_registra_nueva_realizacion_anual_del_mismo_curso(): void
    {
        $servicio = new SincronizarAsistenciaRrhhService;
        $this->assertTrue($servicio->registrarAprobacion($this->asignacion()));
        $renovacion = $this->asignacion();
        $renovacion->fecha_finalizacion = '2027-09-15 10:30:00';
        $this->assertTrue($servicio->registrarAprobacion($renovacion));
        $this->assertTrue($servicio->registrarAprobacion($renovacion));
        $fechas = DB::connection('rrhh')->table('asistencia_capacitacion')
            ->orderBy('id_asistencia_capacitacion')->pluck('fecha_recibida')->all();
        $this->assertSame(['15/09/2026', '15/09/2027'], $fechas);
    }

    public function test_no_registra_reprobados_ni_capacitaciones_sin_vinculo(): void
    {
        $servicio = new SincronizarAsistenciaRrhhService;
        $asignacion = $this->asignacion();
        $asignacion->estado = 'reprobada';
        $this->assertFalse($servicio->registrarAprobacion($asignacion));
        $asignacion = $this->asignacion();
        $asignacion->capacitacion->id_capacitacion_instructor = null;
        $this->assertFalse($servicio->registrarAprobacion($asignacion));
        $this->assertSame(0, DB::connection('rrhh')->table('asistencia_capacitacion')->count());
    }

    public function test_fallo_de_rrhh_permite_reintentar(): void
    {
        $servicio = new SincronizarAsistenciaRrhhService;
        Schema::connection('rrhh')->rename('asistencia_capacitacion', 'asistencia_respaldo');
        $this->assertFalse($servicio->registrarAprobacion($this->asignacion()));
        Schema::connection('rrhh')->rename('asistencia_respaldo', 'asistencia_capacitacion');
        $this->assertTrue($servicio->registrarAprobacion($this->asignacion()));
    }
}
