<?php

namespace Modules\SmpVendor\Models;

use App\Models\User as ModelsUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class User extends ModelsUser
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    $this->fillable = array_marge($this->fillable, ['vendor_id']);

}