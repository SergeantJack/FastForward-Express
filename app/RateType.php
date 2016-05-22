<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RateType extends Model {
    protected $table = 'rate_type';

    protected $fillable = [
        'name'

        //Missing:
        //Discount?
    ];

    public function getCompanies() {
        return $this->belongsToMany('Customer', 'rate_type_id');
    }
}
