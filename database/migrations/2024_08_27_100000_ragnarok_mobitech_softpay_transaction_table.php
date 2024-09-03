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
        Schema::create('mobitech_softpay_transactions', function (Blueprint $table)
        {
            $table->date('chunk_date')->index()->comment('The dated chunk this Softpay transaction belongs to');
            $table->char('operator_reference')->comment('Mobitech/Skyttel operator reference');
            $table->dateTime('departure')->comment('Departure time');
            $table->bigInteger('line_id')->comment('Skyttel (sales place) line ID');
            $table->bigInteger('tour_id')->comment('Skyttel (transaction trips) tour ID');
            $table->bigInteger('stop_place_id_entry')->comment('Skyttel stop place ID (start)');
            $table->bigInteger('stop_place_id_exit')->comment('Skyttel stop place ID (exit)');
            $table->bigInteger('actor_id')->comment('Skyttel (sales place) actor/operator ID');
            $table->integer('country_code')->comment('Numeric country code (ISO 3166)');
            $table->integer('transaction_number');
            $table->boolean('trailer')->default(false);
            $table->char('tariff_class')->comment('AutoPass code. AP1-9: Classified vehicle length. MC: Motorcycle');
            $table->char('receipt_id');
            $table->char('batch_number');
            $table->char('terminal_id');
            $table->bigInteger('merchant_org_number')->comment('Organization number');
            $table->char('merchant_name');
            $table->char('card_scheme')->comment('Card type used for payment');
            $table->dateTime('processed')->comment('Time for when the payment was processed');
            $table->float('amount_paid');
            $table->float('net_amount');
            $table->float('vat');
            $table->float('vat_rate');
            $table->char('transaction_reference')->comment('Skyttel (transaction trips) operator reference');
            $table->primary([
                'chunk_date',
                'operator_reference',
            ], 'softpay_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobitech_softpay_transactions');
    }
};
