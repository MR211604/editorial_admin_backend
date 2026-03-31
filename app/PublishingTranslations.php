<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublishingTranslations extends Model
{
  protected $primaryKey = 'idPublishingTranslation';

  protected $fillable = [
    'publishing_id',
    'language_id',
    'title',
    'URL',
    'summary'
  ];

  public $timestamps = false;

  public function publishing()
  {
    return $this->belongsTo(Publishing::class, 'publishing_id', 'idPublishing');
  }

  public function language()
  {
    return $this->belongsTo(Language::class, 'language_id', 'idLanguage');
  }

}