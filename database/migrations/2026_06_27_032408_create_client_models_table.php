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
    Schema::create('client_models', function (Blueprint $table) {
        $table->id();

        $table->foreignId('client_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('piece_name');

        $table->text('notes')->nullable();

        $table->text('modification')->nullable();

        $table->date('receiving_date');

        $table->date('delivery_date');

        $table->decimal('deposit',10,2)
            ->default(0);

        $table->decimal('price',10,2);

        $table->string('status')
            ->default('in_progress');

        $table->timestamp('completed_at')
            ->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_models');
    }
};
