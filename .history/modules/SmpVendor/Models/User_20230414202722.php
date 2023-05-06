<?php

namespace Modules\SmpVendor\Models;

use App\Models\User as ModelsUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class User extends ModelsUser
{
    protected $fillable = array_merge(parent::$fillable,['vendor_id']);
}