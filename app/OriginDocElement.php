<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UploadDocElement;

class OriginDocElement extends Model
{
    protected $fillable = ['value', 'font_size'];

    protected $visible = [
        'font_size',
        'origin_element_id',
        'origin_value',
        'now_value'
    ];


    protected $appends = [
        'origin_element_id',
        'origin_value',
        'now_value'
    ];

    
    public function uploadDocElements()
    {
        return $this->hasMany('App\UploadDocElement');
    }


    public function getOriginElementIdAttribute()
    {
        return $this->attributes['id'];
    }


    public function getOriginValueAttribute()
    {
        return $this->attributes['value'];
    }


    public function getNowValueAttribute()
    {
        $lastUploadDocElement = UploadDocElement::latest('id')->first();
        if ($lastUploadDocElement){
            return $lastUploadDocElement->value;
        }
        else {
            return null;
        }
    }

}
