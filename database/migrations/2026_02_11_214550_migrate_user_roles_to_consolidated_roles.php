<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('colaboradores')
            ->orderBy('id')
            ->each(function (object $colaborador): void {
                DB::table('users')
                    ->where('id', $colaborador->user_id)
                    ->update(['role' => $colaborador->tipo]);
            });

        DB::table('users')->where('role', 'admin')->update(['role' => 'administrativos']);
        DB::table('users')->where('role', 'coordenador')->update(['role' => 'coordenadores']);
        DB::table('users')->where('role', 'prestador')->update(['role' => 'levantadores']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('role', 'administrativos')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'coordenadores')->update(['role' => 'coordenador']);
        DB::table('users')->whereIn('role', ['levantadores', 'projetistas', 'orcamentistas'])
            ->update(['role' => 'prestador']);
    }
};
