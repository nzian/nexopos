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

        Hook::addFilter('ns-dashboard-menus', function ($menus) {
            $menus[ 'reports' ]  =   [
                'label'   =>  __('Foobar'),
                'href'    =>  '#',
                'childrens' =>  [
                    [
                        'label' =>  __('Sub Menu'),
                        'href'  =>  url('/url/to/ui')
                    ]
                ]
            ];

            return $menus;
        });
    }

}
