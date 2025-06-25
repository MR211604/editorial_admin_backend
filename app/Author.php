<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    //
    protected $table = 'authors';
    
    public function publishings() {
        return $this->belongsToMany('App\Publishing', 'authors_publishings', 'author_id', 'publishing_id');
    }
}
