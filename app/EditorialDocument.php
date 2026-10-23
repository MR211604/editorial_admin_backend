<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EditorialDocument extends Model
{
    protected $table = 'editorial_documents';
    protected $primaryKey = 'idDocument';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'state',
        'sort_order',
    ];
}
