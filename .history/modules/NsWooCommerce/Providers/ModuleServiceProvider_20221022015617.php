<?php

namespace Modules\NsWooCommerce\Providers;

use App\Classes\Hook;
use App\Classes\Output;
use App\Crud\CustomerCrud;
use App\Crud\OrderCrud;
use App\Crud\PaymentTypeCrud;
use App\Crud\ProductCategoryCrud;
use App\Crud\ProductCrud;
use App\Events\CustomerAfterCreatedEvent;
use App\Events\CustomerAfterUpdatedEvent;
use App\Events\CustomerBeforeDeletedEvent;
use App\Events\OrderAfterCreatedEvent;
use App\Events\OrderBeforeDeleteEvent;
use App\Events\ProductAfterCreatedEvent;
use App\Events\ProductAfterUpdatedEvent;
use App\Events\ProductBeforeDeleteEvent;
use App\Events\ProductCategoryAfterCreatedEvent;
use App\Events\ProductCategoryAfterUpdatedEvent;
use App\Events\ProductCategoryBeforeDeletedEvent;
use App\Providers\AppServiceProvider;
use App\Services\CustomerService;
use App\Services\OrdersService;
use App\Services\ProductCategoryService;
use App\Services\ProductService;
use Illuminate\Support\Facades\Event;
use Modules\NsWooCommerce\Events\NsWooCommerceEvent;
use Modules\NsWooCommerce\Services\NexoPOSService;
use Modules\NsWooCommerce\Services\WooCommerceService;

class ModuleServiceProvider extends AppServiceProvider
{
    public function register()
    {
        Hook::addFilter('ns-dashboard-menus', [NsWooCommerceEvent::class, 'menus']);
        Hook::addFilter('ns.settings', [NsWooCommerceEvent::class, 'registerSettings'], 10, 2);

        Event::listen(
            ProductCategoryAfterCreatedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::syncCategory($event);
                }
            }
        );

        Event::listen(
            ProductCategoryAfterUpdatedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::syncCategory($event);
                }
            }
        );

        Event::listen(
            ProductCategoryBeforeDeletedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::deleteCategory($event);
                }
            }
        );

        Event::listen(
            ProductAfterCreatedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::syncProduct($event);
                }
            }
        );

        Event::listen(
            ProductAfterUpdatedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::syncProduct($event);
                }
            }
        );

        Event::listen(
            ProductBeforeDeleteEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::deleteProduct($event);
                }
            }
        );

        Event::listen(
            CustomerAfterCreatedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::syncCustomer($event);
                }
            }
        );
        
        Event::listen(
            CustomerAfterUpdatedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::syncCustomer($event);
                }
            }
        );

        Event::listen(
            CustomerBeforeDeletedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::deleteCustomer($event);
                }
            }
        );

        Event::listen(
            OrderAfterCreatedEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::syncOrder($event);
                }
            }
        );

        Event::listen(
            OrderBeforeDeleteEvent::class,
            function ($event) {
                global $wcSync;
                if ($wcSync !== false) {
                    NsWooCommerceEvent::deleteOrder($event);
                }
            }
        );

        $this->app->singleton(WooCommerceService::class, function () {
            return new WooCommerceService(
                ns()->option->get('nsw_woocommerce_endpoint'),
                ns()->option->get('nsw_woocommerce_consummer_key'),
                ns()->option->get('nsw_woocommerce_consummer_secret')
            );
        });

        $this->app->singleton(NexoPOSService::class, function () {
            return new NexoPOSService(
                app()->make(ProductService::class),
                app()->make(OrdersService::class),
                app()->make(CustomerService::class),
                app()->make(WooCommerceService::class),
                app()->make(ProductCategoryService::class)
            );
        });

        Hook::addFilter(ProductCrud::method('setActions'), [NsWooCommerceEvent::class, 'setProductActions']);
        Hook::addFilter(ProductCrud::method('getBulkActions'), [NsWooCommerceEvent::class, 'addCustomBulkAction']);
        Hook::addFilter(ProductCrud::method('bulkAction'), [NsWooCommerceEvent::class, 'handleProductBulkAction'], 10, 2);
        Hook::addFilter(CustomerCrud::method('setActions'), [NsWooCommerceEvent::class, 'setCustomerActions']);
        Hook::addFilter(CustomerCrud::method('getBulkActions'), [NsWooCommerceEvent::class, 'addCustomBulkAction']);
        Hook::addFilter(CustomerCrud::method('bulkAction'), [NsWooCommerceEvent::class, 'handleCustomerBulkAction'], 10, 2);
        Hook::addFilter(PaymentTypeCrud::method('getForm'), [NsWooCommerceEvent::class, 'updateForm'], 10, 2);
        Hook::addFilter(OrderCrud::method('setActions'), [NsWooCommerceEvent::class, 'setOrdersActions'], 10, 2);
        Hook::addFilter(ProductCategoryCrud::method('setActions'), [NsWooCommerceEvent::class, 'setCategoryActions']);
        Hook::addFilter(ProductCategoryCrud::method('getBulkActions'), [NsWooCommerceEvent::class, 'addCustomBulkAction']);
        Hook::addFilter(ProductCategoryCrud::method('bulkAction'), [NsWooCommerceEvent::class, 'handleBulkAction'], 10, 2);
        Hook::addAction( 'ns-dashboard-footer', function( Output $output ) {
            $output->addView( 'NsWooCommerce::settings-footer' );
        });
    }
}
