<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //
    public function rol() {
        return $this->belongsTo('App\Rol', 'rol_id');
    }

}
