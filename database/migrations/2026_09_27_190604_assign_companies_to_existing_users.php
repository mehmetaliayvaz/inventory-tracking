<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->whereNull('company_id')->get();

        foreach ($users as $user) {
            $companyId = DB::table('companies')->insertGetId([
                'name' => $user->name . ' Şirketi',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $user->id)->update(['company_id' => $companyId]);
        }
    }

    public function down(): void
    {
        DB::table('users')->update(['company_id' => null]);
    }
};