<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'recibe_avisos_capacitaciones')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('recibe_avisos_capacitaciones')->default(false);
            });
        }

        // Conserva el comportamiento actual al instalar la mejora: los admins
        // activos quedan seleccionados hasta que se cambie la configuración.
        DB::table('users')
            ->where('estado', 1)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('user_rol')
                    ->join('rol', 'rol.id_rol', '=', 'user_rol.id_rol')
                    ->whereColumn('user_rol.id_user', 'users.id')
                    ->where('rol.rol', 'admin');
            })
            ->update(['recibe_avisos_capacitaciones' => 1]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'recibe_avisos_capacitaciones')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('recibe_avisos_capacitaciones');
            });
        }
    }
};
