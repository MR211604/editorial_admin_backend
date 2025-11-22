<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Publishing extends Model
{

    protected $fillable = [
        'title',
        'publish_date',
        'publish_code',
        'age',
        'pages',
        'age_number',
        'volume',
        'is_new_arrival',
        'category_id',
        'publishing_type',
        'resource_URL',
        'is_archived',
        'URL',
        'cover'
    ];

    public $timestamps = false;

    public function translations()
    {
        return $this->hasMany(PublishingTranslations::class);
    }

    public function translation($languageId = null)
    {
        if ($languageId) {
            $languageId = app()->getLocale() === 'es' ? 1 : 2;
        }
        return $this->hasOne(PublishingTranslations::class)->where('language_id', $languageId);
    }

    public function category()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function authors()
    {
        return $this->belongsToMany('App\Author', 'authors_publishings', 'publishing_id', 'author_id');
    }

    public function type()
    {
        return $this->belongsTo('App\PublishingType', 'publishing_type');
    }

    public function details()
    {
        return $this->hasMany('App\PublishingDetail', 'publishing_id');
    }

}
