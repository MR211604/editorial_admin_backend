<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = "categories";

    public function detail() {
        return $this->hasMany('App\CategoryDetail', 'category_id');
    }

    public function publishings() {
        return $this->hasMany('App\Publishing', 'category_id');
    }

}
