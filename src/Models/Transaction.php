<?php

namespace Ragnarok\Mobitech\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'mobitech_transactions';
    protected $fillable = [
        'chunk_date',
        'obu_issuer_id',
        'line_id',
        'actor_id',
        'lane',
        'device_type',
        'device_id',
        'validation_file',
        'operator_reference',
        'tour_id',
        'departure',
        'registered',
        'stop_place_id_entry',
        'stop_place_id_exit',
        'trailer',
        'signal_code',
        'measured_length',
        'tariff_class',
        'nation_lpn_front',
        'ocr_confidence_front',
        'seq_lc',
        'seq_video',
        'transaction_type',
        'app_version',
        'netex_id',
        'is_approved',
    ];
}
