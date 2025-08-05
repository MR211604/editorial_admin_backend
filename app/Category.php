<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = "categories";
    public $timestamps = false;

    protected $fillable = ['title', 'description', 'main_category', 'state', 'is_featured', 'language_id', 'category_type'];

    public function detail()
    {
        return $this->hasOne('App\CategoryDetail', 'category_id');
    }

    public function publishings()
    {
        return $this->hasMany('App\Publishing', 'category_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'main_category');
    }

    public function parent()
    {
        return $this->hasMany(self::class, 'id', 'main_category');
    }
    
    public function childrenRecursive() {
        return $this->children()->with('childrenRecursive');
    }

    public static function alreadyExists($title)
    {
        return self::where('title', $title)->exists();
    }

}
