<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //$table->string('role')->default('user');
            $table->boolean('status')->default(true); // true = aktif
            $table->boolean('is_superadmin')->default(true); // true = aktif
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //$table->dropColumn('role');
            $table->dropColumn('status');
            $table->dropColumn('is_superadmin');
        });
    }
};
