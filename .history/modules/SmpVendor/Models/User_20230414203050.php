<?php

namespace Modules\SmpVendor\Models;

use App\Models\User as ModelsUser;

class User extends ModelsUser
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array_merge(parent::$fillable, ['vendor_id']);
}
