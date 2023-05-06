<?php
/**
 * Service Provider
 * @package : SmpVendor
**/

namespace Modules\SmpVendor\Providers;

use Illuminate\Support\ServiceProvider as CoreServiceProvider;
use App\Classes\Hook;

class ModuleServiceProvider extends CoreServiceProvider
{
    /**
     * register method
     */
    public function register()
    {
        // register stuff here
        Hook::addFilter('ns-dashboard-menus', function ($menus) {
            if (isset($menus['reports'])) {
                $menus['reports']['childrens'] = array_insert_after($menus['reports']['childrens'], 'sales', [
                    'vendor-sales'   =>  [
                        'label' =>  __m('Vendor Monthly Sales', 'SmpVendor'),
                        'href'  =>  ns()->route('smp.reports.vendors-monthly-statement'),
                    ],
                ]);
            }

            if (isset($menus['users'])) {
                $menus['users']['childrens'] = array_insert_after($menus['users']['childrens'], 'profile', [
                    'vendors'   =>  [
                        'label' =>  __m('Vendor List', 'SmpVendor'),
                        'href'  =>  ns()->route('smp.vendors'),
                    ],
                    'vendors'   =>  [
                        'label' =>  __m('Create Vendor', 'SmpVendor'),
                        'href'  =>  ns()->route('smp.vendors-create'),
                    ],
                ]);
            }
            return $menus;
        }, 30);
    }

    /**
     * Boot method
     * @return void
    **/
    public function boot()
    {
        // boot stuff here
    }
}
