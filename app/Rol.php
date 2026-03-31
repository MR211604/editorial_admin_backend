<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    //
    protected $primaryKey = 'idRol';

    protected $fillable = [
        'name',
        'description'
    ];
    public $timestamps = false;
    protected $table = "rol";
}
