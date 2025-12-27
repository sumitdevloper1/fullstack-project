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
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->string('file')->nullable();
            $table->string('note')->nullable();
            $table->string('capacity')->nullable();
            $table->string('neck_size')->nullable();
            $table->string('item_name')->nullable();
            $table->text('item_description')->nullable();
            $table->string('remarks')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('pack_size')->nullable();
            $table->string('moq')->nullable();
            $table->foreignId('customization_id')->constrained('customizations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specifications');
    }
};
