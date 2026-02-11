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
        DB::statement('
            UPDATE users u
            INNER JOIN colaboradores c ON c.user_id = u.id
            SET u.role = c.tipo
        ');

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
