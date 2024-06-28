<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes non-critical columns that otherwise just takes up space.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mobitech_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'obu_issuer_id',
                'lane',
                'device_type',
                'device_id',
                'validation_file',
                'signal_code',
                'measured_length',
                'seq_lc',
                'seq_video',
                'app_version',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobitech_transactions', function (Blueprint $table) {
            $table->string('obu_issuer_id')->after('chunk_date')->nullable();
            $table->string('lane')->after('actor_id')->comment('Skyttel (sales place) lane number');
            $table->integer('device_type')->after('lane')->comment('Skyttel (sales place) device type');
            $table->integer('device_id')->after('device_type')->comment('Skyttel (sales place) device ID');
            $table->string('validation_file')->after('device_id')->comment('Skyttel (sales place) validation file');
            $table->integer('signal_code')->after('trailer')->comment('Indicates payment method: Autopass/AutopassFerry/FerryPay (30), invoice (31), SoftPay (32/33), QR-code (35/36)');
            $table->integer('measured_length')->after('signal_code');
            $table->integer('seq_lc')->after('ocr_confidence_front')->comment('Sequence Lane Controller');
            $table->integer('seq_video')->after('seq_lc')->comment('Video sequence number');
            $table->string('app_version')->after('transaction_type')->comment('Mobitech app/API version');
        });
    }
};
