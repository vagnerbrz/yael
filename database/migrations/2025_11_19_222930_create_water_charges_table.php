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
        Schema::create('water_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->string('competence', 7); // YYYY-MM
            $table->decimal('amount', 8, 2)->default(0);
            $table->date('due_date');
            $table->enum('status', ['aberto', 'pago', 'atrasado'])->default('aberto');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['apartment_id', 'competence']);
            $table->index(['competence', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_charges');
    }
};
