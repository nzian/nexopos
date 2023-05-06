<?php

namespace Modules\SmpVendor;

use Illuminate\Support\Facades\Event;
use App\Services\Module;

class SmpVendorModule extends Module
{
    public function __construct()
    {
        parent::__construct(__FILE__);

        Hook::addFilter('ns-dashboard-menus', function ($menus) {
            $menus    =   array_insert_after($menus, 'dashboard', [
                'foobar'    =>    [
                    'label'   =>    __('Foobar'),
                    'href'    =>    url('/url/to/ui')
                ]
            ]);

            return $menus; // <= do not forget
        });
    }

}
