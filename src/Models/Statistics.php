<?php

namespace Ragnarok\Mobitech\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    public $timestamps = false;
    protected $table = 'mobitech_statistics';
    protected $fillable = [
        'chunk_date',
        'actor_id',
        'line_id',
        'tour_id',
        'operator_reference',
        'departure',
        'registered',
        'stop_place_id_entry',
        'stop_place_id_exit',
        'statistic_name',
        'statistic_count',
        'automatic_passenger_count',
        'manual_passenger_count',
        'remaining_vehicle_count',
    ];
}
