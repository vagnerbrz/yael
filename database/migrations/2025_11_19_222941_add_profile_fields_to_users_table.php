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
            $table->string('document', 20)->nullable()->unique()->after('email');
            $table->string('phone', 20)->nullable()->after('document');
            $table->enum('role', ['admin', 'resident', 'manager'])->default('resident')->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['document', 'phone', 'role']);
        });
    }
};
