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
            $table->integer('line_id')->comment('Skyttel (sales place) line ID');
            $table->integer('actor_id')->comment('Skyttel (sales place) actor/operator ID');
            $table->string('lane')->comment('Skyttel (sales place) lane number');
            $table->integer('device_type')->comment('Skyttel (sales place) device type');
            $table->integer('device_id')->comment('Skyttel (sales place) device ID');
            $table->string('validation_file')->comment('Skyttel (sales place) validation file');
            $table->string('operator_reference')->comment('Skyttel (transaction trip) operator reference');
            $table->integer('tour_id')->comment('Skyttel (transaction trip) tour ID');
            $table->dateTime('departure')->comment('Departure time');
            $table->dateTime('registered')->comment('Transaction registration time');
            $table->integer('stop_place_id_entry')->comment('Skyttel stop place ID (start)');
            $table->integer('stop_place_id_exit')->comment('Skyttel stop place ID (exit)');
            $table->boolean('trailer')->default(false);
            $table->integer('signal_code')->comment('Indicates payment method: Autopass/AutopassFerry/FerryPay (30), invoice (31), SoftPay (32/33), QR-code (35/36)');
            $table->integer('measured_length');
            $table->char('tariff_class', 16);
            $table->string('nation_lpn_front')->comment('Skyttel nationality code according to the front license plate');
            $table->integer('ocr_confidence_front')->comment('Optical character recognition read quality of the front license plate');
            $table->integer('seq_lc')->comment('Sequence Lane Controller');
            $table->integer('seq_video')->comment('Video sequence number');
            $table->string('transaction_type')->comment('Transaction method/type');
            $table->string('app_version')->comment('Mobitech app/API version');
            $table->boolean('is_approved')->default(false)->comment('Transaction approval result');
            $table->primary([
                'chunk_date',
                'operator_reference',
            ], 'transactions_pk');
        });

        Schema::create('mobitech_statistics', function (Blueprint $table)
        {
            $table->date('chunk_date')->index()->comment('The dated chunk the statistics data belongs to');
            $table->integer('actor_id')->comment('Skyttel (sales place) actor/operator ID');
            $table->integer('line_id')->comment('Skyttel (sales place) line ID');
            $table->integer('tour_id')->comment('Skyttel (transaction trip) tour ID');
            $table->string('operator_reference')->comment('Unknown operator reference');
            $table->dateTime('departure')->comment('Departure time');
            $table->dateTime('registered')->comment('Registration time');
            $table->integer('stop_place_id_entry')->comment('Skyttel stop place ID (start)');
            $table->integer('stop_place_id_exit')->comment('Skyttel stop place ID (exit)');
            $table->string('statistic_name')->nullable()->comment('Name of statistic count value. Only used in older API versions');
            $table->integer('statistic_count')->nullable()->comment('Statistic count value. Only used in older API versions');
            $table->integer('automatic_passenger_count')->nullable()->comment('Automatic passenger count value. Only used in newer API versions');
            $table->integer('manual_passenger_count')->nullable()->comment('Manual passenger count value. Only used in newer API versions');
            $table->integer('remaining_vehicle_count')->nullable()->comment('Number of vehicles left on land. Only used in newer API versions');
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
