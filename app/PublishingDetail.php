<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublishingDetail extends Model
{
    protected $fillable = [
        'summary',
        'URL',
        'repository_URL',
        'pages',
        'publishing_id',
    ];
    public $timestamps = false;

    protected $table = "publishing_details";
    
    public function publishing()
    {
        return $this->belongsTo('App\Publishing');
    }
}
