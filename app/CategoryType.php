<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryType extends Model
{
    //
    protected $table = "categories_types";
    public $timestamps = false;

    protected $fillable = [
        'name', 
    ];
}
