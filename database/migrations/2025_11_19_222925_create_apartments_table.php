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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->cascadeOnDelete();
            $table->string('side', 1);
            $table->string('number', 10);
            $table->enum('status', ['ocupado', 'vago'])->default('ocupado');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['block_id', 'side', 'number']);
            $table->index(['side', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
