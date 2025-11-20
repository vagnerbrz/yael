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
        Schema::create('correspondences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('carrier')->nullable();
            $table->string('tracking_code')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->enum('status', ['pendente', 'retirado'])->default('pendente');
            $table->timestamp('retrieved_at')->nullable();
            $table->string('retrieved_by_name')->nullable();
            $table->foreignId('registered_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['status', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondences');
    }
};
