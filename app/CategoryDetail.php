<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryDetail extends Model
{
    //
    protected $table = "categories_details";

    public function category() {
        return $this->belongsTo('App\Category', 'category_id');
    }
}
