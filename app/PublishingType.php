<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublishingType extends Model
{
    //
    protected $table = "publishing_types";

    public function publishings() {
        return $this->hasMany('App\Publishing', 'publishing_type');
    }
}
