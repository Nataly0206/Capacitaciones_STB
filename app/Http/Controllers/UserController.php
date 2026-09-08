<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use App\Models\EmpleadoRrhh;
use App\Models\UserRol;
use App\Models\EmpleadoUser;
use App\Models\InstructorRrhh;
use App\Models\InstructorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Capacitacion;
use App\Models\EmpleadoCapacitacion;
use App\Models\HistorialCapacitacionEmpleado;
use App\Services\CredencialTemporalService;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Validation\Rule;
use Throwable;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['rolesSistema', 'empleadoUser.empleado', 'instructorUser.instructor'])
            ->orderBy('id', 'desc')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Rol::where('estado', 1)->orderBy('rol')->get();
        $empleados = EmpleadoRrhh::query()
            ->where('estado', 1)
            ->orderBy('nombre_completo')
            ->get(['id_empleado', 'nombre_completo', 'codigo_empleado']);
        $instructores = $this->instructoresRrhhDisponibles();

        return view('usuarios.create', compact('roles', 'empleados', 'instructores'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $rolSolicitado = Rol::find($request->input('id_rol'));
        $esRolInstructor = strtolower(trim((string) ($rolSolicitado?->rol ?? ''))) === 'instructor';

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9._-]+$/', 'unique:users,username'],
            'email' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/',
                'unique:users,email',
            ],
            'estado' => ['required', 'in:0,1'],
            'id_rol' => ['required', 'exists:rol,id_rol'],
            'id_empleado' => [
                'nullable',
                Rule::exists('rrhh.empleado', 'id_empleado')->where(
                    fn ($query) => $query->where('estado', 1)
                ),
            ],
            'id_instructor' => [
                Rule::requiredIf($esRolInstructor),
                'nullable',
                'integer',
                Rule::exists('rrhh.instructor', 'id_instructor'),
                Rule::unique('instructor_user', 'id_instructor'),
            ],
        ], [
            'username.regex' => 'El usuario solo puede contener letras minúsculas, números, punto, guion y guion bajo.',
            'username.unique' => 'Ese nombre de usuario ya está registrado.',
            'email.regex' => 'El correo debe tener un formato válido, por ejemplo: correo.prueba123@dominio.com',
            'id_empleado.exists' => 'El empleado seleccionado no está disponible o todavía no ha sido sincronizado con Capacitaciones.',
            'id_instructor.required' => 'Debes vincular un instructor de Recursos Humanos para este rol.',
            'id_instructor.exists' => 'El instructor seleccionado no existe en Recursos Humanos.',
            'id_instructor.unique' => 'Ese instructor ya está vinculado a otro usuario.',
        ]);

        if ($request->filled('id_empleado')) {
            $empleadoYaVinculado = EmpleadoUser::where('id_empleado', $request->id_empleado)->exists();

            if ($empleadoYaVinculado) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'id_empleado' => 'Ese empleado ya está vinculado a otro usuario.',
                    ]);
            }
        }

        $credenciales = app(CredencialTemporalService::class);
        $passwordTemporal = $credenciales->generar();

        $user = DB::transaction(function () use ($request, $credenciales, $passwordTemporal, $esRolInstructor) {
            $user = User::create([
                'name' => $request->name,
                'username' => strtolower($request->username),
                'email' => $request->email,
                'password' => $passwordTemporal,
                'estado' => $request->estado,
            ]);

            $credenciales->preparar($user, $passwordTemporal);

            UserRol::create([
                'id_user' => $user->id,
                'id_rol' => $request->id_rol,
            ]);

            $this->sincronizarRolesSpatie($user, [$request->id_rol]);

            if ($request->filled('id_empleado')) {
                EmpleadoUser::create([
                    'id_user' => $user->id,
                    'id_empleado' => $request->id_empleado,
                ]);
            }

            if ($esRolInstructor && $request->filled('id_instructor')) {
                InstructorUser::create([
                    'id_user' => $user->id,
                    'id_instructor' => $request->id_instructor,
                    'fecha_asignacion' => now(),
                ]);
            }

            return $user;
        });

        try {
            $credenciales->enviar($user, $passwordTemporal);
        } catch (Throwable $e) {
            Log::error('No se pudo enviar el correo inicial de credenciales temporales.', [
                'id_usuario' => $user->id,
                'id_administrador' => Auth::id(),
                'excepcion' => $e::class,
                'mensaje' => $e->getMessage(),
            ]);

            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'El usuario fue creado, pero no se pudo enviar el correo de credenciales. Configura el correo SMTP y usa “Generar contraseña temporal” para emitir una nueva.',
            ]);
        }

        Log::notice('Usuario creado con credencial temporal.', [
            'id_usuario' => $user->id,
            'id_administrador' => Auth::id(),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado y credenciales temporales enviadas correctamente.');
    }

    public function edit($id)
    {
        $usuario = User::with(['rolesSistema', 'empleadoUser', 'instructorUser'])->findOrFail($id);
        $roles = Rol::where('estado', 1)->orderBy('rol')->get();
        $empleados = EmpleadoRrhh::query()
            ->where('estado', 1)
            ->orderBy('nombre_completo')
            ->get(['id_empleado', 'nombre_completo', 'codigo_empleado']);
        $instructores = $this->instructoresRrhhDisponibles();

        $usuarioLogueado = Auth::user();
        $esPropioUsuario = (int) $usuarioLogueado->id === (int) $usuario->id;

        return view('usuarios.edit', compact('usuario', 'roles', 'empleados', 'instructores', 'esPropioUsuario'));
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $usuario = User::with(['userRoles', 'empleadoUser', 'instructorUser', 'rolesSistema'])->findOrFail($id);
        $usuarioLogueado = Auth::user();
        $rolSolicitado = Rol::find($request->input('id_rol'));
        $esRolInstructor = strtolower(trim((string) ($rolSolicitado?->rol ?? ''))) === 'instructor';
        $reglaInstructorUnico = Rule::unique('instructor_user', 'id_instructor');

        if ($usuario->instructorUser) {
            $reglaInstructorUnico->ignore($usuario->instructorUser->id_instructor, 'id_instructor');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($usuario->id)],
            'email' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/',
                'unique:users,email,' . $usuario->id,
            ],
            'estado' => ['required', 'in:0,1'],
            'id_rol' => ['required', 'exists:rol,id_rol'],
            'id_empleado' => [
                'nullable',
                Rule::exists('rrhh.empleado', 'id_empleado')->where(
                    fn ($query) => $query->where('estado', 1)
                ),
            ],
            'id_instructor' => [
                Rule::requiredIf($esRolInstructor),
                'nullable',
                'integer',
                Rule::exists('rrhh.instructor', 'id_instructor'),
                $reglaInstructorUnico,
            ],
        ], [
            'username.regex' => 'El usuario solo puede contener letras minúsculas, números, punto, guion y guion bajo.',
            'username.unique' => 'Ese nombre de usuario ya está registrado.',
            'email.regex' => 'El correo debe tener un formato válido, por ejemplo: correo.prueba123@dominio.com',
            'id_empleado.exists' => 'El empleado seleccionado no está disponible en Recursos Humanos.',
            'id_instructor.required' => 'Debes vincular un instructor de Recursos Humanos para este rol.',
            'id_instructor.exists' => 'El instructor seleccionado no existe en Recursos Humanos.',
            'id_instructor.unique' => 'Ese instructor ya está vinculado a otro usuario.',
        ]);

        if ($request->filled('id_empleado')) {
            $empleadoYaVinculado = EmpleadoUser::where('id_empleado', $request->id_empleado)
                ->where('id_user', '!=', $usuario->id)
                ->exists();

            if ($empleadoYaVinculado) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'id_empleado' => 'Ese empleado ya está vinculado a otro usuario.',
                    ]);
            }
        }

        if ($usuarioLogueado->id === $usuario->id && (int) $request->estado === 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'estado' => 'No puedes desactivarte a vos misma mientras estás usando esta cuenta.',
                ]);
        }

        $rolActualId = $usuario->rolesSistema->first()?->id_rol;

        if ($usuarioLogueado->id === $usuario->id && (int) $request->id_rol !== (int) $rolActualId) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_rol' => 'No puedes cambiar tu propio rol desde esta pantalla.',
                ]);
        }

        DB::transaction(function () use ($request, $usuario, $esRolInstructor) {
            $datosActualizar = [
                'name' => $request->name,
                'username' => strtolower($request->username),
                'email' => $request->email,
                'estado' => $request->estado,
            ];

            $usuario->update($datosActualizar);

            UserRol::where('id_user', $usuario->id)->delete();

            UserRol::create([
                'id_user' => $usuario->id,
                'id_rol' => $request->id_rol,
            ]);

            $this->sincronizarRolesSpatie($usuario, [$request->id_rol]);

            EmpleadoUser::where('id_user', $usuario->id)->delete();

            if ($request->filled('id_empleado')) {
                EmpleadoUser::create([
                    'id_user' => $usuario->id,
                    'id_empleado' => $request->id_empleado,
                ]);
            }

            InstructorUser::where('id_user', $usuario->id)->delete();

            if ($esRolInstructor && $request->filled('id_instructor')) {
                InstructorUser::create([
                    'id_user' => $usuario->id,
                    'id_instructor' => $request->id_instructor,
                    'fecha_asignacion' => now(),
                ]);
            }
        });

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function generarPasswordTemporal($id)
    {
        $usuario = User::with('rolesSistema')->findOrFail($id);
        $usuarioLogueado = Auth::user();

        if ((int) $usuarioLogueado->id === (int) $usuario->id) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No puedes generar una contraseña temporal para tu propia sesión administrativa.',
            ]);
        }

        if ((int) $usuario->estado !== 1) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'Activa el usuario antes de generar nuevas credenciales.',
            ]);
        }

        $credenciales = app(CredencialTemporalService::class);
        $passwordTemporal = $credenciales->generar();
        $credencialAnterior = [
            'password' => $usuario->getRawOriginal('password'),
            'debe_cambiar_password' => $usuario->debe_cambiar_password,
            'password_temporal_expira_en' => $usuario->password_temporal_expira_en,
            'remember_token' => $usuario->remember_token,
        ];
        $credenciales->preparar($usuario, $passwordTemporal);

        try {
            $credenciales->enviar($usuario, $passwordTemporal);
        } catch (Throwable $e) {
            $usuario->forceFill($credencialAnterior)->save();

            Log::error('No se pudo enviar el correo de nueva contraseña temporal.', [
                'id_usuario' => $usuario->id,
                'id_administrador' => Auth::id(),
                'excepcion' => $e::class,
                'mensaje' => $e->getMessage(),
            ]);

            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No se pudo enviar el correo. La contraseña anterior continúa funcionando; revisa la configuración SMTP e inténtalo nuevamente.',
            ]);
        }

        Log::notice('Contraseña temporal regenerada por administración.', [
            'id_usuario' => $usuario->id,
            'id_administrador' => Auth::id(),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Se generó una nueva contraseña temporal y fue enviada al usuario.');
    }

    public function toggleEstado($id)
    {
        $usuario = User::with('rolesSistema')->findOrFail($id);
        $usuarioLogueado = Auth::user();

        if ((int) $usuarioLogueado->id === (int) $usuario->id) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No puedes cambiar tu propio estado desde el listado de usuarios.',
            ]);
        }

        $esAdmin = $usuario->rolesSistema->contains(function ($rol) {
            return $rol->rol === 'admin';
        });

        if ($esAdmin) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No se permite inactivar ni reactivar cuentas con rol administrador desde esta opción.',
            ]);
        }

        $usuario->estado = (int) $usuario->estado === 1 ? 0 : 1;
        $usuario->save();

        $mensaje = (int) $usuario->estado === 1
            ? 'Usuario reactivado correctamente.'
            : 'Usuario inactivado correctamente.';

        return redirect()->route('usuarios.index')->with('success', $mensaje);
    }

    public function actualizarAvisosCorreo(Request $request, $id)
    {
        $data = $request->validate([
            'recibe_avisos_capacitaciones' => ['required', 'boolean'],
        ]);

        $usuario = User::findOrFail($id);

        if ((int) $usuario->estado !== 1) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'Solo los usuarios activos pueden configurarse como destinatarios de avisos.',
            ]);
        }

        $usuario->update([
            'recibe_avisos_capacitaciones' => (bool) $data['recibe_avisos_capacitaciones'],
        ]);

        $mensaje = $usuario->recibe_avisos_capacitaciones
            ? "{$usuario->name} recibirá los avisos administrativos por correo."
            : "{$usuario->name} ya no recibirá los avisos administrativos por correo.";

        return redirect()->route('usuarios.index')->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $usuario = User::with(['rolesSistema', 'userRoles', 'empleadoUser', 'instructorUser'])->findOrFail($id);
        $usuarioLogueado = Auth::user();

        if ((int) $usuarioLogueado->id === (int) $usuario->id) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No puedes eliminar tu propia cuenta.',
            ]);
        }

        $esAdmin = $usuario->rolesSistema->contains(function ($rol) {
            return $rol->rol === 'admin';
        });

        if ($esAdmin) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No se permite eliminar cuentas con rol administrador.',
            ]);
        }

        $tieneCapacitacionesCreadas = Capacitacion::where('created_by', $usuario->id)->exists();
        $tieneAsignacionesRealizadas = EmpleadoCapacitacion::where('id_usuario_asigno', $usuario->id)->exists();
        $tieneHistorial = HistorialCapacitacionEmpleado::where('id_user', $usuario->id)->exists();

        if ($tieneCapacitacionesCreadas || $tieneAsignacionesRealizadas || $tieneHistorial) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'Este usuario ya tiene movimientos reales dentro del sistema. No se puede eliminar; solo se puede inactivar.',
            ]);
        }

        DB::transaction(function () use ($usuario) {
            if ($usuario->empleadoUser) {
                $usuario->empleadoUser->delete();
            }

            if ($usuario->instructorUser) {
                $usuario->instructorUser->delete();
            }

            if ($usuario->userRoles()->exists()) {
                $usuario->userRoles()->delete();
            }

            $usuario->syncRoles([]);

            $usuario->delete();
        });

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function sincronizarRolesSpatie(User $usuario, array $idsRoles): void
    {
        $nombresRoles = Rol::whereIn('id_rol', $idsRoles)
            ->get()
            ->map(function ($rol) {
                $nombreRol = $rol->rol
                    ?? $rol->nombre
                    ?? $rol->nombre_rol
                    ?? $rol->name
                    ?? '';

                $nombreRol = strtolower(trim($nombreRol));

                if (in_array($nombreRol, ['empleado', 'empleados', 'user', 'usuario normal'])) {
                    return 'usuario';
                }

                if (in_array($nombreRol, ['administrador'])) {
                    return 'admin';
                }

                return $nombreRol;
            })
            ->filter()
            ->values()
            ->toArray();

        foreach ($nombresRoles as $nombreRol) {
            SpatieRole::firstOrCreate([
                'name' => $nombreRol,
                'guard_name' => 'web',
            ]);
        }

        $usuario->syncRoles($nombresRoles);
    }

    private function instructoresRrhhDisponibles()
    {
        return InstructorRrhh::query()
            ->orderBy('instructor')
            ->get([
                'id_instructor',
                'instructor',
                'institucion',
            ]);
    }

}
