<?php

namespace Modules\SmpVendor\Models;

use App\Models\User as ModelsUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class User extends ModelsUser
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        $this->fillable = array_merge($this->fillable, ['vendor_id']);
    }
}
