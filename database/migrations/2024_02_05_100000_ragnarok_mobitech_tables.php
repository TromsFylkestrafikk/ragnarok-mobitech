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
        Schema::create('mobitech_transactions', function (Blueprint $table)
        {
            $table->date('chunk_date')->index()->comment('The dated chunk this transaction belongs to');
            $table->string('obu_issuer_id')->nullable();
            $table->integer('line_id')->comment('Line ID');
            $table->integer('actor_id');
            $table->string('lane');
            $table->integer('device_type');
            $table->integer('device_id');
            $table->string('validation_file');
            $table->string('operator_reference');
            $table->integer('tour_id');
            $table->dateTime('departure')->comment('Departure time');
            $table->dateTime('registered')->comment('Transaction registration time');
            $table->string('stop_place_id_entry');
            $table->string('stop_place_id_exit');
            $table->smallInteger('trailer');
            $table->integer('signal_code');
            $table->integer('measured_length');
            $table->string('tariff_class');
            $table->string('nation_lpn_front')->comment('Nationality code according to the front license plate');
            $table->integer('ocr_confidence_front');
            $table->integer('seq_lc');
            $table->integer('seq_video')->comment('Video sequence number');
            $table->string('transaction_type');
            $table->string('app_version');
            $table->smallInteger('is_approved');
            $table->primary([
                'chunk_date',
                'operator_reference',
            ], 'transactions_pk');
        });

        Schema::create('mobitech_statistics', function (Blueprint $table)
        {
            $table->date('chunk_date')->index();
            $table->integer('actor_id');
            $table->integer('line_id')->comment('Line ID');
            $table->integer('tour_id');
            $table->string('operator_reference');
            $table->dateTime('departure')->comment('Departure time');
            $table->dateTime('registered')->comment('Registration time');
            $table->string('stop_place_id_entry');
            $table->string('stop_place_id_exit');
            $table->string('statistic_name')->nullable();
            $table->integer('statistic_count')->nullable();
            $table->integer('automatic_passenger_count')->nullable();
            $table->integer('manual_passenger_count')->nullable();
            $table->integer('remaining_vehicle_count')->nullable();
            $table->primary([
                'chunk_date',
                'operator_reference',
            ], 'statistics_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobitech_transactions');
        Schema::dropIfExists('mobitech_statistics');
    }
};
