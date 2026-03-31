<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $primaryKey = "idCategory";
    protected $table = "categories";

    public $timestamps = false;

    protected $fillable = ['idCategory', 'title', 'description', 'main_category', 'state', 'is_featured', 'language_id', 'category_type', 'URL'];

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
        return $this->hasMany(self::class, 'idCategory', 'main_category');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

}
