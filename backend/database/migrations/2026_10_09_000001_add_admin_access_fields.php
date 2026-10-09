<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->boolean('is_super_admin')->default(false);
        });

        // Existing installations need one account that can manage admins.
        $firstAdminId = DB::table('admins')->orderBy('id')->value('id');
        if ($firstAdminId !== null) {
            DB::table('admins')->where('id', $firstAdminId)->update(['is_super_admin' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'is_super_admin']);
        });
    }
};
