<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
  protected $primaryKey = 'idBanner';
  protected $fillable = ['title', 'resourceURL', 'state'];
  public $timestamps = false;

}
