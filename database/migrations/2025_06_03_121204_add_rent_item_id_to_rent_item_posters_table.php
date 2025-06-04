<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('rent_item_posters', function (Blueprint $table) {
            // Sprawdź czy kolumna już nie istnieje, jeśli chcesz uniknąć błędu
            if (!Schema::hasColumn('rent_item_posters', 'rent_item_id')) {
                $table->unsignedBigInteger('rent_item_id')->nullable()->after('id');
                $table->foreign('rent_item_id')->references('id')->on('rent_items')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('rent_item_posters', function (Blueprint $table) {
            $table->dropForeign(['rent_item_id']);
            $table->dropColumn('rent_item_id');
        });
    }
};
