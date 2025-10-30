<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRol extends Model
{
  //
  protected $table = "rol_users";
  public $timestamps = false;

  protected $fillable = ['user_id', 'rol_id'];

}
