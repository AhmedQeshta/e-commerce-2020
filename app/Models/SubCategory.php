<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MainCategory;

class SubCategory extends Model
{
    protected $table = 'sub_categories';

    protected $fillable = [
        'translation_lang','category_id','parent_id','translation_of', 'name', 'slug', 'photo', 'active', 'created_at', 'updated_at'
    ];

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
    public function scopeDefaultlang($query)
    {
        return $query->where('translation_of', 0);
    }


    public function scopeSelection($query)
    {

        return $query->select('id','parent_id','category_id','translation_lang', 'name', 'slug', 'photo', 'active', 'translation_of');
    }

    public function getPhotoAttribute($val)
    {
        return ($val !== null) ? asset('assets/' . $val) : "";

    }

    public function getActive()
    {
        return $this->active == 1 ? 'مفعل' : 'غير مفعل';

    }
    public function scopeDefaultCategory($query){
        return  $query -> where('translation_of',0);
    }


    //get main category of subcategory
    public  function mainCategory(){
        return $this -> belongsTo(MainCategory::class,'category_id','id');
    }

    // get all translation categories
    public function langCategories(){
        return $this->hasMany(self::class, 'translation_of');
    }


}
