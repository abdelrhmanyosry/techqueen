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
        Schema::table('client_models', function (Blueprint $table) {
            $table->text('scan_files')->nullable();
            $table->text('solidworks_files')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_models', function (Blueprint $table) {
            $table->dropColumn(['scan_files', 'solidworks_files']);
        });
    }
};
