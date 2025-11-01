<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublishingDetail extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'URL',
        'repository_URL',
        'language_id',
        'publishing_id',
    ];
    public $timestamps = false;

    protected $table = "publishing_details";

    //Relacionando el language_id con la tabla Language
    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    public function publishing()
    {
        return $this->belongsTo('App\Publishing');
    }
}
