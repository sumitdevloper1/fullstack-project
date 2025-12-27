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
        Schema::create('customizations', function (Blueprint $table) {
            $table->id();
            $table->longText('printing_color_mark_json');
            $table->longText('printing_color_print_json');
            $table->string('engraving')->nullable();
            $table->enum('is_specification', ['yes', 'no'])->default('no');
            $table->string('add_accessories_data')->nullable();
            $table->string('remove_accessories_data')->nullable();
           $table->string('unique_code', 50)->unique()->index();
            $table->foreignId('standard_code_id')->constrained('standard_codes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customizations');
    }
};
