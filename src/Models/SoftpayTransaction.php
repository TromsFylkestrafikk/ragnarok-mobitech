<?php

namespace Ragnarok\Mobitech\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftpayTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'mobitech_softpay_transactions';
    protected $fillable = [
        'chunk_date',
        'operator_reference',
        'departure',
        'line_id',
        'tour_id',
        'stop_place_id_entry',
        'stop_place_id_exit',
        'actor_id',
        'country_code',
        'transaction_number',
        'trailer',
        'tariff_class',
        'receipt_id',
        'batch_number',
        'terminal_id',
        'merchant_org_number',
        'merchant_name',
        'card_scheme',
        'processed',
        'local_time',
        'currency',
        'amount_paid',
        'net_amount',
        'vat',
        'vat_rate',
        'transaction_reference',
    ];
}
