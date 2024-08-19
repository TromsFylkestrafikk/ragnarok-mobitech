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
        Schema::table('mobitech_transactions', function (Blueprint $table) {
            $table->bigInteger('line_id')->comment('Skyttel (sales place) line ID')->change();
            $table->bigInteger('actor_id')->comment('Skyttel (sales place) actor/operator ID')->change();
            $table->bigInteger('tour_id')->comment('Skyttel (transaction trip) tour ID')->change();
            $table->bigInteger('stop_place_id_entry')->comment('Skyttel stop place ID (start)')->change();
            $table->bigInteger('stop_place_id_exit')->comment('Skyttel stop place ID (exit)')->change();
            $table->char('tariff_class')->change();
        });

        Schema::table('mobitech_statistics', function (Blueprint $table) {
            $table->bigInteger('actor_id')->comment('Skyttel (sales place) actor/operator ID')->change();
            $table->bigInteger('line_id')->comment('Skyttel (sales place) line ID')->change();
            $table->bigInteger('tour_id')->comment('Skyttel (transaction trip) tour ID')->change();
            $table->bigInteger('stop_place_id_entry')->comment('Skyttel stop place ID (start)')->change();
            $table->bigInteger('stop_place_id_exit')->comment('Skyttel stop place ID (exit)')->change();
        });

        Schema::table('mobitech_actors', function (Blueprint $table) {
            $table->bigInteger('id')->comment('Unique actor ID')->change();
        });
    }
};
