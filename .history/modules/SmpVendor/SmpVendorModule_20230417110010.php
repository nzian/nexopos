<?php

namespace Modules\SmpVendor;

use Illuminate\Support\Facades\Event;
use App\Services\Module;
use App\Classes\Hook;

class SmpVendorModule extends Module
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

}
