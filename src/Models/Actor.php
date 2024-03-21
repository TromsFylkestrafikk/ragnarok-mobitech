<?php

namespace Ragnarok\Mobitech\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'mobitech_actors';
    protected $fillable = [
        'id',
        'name',
    ];
}
