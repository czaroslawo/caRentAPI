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
        Schema::create('rent_item_posters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('location');
            $table->string('transmission');
            $table->integer('seats');
            $table->integer('power');
            $table->integer('year');
            $table->decimal('price', 10, 2);
            $table->float('rating')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_item_posters');
    }
};
