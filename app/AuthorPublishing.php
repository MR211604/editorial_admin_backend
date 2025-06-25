<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthorPublishing extends Model
{
    //
    private $table = "authors_publishings";

    protected $fillable = ['author_id', 'publishing_id'];

}
