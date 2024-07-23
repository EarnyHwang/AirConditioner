<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirConditioner extends Model
{
    const TYPE_NONE = 0;
    const TYPE_MIEZO = 1;

    protected $fillable = [
        'space_id',
        'uuid',
        'type'
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo('App\Space', 'space_id', 'id');
    }
}
