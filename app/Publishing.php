<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Publishing extends Model
{

    public function category() {
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function authors() {
        return $this->belongsToMany('App\Author', 'authors_publishings', 'publishing_id', 'author_id');
    }

    public function type() {
        return $this->belongsTo('App\PublishingType', 'publishing_type');
    }

    public function details() {
        return $this->hasOne('App\PublishingDetail', 'publishing_id');
    }

}
