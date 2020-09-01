<?php

namespace App\Models;

use App\Observers\MainCategoryObserver;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubCategory;
class MainCategory extends Model
{
    protected $table = 'main_categories';

    protected $fillable = [
        'translation_lang', 'translation_of', 'name', 'slug', 'photo', 'active', 'created_at', 'updated_at'
    ];

    public $timestamps = true;

    ####################################### to use App\Observers\MainCategoryObserver.php #################################
    ########################### to change status for all any model in relation with main_category ###################
    protected static function boot()
    {
        parent::boot();
        MainCategory::observe(MainCategoryObserver::class);
    }

    ################## local scope to tell me if active or not ###############
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
    ################## End  local scope to tell me if active or not ###############

    public function scopeDefaultlang($query)
    {
        return $query->where('translation_of', 0);
    }

    public function scopeSelection($query)
    {

        return $query->select('id', 'translation_lang', 'name', 'slug', 'photo', 'active', 'translation_of');
    }

    ################## to save image ###in data base(/images/mainCategory/1598713617.jpg)################
    ### in show in blade ##  http://e-commerce.net/assets/images/mainCategory/1598713617.jpg  ####
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


      // get all translation categories
    public function categories(){
        return $this->hasMany(self::class, 'translation_of');
    }


    public  function subCategories(){
        return $this -> hasMany(SubCategory::class,'category_id','id');
    }



    public function vendors(){
        return $this -> hasMany('App\Models\Vendor','category_id','id');
    }

}
