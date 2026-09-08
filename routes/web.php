<?php

//use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\CapacitacionController;
use App\Http\Controllers\CapacitacionModuloController;
use App\Http\Controllers\CapacitacionRecursoController;
use App\Http\Controllers\EvaluacionController;
use App\Http\Controllers\EvaluacionPreguntaController;
use App\Http\Controllers\EvaluacionOpcionController;
use App\Http\Controllers\MiCapacitacionController;
use App\Http\Controllers\MiModuloController;
use App\Http\Controllers\MiEvaluacionController;
use App\Http\Controllers\EmpleadoCapacitacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SeguimientoCapacitacionController;
use App\Http\Controllers\PuestoCapacitacionController;
use App\Http\Controllers\NecesidadCapacitacionController;
use App\Http\Controllers\CapacitacionBuilderController;
use App\Http\Controllers\EjercicioController;
use App\Http\Controllers\EjercicioPreguntaController;
use App\Http\Controllers\EjercicioOpcionController;
use App\Http\Controllers\MiEjercicioController;
use App\Http\Controllers\MiCalificacionController;
use App\Http\Controllers\AvisoCorreoController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active', 'password.changed'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', function () {
        abort(403, 'El acceso al perfil está deshabilitado por administración.');
    })->name('profile.edit');

    Route::patch('/profile', function () {
        abort(403, 'La edición del perfil está deshabilitada por administración.');
    })->name('profile.update');

    Route::delete('/profile', function () {
        abort(403, 'La eliminación de cuenta está deshabilitada por administración.');
    })->name('profile.destroy');

    Route::get('/mis-capacitaciones', [MiCapacitacionController::class, 'index'])->name('mis_capacitaciones.index');
    Route::get('/mis-capacitaciones/{id_empleado_capacitacion}/calificaciones', [MiCalificacionController::class, 'show'])
        ->name('mis_calificaciones.show');
    Route::get('/mis-capacitaciones/{id}', [MiCapacitacionController::class, 'show'])->name('mis_capacitaciones.show');

    Route::get('/mis-capacitaciones/{id_empleado_capacitacion}/modulos/{id_capacitacion_modulo}', [MiModuloController::class, 'show'])->name('mis_modulos.show');
    Route::post('/mis-capacitaciones/{id_empleado_capacitacion}/modulos/{id_capacitacion_modulo}/completar', [MiModuloController::class, 'completar'])->name('mis_modulos.completar');

    Route::post('/mis-capacitaciones/{id_empleado_capacitacion}/modulos/{id_capacitacion_modulo}/avance-contenido', [MiModuloController::class, 'registrarAvanceContenido'])
        ->name('mis_modulos.avance_contenido');

    Route::get('/mis-capacitaciones/{id_empleado_capacitacion}/ejercicios/{id_ejercicio}', [MiEjercicioController::class, 'show'])->name('mis_ejercicios.show');
    Route::post('/mis-capacitaciones/{id_empleado_capacitacion}/ejercicios/{id_ejercicio}', [MiEjercicioController::class, 'submit'])->name('mis_ejercicios.submit');
    Route::get('/mis-capacitaciones/{id_empleado_capacitacion}/ejercicios-intentos/{id_intento}/resultado', [MiEjercicioController::class, 'resultado'])->name('mis_ejercicios.resultado');

    Route::get('/mis-capacitaciones/{id_empleado_capacitacion}/evaluaciones/{id_evaluacion}', [MiEvaluacionController::class, 'show'])->name('mis_evaluaciones.show');
    Route::post('/mis-capacitaciones/{id_empleado_capacitacion}/evaluaciones/{id_evaluacion}', [MiEvaluacionController::class, 'submit'])->name('mis_evaluaciones.submit');
    Route::get('/mis-capacitaciones/{id_empleado_capacitacion}/intentos/{id_intento}/resultado', [MiEvaluacionController::class, 'resultado'])->name('mis_evaluaciones.resultado');
});

    Route::middleware(['auth', 'active', 'password.changed', 'rol:admin,instructor'])->group(function () {

        Route::middleware(['rol:admin'])->group(function () {
            Route::get('/admin', function () {
                return 'Panel administrador';
            })->name('admin');

            Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
            Route::get('/usuarios/crear', [UserController::class, 'create'])->name('usuarios.create');
            Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
            Route::get('/usuarios/{id}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
            Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
            Route::patch('/usuarios/{id}/toggle-estado', [UserController::class, 'toggleEstado'])->name('usuarios.toggleEstado');
            Route::patch('/usuarios/{id}/avisos-correo', [UserController::class, 'actualizarAvisosCorreo'])->name('usuarios.avisos_correo');
            Route::post('/usuarios/{id}/generar-password-temporal', [UserController::class, 'generarPasswordTemporal'])
                ->middleware('throttle:3,1')
                ->name('usuarios.generar_password_temporal');
            Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');

            Route::get('/instructores', [InstructorController::class, 'index'])->name('instructores.index');
            Route::get('/instructores/crear', [InstructorController::class, 'create'])->name('instructores.create');
            Route::post('/instructores', [InstructorController::class, 'store'])->name('instructores.store');
            Route::get('/instructores/{id}/editar', [InstructorController::class, 'edit'])->name('instructores.edit');
            Route::put('/instructores/{id}', [InstructorController::class, 'update'])->name('instructores.update');
            Route::patch('/instructores/{id}/toggle-estado', [InstructorController::class, 'toggleEstado'])->name('instructores.toggleEstado');
        });

        Route::middleware(['capacitacion.instructor'])->group(function () {
            Route::get('/capacitaciones', [CapacitacionController::class, 'index'])->name('capacitaciones.index');
            Route::get('/capacitaciones/archivadas', [CapacitacionController::class, 'archivadas'])->name('capacitaciones.archivadas');
            Route::get('/capacitaciones/crear', [CapacitacionController::class, 'create'])->name('capacitaciones.create');
            Route::post('/capacitaciones', [CapacitacionController::class, 'store'])->name('capacitaciones.store');
            Route::get('/capacitaciones/{id}/editar', [CapacitacionController::class, 'edit'])->name('capacitaciones.edit');
            Route::put('/capacitaciones/{id}', [CapacitacionController::class, 'update'])->name('capacitaciones.update');
            Route::patch('/capacitaciones/{id}/toggle-estado', [CapacitacionController::class, 'toggleEstado'])->name('capacitaciones.toggleEstado');
            Route::patch('/capacitaciones/{id}/archivar', [CapacitacionController::class, 'archivar'])->name('capacitaciones.archivar');
            Route::patch('/capacitaciones/{id}/restaurar-archivada', [CapacitacionController::class, 'restaurarArchivada'])->name('capacitaciones.restaurar_archivada');
            Route::delete('/capacitaciones/{id}', [CapacitacionController::class, 'destroy'])->name('capacitaciones.destroy');

            Route::get('/capacitaciones/{id}/constructor', [CapacitacionBuilderController::class, 'show'])->name('capacitaciones.builder');

            Route::post('/capacitacion-modulos/teoria/imagen', [CapacitacionModuloController::class, 'subirImagenTeoria'])
                ->name('capacitacion_modulos.teoria.imagen');

            Route::post('/capacitacion-modulos/{id}/secciones/guardar-rapida', [CapacitacionModuloController::class, 'guardarSeccionRapida'])
                ->name('capacitacion_modulos.secciones.guardar_rapida');

            Route::get('/capacitaciones/{id_capacitacion}/modulos', [CapacitacionModuloController::class, 'index'])->name('capacitaciones.modulos.index');
            Route::get('/capacitaciones/{id_capacitacion}/modulos/crear', [CapacitacionModuloController::class, 'create'])->name('capacitaciones.modulos.create');
            Route::post('/capacitaciones/{id_capacitacion}/modulos', [CapacitacionModuloController::class, 'store'])->name('capacitaciones.modulos.store');

            Route::get('/capacitacion-modulos/{id}/editar', [CapacitacionModuloController::class, 'edit'])->name('capacitacion_modulos.edit');
            Route::put('/capacitacion-modulos/{id}', [CapacitacionModuloController::class, 'update'])->name('capacitacion_modulos.update');
            Route::patch('/capacitacion-modulos/{id}/toggle-estado', [CapacitacionModuloController::class, 'toggleEstado'])->name('capacitacion_modulos.toggleEstado');
            Route::delete('/capacitacion-modulos/{id}', [CapacitacionModuloController::class, 'destroy'])->name('capacitacion_modulos.destroy');

            Route::get('/capacitacion-modulos/{id_capacitacion_modulo}/recursos', [CapacitacionRecursoController::class, 'index'])->name('capacitacion_modulos.recursos.index');
            Route::get('/capacitacion-modulos/{id_capacitacion_modulo}/recursos/crear', [CapacitacionRecursoController::class, 'create'])->name('capacitacion_modulos.recursos.create');
            Route::post('/capacitacion-modulos/{id_capacitacion_modulo}/recursos', [CapacitacionRecursoController::class, 'store'])->name('capacitacion_modulos.recursos.store');

            Route::get('/capacitacion-recursos/{id}/editar', [CapacitacionRecursoController::class, 'edit'])->name('capacitacion_recursos.edit');
            Route::put('/capacitacion-recursos/{id}', [CapacitacionRecursoController::class, 'update'])->name('capacitacion_recursos.update');
            Route::patch('/capacitacion-recursos/{id}/toggle-estado', [CapacitacionRecursoController::class, 'toggleEstado'])->name('capacitacion_recursos.toggleEstado');
            Route::delete('/capacitacion-recursos/{id}', [CapacitacionRecursoController::class, 'destroy'])->name('capacitacion_recursos.destroy');

            Route::get('/capacitacion-modulos/{id_capacitacion_modulo}/ejercicios', [EjercicioController::class, 'index'])->name('capacitacion_modulos.ejercicios.index');
            Route::get('/capacitacion-modulos/{id_capacitacion_modulo}/ejercicios/crear', [EjercicioController::class, 'create'])->name('capacitacion_modulos.ejercicios.create');
            Route::post('/capacitacion-modulos/{id_capacitacion_modulo}/ejercicios', [EjercicioController::class, 'store'])->name('capacitacion_modulos.ejercicios.store');

            Route::get('/ejercicios/{id}/editar', [EjercicioController::class, 'edit'])->name('ejercicios.edit');
            Route::put('/ejercicios/{id}', [EjercicioController::class, 'update'])->name('ejercicios.update');
            Route::delete('/ejercicios/{id}', [EjercicioController::class, 'destroy'])->name('ejercicios.destroy');

            Route::post('/ejercicios/{id_ejercicio}/preguntas', [EjercicioPreguntaController::class, 'store'])->name('ejercicios.preguntas.store');
            Route::put('/ejercicio-preguntas/{id}', [EjercicioPreguntaController::class, 'update'])->name('ejercicio_preguntas.update');
            Route::delete('/ejercicio-preguntas/{id}', [EjercicioPreguntaController::class, 'destroy'])->name('ejercicio_preguntas.destroy');

            Route::post('/ejercicio-preguntas/{id_ejercicio_pregunta}/opciones', [EjercicioOpcionController::class, 'store'])->name('ejercicio_preguntas.opciones.store');
            Route::put('/ejercicio-opciones/{id}', [EjercicioOpcionController::class, 'update'])->name('ejercicio_opciones.update');
            Route::delete('/ejercicio-opciones/{id}', [EjercicioOpcionController::class, 'destroy'])->name('ejercicio_opciones.destroy');

            Route::get('/capacitacion-modulos/{id_capacitacion_modulo}/evaluaciones', [EvaluacionController::class, 'index'])->name('capacitacion_modulos.evaluaciones.index');
            Route::get('/capacitacion-modulos/{id_capacitacion_modulo}/evaluaciones/crear', [EvaluacionController::class, 'create'])->name('capacitacion_modulos.evaluaciones.create');
            Route::post('/capacitacion-modulos/{id_capacitacion_modulo}/evaluaciones', [EvaluacionController::class, 'store'])->name('capacitacion_modulos.evaluaciones.store');

            Route::get('/evaluaciones/{id}/editar', [EvaluacionController::class, 'edit'])->name('evaluaciones.edit');
            Route::put('/evaluaciones/{id}', [EvaluacionController::class, 'update'])->name('evaluaciones.update');
            Route::patch('/evaluaciones/{id}/toggle-estado', [EvaluacionController::class, 'toggleEstado'])->name('evaluaciones.toggleEstado');
            Route::delete('/evaluaciones/{id}', [EvaluacionController::class, 'destroy'])->name('evaluaciones.destroy');

            Route::get('/evaluaciones/{id_evaluacion}/preguntas', [EvaluacionPreguntaController::class, 'index'])->name('evaluaciones.preguntas.index');
            Route::get('/evaluaciones/{id_evaluacion}/preguntas/crear', [EvaluacionPreguntaController::class, 'create'])->name('evaluaciones.preguntas.create');
            Route::post('/evaluaciones/{id_evaluacion}/preguntas', [EvaluacionPreguntaController::class, 'store'])->name('evaluaciones.preguntas.store');

            Route::get('/evaluacion-preguntas/{id}/editar', [EvaluacionPreguntaController::class, 'edit'])->name('evaluacion_preguntas.edit');
            Route::put('/evaluacion-preguntas/{id}', [EvaluacionPreguntaController::class, 'update'])->name('evaluacion_preguntas.update');
            Route::patch('/evaluacion-preguntas/{id}/toggle-estado', [EvaluacionPreguntaController::class, 'toggleEstado'])->name('evaluacion_preguntas.toggleEstado');
            Route::delete('/evaluacion-preguntas/{id}', [EvaluacionPreguntaController::class, 'destroy'])->name('evaluacion_preguntas.destroy');

            Route::get('/evaluacion-preguntas/{id_evaluacion_pregunta}/opciones', [EvaluacionOpcionController::class, 'index'])->name('evaluacion_preguntas.opciones.index');
            Route::get('/evaluacion-preguntas/{id_evaluacion_pregunta}/opciones/crear', [EvaluacionOpcionController::class, 'create'])->name('evaluacion_preguntas.opciones.create');
            Route::post('/evaluacion-preguntas/{id_evaluacion_pregunta}/opciones', [EvaluacionOpcionController::class, 'store'])->name('evaluacion_preguntas.opciones.store');

            Route::get('/evaluacion-opciones/{id}/editar', [EvaluacionOpcionController::class, 'edit'])->name('evaluacion_opciones.edit');
            Route::put('/evaluacion-opciones/{id}', [EvaluacionOpcionController::class, 'update'])->name('evaluacion_opciones.update');
            Route::delete('/evaluacion-opciones/{id}', [EvaluacionOpcionController::class, 'destroy'])->name('evaluacion_opciones.destroy');

            Route::get('/seguimiento-capacitaciones', [SeguimientoCapacitacionController::class, 'index'])->name('seguimiento_capacitaciones.index');
        });

        Route::middleware(['rol:admin'])->group(function () {
            Route::get('/matriz-puestos-capacitacion', [PuestoCapacitacionController::class, 'index'])->name('puestos_capacitacion.index');

            Route::get('/necesidades-capacitacion', [NecesidadCapacitacionController::class, 'index'])->name('necesidades_capacitacion.index');
            Route::get('/necesidades-capacitacion/exportar', [NecesidadCapacitacionController::class, 'exportar'])->name('necesidades_capacitacion.exportar');

            Route::get('/asignaciones-capacitacion', [EmpleadoCapacitacionController::class, 'index'])->name('empleado_capacitaciones.index');
            Route::get('/asignaciones-capacitacion/crear', [EmpleadoCapacitacionController::class, 'create'])->name('empleado_capacitaciones.create');
            Route::post('/asignaciones-capacitacion', [EmpleadoCapacitacionController::class, 'store'])->name('empleado_capacitaciones.store');
            Route::get('/asignaciones-capacitacion/por-puesto', [EmpleadoCapacitacionController::class, 'porPuesto'])->name('empleado_capacitaciones.por_puesto');
            Route::post('/asignaciones-capacitacion/por-puesto', [EmpleadoCapacitacionController::class, 'storePorPuesto'])->name('empleado_capacitaciones.por_puesto.store');
            Route::get('/asignaciones-capacitacion/{id}/editar', [EmpleadoCapacitacionController::class, 'edit'])->name('empleado_capacitaciones.edit');
            Route::put('/asignaciones-capacitacion/{id}', [EmpleadoCapacitacionController::class, 'update'])->name('empleado_capacitaciones.update');
            Route::delete('/asignaciones-capacitacion/{id}', [EmpleadoCapacitacionController::class, 'destroy'])->name('empleado_capacitaciones.destroy');

            Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
            Route::get('/reportes/exportar/excel', [ReporteController::class, 'excel'])->name('reportes.excel');
            Route::get('/reportes/exportar/pdf', [ReporteController::class, 'pdf'])->name('reportes.pdf');
            Route::get('/reportes/empleados/{empleado}/expediente-pdf', [ReporteController::class, 'expedienteEmpleadoPdf'])
                ->name('reportes.empleado.expediente_pdf');

            Route::get('/avisos-correo', [AvisoCorreoController::class, 'index'])->name('avisos.index');
            Route::put('/avisos-correo/configuracion', [AvisoCorreoController::class, 'configuracionUpdate'])->name('avisos.configuracion.update');
            Route::post('/avisos-correo/generar', [AvisoCorreoController::class, 'generar'])->name('avisos.generar');
            Route::post('/avisos-correo/enviar-pendientes', [AvisoCorreoController::class, 'enviarPendientes'])->name('avisos.enviar_pendientes');
            Route::post('/avisos-correo/reintentar-errores', [AvisoCorreoController::class, 'reintentarErrores'])->name('avisos.reintentar_errores');
        });

        Route::middleware(['capacitacion.instructor'])->group(function () {
            Route::get('/seguimiento-capacitaciones/empleados/{id_empleado}/expediente', [SeguimientoCapacitacionController::class, 'expedienteEmpleado'])
                ->name('seguimiento_capacitaciones.expediente_empleado');
            Route::get('/seguimiento-capacitaciones/{id}', [SeguimientoCapacitacionController::class, 'show'])->name('seguimiento_capacitaciones.show');
            Route::get('/seguimiento-capacitaciones/{id}/ejercicios-intentos/{id_intento}', [SeguimientoCapacitacionController::class, 'ejercicioIntento'])->name('seguimiento_capacitaciones.ejercicio_intento.show');
            Route::put('/seguimiento-capacitaciones/{id}/ejercicios-intentos/{id_intento}/revisar', [SeguimientoCapacitacionController::class, 'revisarEjercicioIntento'])->name('seguimiento_capacitaciones.ejercicio_intento.revisar');
            Route::get('/seguimiento-capacitaciones/{id}/intentos/{id_intento}', [SeguimientoCapacitacionController::class, 'intento'])->name('seguimiento_capacitaciones.intentos.show');
            Route::put('/seguimiento-capacitaciones/{id}/intentos/{id_intento}/revisar', [SeguimientoCapacitacionController::class, 'revisarIntentoEvaluacion'])->name('seguimiento_capacitaciones.intentos.revisar');
        });
    });


require __DIR__.'/auth.php';
