<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Vendor extends Model
{
    use Notifiable;

    protected $table = 'vendors';

    protected $fillable = [
        'latitude', 'longitude', 'name', 'mobile', 'password', 'address', 'email', 'logo', 'category_id', 'active', 'created_at', 'updated_at'
    ];

    protected $hidden = ['category_id', 'password'];


    public function scopeActive($query)
    {

        return $query->where('active', 1);
    }

    ################## to save image ###in data base(/images/vendors/xxxxxxxx.xxx)################
    ### in show in blade ##  http://e-commerce.net/assets/images/vendors/xxxxxxxx.xxx  ####
    public function getLogoAttribute($val)
    {
        return ($val !== null) ? asset('assets/' . $val) : "";

    }


    public function scopeSelection($query)
    {
        return $query->select('id', 'category_id','latitude','longitude', 'active', 'name', 'address', 'email', 'logo', 'mobile');
    }

// ONE vendor has one main_category
    public function category()
    {

        return $this->belongsTo('App\Models\MainCategory', 'category_id', 'id');
    }

    public function getActive()
    {
        return $this->active == 1 ? 'مفعل' : 'غير مفعل';

    }

    ############## this is mutators to be password hash in insert in dataBase  ###############
    public function setPasswordAttribute($password)
    {
        if (!empty($password)) {
            $this->attributes['password'] = bcrypt($password);
        }
    }


}
