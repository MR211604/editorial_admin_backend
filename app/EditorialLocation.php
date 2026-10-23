<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EditorialLocation extends Model
{
    protected $table = 'editorial_locations';
    protected $primaryKey = 'idLocation';

    protected $fillable = [
        'name',
        'image',
        'address',
        'mapsQuery',
        'state',
        'sort_order',
    ];
}
