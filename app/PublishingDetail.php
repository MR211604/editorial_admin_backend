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
        'publishing_id',
    ];
    public $timestamps = false;

    protected $table = "publishing_details";
    
    public function publishing()
    {
        return $this->belongsTo('App\Publishing');
    }
}
