<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UploadDocElement extends Model
{
    protected $fillable = ['origin_element_id', 'value'];


    public function originDocElement()
    {
        return $this->belongsTo('App\OriginDocElement');
    }
}
