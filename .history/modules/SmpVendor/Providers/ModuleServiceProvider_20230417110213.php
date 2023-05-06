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

        });
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
