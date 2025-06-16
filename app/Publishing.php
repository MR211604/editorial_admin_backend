<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Publishing extends Model
{
    public function author() {
        return $this->hasMany('App\Author', 'id', 'author_id');
    }

    public function category() {
        return $this->hasOne('App\Category', 'id', 'category_id');
    }

}
