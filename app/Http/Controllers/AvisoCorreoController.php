<?php

namespace App\Http\Controllers;

use App\Models\AvisoCorreo;
use App\Models\ConfiguracionAviso;
use Illuminate\Http\Request;

class AvisoCorreoController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->query('estado', '');
        $tipoAviso = $request->query('tipo_aviso', '');
        $destinatarioTipo = $request->query('destinatario_tipo', '');
        $buscar = $request->query('buscar', '');
        $fechaDesde = $request->query('fecha_desde', '');
        $fechaHasta = $request->query('fecha_hasta', '');

        $configuraciones = ConfiguracionAviso::orderBy('tipo_aviso')->get();
        $consultaAvisos = AvisoCorreo::with([
            'empleadoCapacitacion.empleado',
            'empleadoCapacitacion.capacitacion',
        ]);

        if ($estado !== '') {
            $consultaAvisos->where('estado', $estado);
        }

        if ($tipoAviso !== '') {
            $consultaAvisos->where('tipo_aviso', $tipoAviso);
        }

        if ($destinatarioTipo !== '') {
            $consultaAvisos->where('destinatario_tipo', $destinatarioTipo);
        }

        if ($buscar !== '') {
            $consultaAvisos->where(function ($query) use ($buscar) {
                $query->where('destinatario_email', 'like', '%' . $buscar . '%')
                    ->orWhere('asunto', 'like', '%' . $buscar . '%')
                    ->orWhere('mensaje', 'like', '%' . $buscar . '%')
                    ->orWhereHas('empleadoCapacitacion.empleado', function ($subQuery) use ($buscar) {
                        $subQuery->where('nombre_completo', 'like', '%' . $buscar . '%')
                            ->orWhere('correo', 'like', '%' . $buscar . '%');
                    })
                    ->orWhereHas('empleadoCapacitacion.capacitacion', function ($subQuery) use ($buscar) {
                        $subQuery->where('capacitacion', 'like', '%' . $buscar . '%');
                    });
            });
        }

        if ($fechaDesde !== '') {
            $consultaAvisos->whereDate('fecha_programada', '>=', $fechaDesde);
        }

        if ($fechaHasta !== '') {
            $consultaAvisos->whereDate('fecha_programada', '<=', $fechaHasta);
        }

        $avisos = $consultaAvisos
            ->orderBy('id_aviso_correo', 'desc')
            ->paginate(20)
            ->withQueryString();

        $resumen = [
            'total' => AvisoCorreo::count(),
            'asignaciones' => AvisoCorreo::where('tipo_aviso', 'asignada')->count(),
            'retrasadas' => AvisoCorreo::where('tipo_aviso', 'vencida')->count(),
            'pendientes' => AvisoCorreo::where('estado', 'pendiente')->count(),
            'enviados' => AvisoCorreo::where('estado', 'enviado')->count(),
            'errores' => AvisoCorreo::where('estado', 'error')->count(),
        ];

        return view('avisos.index', compact(
            'configuraciones',
            'avisos',
            'resumen',
            'estado',
            'tipoAviso',
            'destinatarioTipo',
            'buscar',
            'fechaDesde',
            'fechaHasta'
        ));
    }
}
