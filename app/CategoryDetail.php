<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryDetail extends Model
{
    //
    protected $table = "categories_details";
    public $timestamps = false;

    protected $fillable = [
        'about', 
        'editorial_board',
        'policy',
        'norms',
        'process',
        'contact',
        'category_id',
        'language_id'
    ];

    public function category()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }
}
