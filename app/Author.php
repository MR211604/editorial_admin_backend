<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    //
    protected $table = 'authors';
    public $timestamps = false;

    protected $fillable = ['name', 'lastname'];

    public static function alreadyExists($name, $lastname)
    {
        return self::where('name', $name)
            ->where('lastname', $lastname)
            ->exists();
    }

    public function publishings()
    {
        return $this->belongsToMany('App\Publishing', 'authors_publishings', 'author_id', 'publishing_id');
    }
}
