<?php

namespace Modules\NsGastro\Providers;

use App\Classes\Hook;
use App\Crud\ProductCrud;
use App\Events\OrderAfterCheckPerformedEvent;
use App\Events\OrderAfterCreatedEvent;
use App\Events\OrderAfterPaymentStatusChangedEvent;
use App\Events\OrderAfterProductStockCheckedEvent;
use App\Events\OrderAfterUpdatedEvent;
use App\Events\OrderProductAfterComputedEvent;
use App\Events\OrderProductAfterSavedEvent;
use App\Events\OrderProductBeforeSavedEvent;
use App\Services\ModulesService;
use App\Services\OrdersService;
use App\Services\ProductCategoryService;
use App\Services\ProductService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\NsGastro\Events\GastroNewProductAddedToOrderEvent;
use Modules\NsGastro\Events\KitchenAfterUpdatedOrderEvent;
use Modules\NsGastro\Events\NsGastroEvent;
use Modules\NsGastro\Services\GastroOrderService;
use Modules\NsGastro\Services\KitchenService;
use Modules\NsGastro\Services\TableService;
use Modules\NsGastro\Settings\GastroSettings;
use Modules\NsMultiStore\Events\MultiStoreApiRoutesLoadedEvent;
use Modules\NsMultiStore\Events\MultiStoreWebRoutesLoadedEvent;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        Hook::addFilter('ns-dashboard-menus', function ($menus) {
            if (isset($menus['inventory'])) {
                $menus = array_insert_after($menus, 'inventory', [
                    'ns-gastro'         =>      [
                        'label'         =>  __m('Restaurant', 'NsGastro'),
                        'icon'          =>  'la-utensils',
                        'permissions'   =>  [
                            'gastro.create.table',
                            'gastro.read.table',
                            'gastro.update.table',
                            'gastro.delete.table',
                            'gastro.create.kitchens',
                            'gastro.read.kitchens',
                            'gastro.update.kitchens',
                            'gastro.delete.kitchens',
                            'gastro.use.kitchens',
                            'gastro.cancel.meals',
                            'gastro.serve.meals',
                            'gastro.update.meals-note',
                        ],
                        'childrens'     =>  [
                            'kitchen-screen'    =>  [
                                'label'     =>  __m('Kitchen Screen', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-kitchen-screen'),
                            ],
                            'kitchen-list'          =>  [
                                'label'     =>  __m('Kitchens', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-kitchen-list'),
                            ],
                            'tables' =>  [
                                'label'     =>  __m('Tables', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-tables'),
                            ],
                            'create-tables' =>  [
                                'label'     =>  __m('Create Table', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-tables-create'),
                            ],
                            'areas' =>  [
                                'label'     =>  __m('Table Areas', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-areas'),
                            ],
                            'create-areas' =>  [
                                'label'     =>  __m('Create Area', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-areas-create'),
                            ],
                            'modifiers-groups' =>  [
                                'label'     =>  __m('Modifiers Groups', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-modifiers-group'),
                            ],
                            'create-modifiers-groups' =>  [
                                'label'     =>  __m('Create Modifier Group', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-modifiers-group-create'),
                            ],
                            'canceled-meals' =>  [
                                'label'     =>  __m('Canceled Meals', 'NsGastro'),
                                'href'      =>  ns()->route('ns-gastro-canceled-meals'),
                            ],
                        ],
                    ],
                ]);
            }

            if (isset($menus['settings'])) {
                $menus['settings']['childrens'] = array_insert_after($menus['settings']['childrens'], 'pos', [
                    'gastro-settings'   =>  [
                        'label' =>  __m('Gastro', 'NsGastro'),
                        'href'  =>  ns()->route('ns-gastro-settings'),
                    ],
                ]);
            }

            return $menus;
        }, 30);

        Hook::addFilter('ns-orders-template-mapping', [NsGastroEvent::class, 'orderTemplateMapping'], 10, 2);
        Hook::addFilter('ns-orders-types', [NsGastroEvent::class, 'orderTypes']);
        Hook::addFilter('ns-receipt-products', [NsGastroEvent::class, 'filterCanceledProducts']);
        Hook::addFilter('ns-crud-resource', [NsGastroEvent::class, 'registerCrud']);
        Hook::addFilter('ns-products-crud-form', [NsGastroEvent::class, 'updateProductForm'], 10, 2);
        Hook::addAction('ns-load-order', [NsGastroEvent::class, 'loadProductsModifiers']);

        /**
         * this register custom action listener
         * for either when multistore is disabled
         * and when the multistore is enabled.
         */
        Hook::addAction('ns-dashboard-pos-footer', [NsGastroEvent::class, 'registerFooterScript']);
        Hook::addAction('ns-multistore--ns-dashboard-pos-footer', [NsGastroEvent::class, 'registerFooterScript']);

        Hook::addFilter('ns-wipeable-tables', [NsGastroEvent::class, 'registerWipeableTables']);
        Hook::addFilter('ns-pa-receipt-after-product', [NsGastroEvent::class, 'injectModifiers'], 10, 2);
        Hook::addFilter('ns-create-products-inputs', [NsGastroEvent::class, 'filterProductPostInput'], 10, 2);
        Hook::addFilter('ns-update-products-inputs', [NsGastroEvent::class, 'filterProductPutInputs'], 10, 3);
        Hook::addFilter('ns-order-types', [NsGastroEvent::class, 'setOrderType']);
        Hook::addFilter(ProductCrud::method('getColumns'), [NsGastroEvent::class, 'addNewColumn']);
        Hook::addFilter(ProductCrud::method('setActions'), [NsGastroEvent::class, 'setProductActions']);
        Hook::addFilter('ns-handle-custom-reset', [NsGastroEvent::class, 'handleResetActon'], 10, 2);
        Hook::addFilter('ns.settings', function ($class, $identifier) {
            switch ($identifier) {
                case 'ns-gastro-settings': return new GastroSettings();
                    break;
                default: return $class;
            }
        }, 10, 2);

        Hook::addFilter('ns-reset-options', function ($options) {
            $options['gastro_demo'] = __m('Restaurant Demo', 'NsGastro');

            return $options;
        });

        Hook::addFilter('ns-receipts-settings-tags', [NsGastroEvent::class, 'addSupportedTags']);

        /**
         * Will register localization to load.
         */
        Hook::addFilter('ns.langFiles', function ($langFiles) {
            $langFiles['NsGastro'] = asset('/modules-lang/nsgastro/'.app()->getLocale().'.json');

            return $langFiles;
        });

        Event::listen(MultiStoreApiRoutesLoadedEvent::class, fn () => ModulesService::loadModuleFile('NsGastro', 'Routes/api'));
        Event::listen(MultiStoreWebRoutesLoadedEvent::class, fn () => ModulesService::loadModuleFile('NsGastro', 'Routes/multistore'));

        Event::listen(OrderAfterCreatedEvent::class, [NsGastroEvent::class, 'saveTable']);
        Event::listen(KitchenAfterUpdatedOrderEvent::class, [NsGastroEvent::class, 'checkOrderCookingStatus']);
        Event::listen(KitchenAfterUpdatedOrderEvent::class, [NsGastroEvent::class, 'countReadyMeals']);
        Event::listen(KitchenAfterUpdatedOrderEvent::class, [NsGastroEvent::class, 'computeOrder']);
        Event::listen(OrderAfterProductStockCheckedEvent::class, [NsGastroEvent::class, 'checkStockAvailability']);
        Event::listen(OrderAfterCheckPerformedEvent::class, [NsGastroEvent::class, 'checkOrderDetails']);
        Event::listen(OrderProductBeforeSavedEvent::class, [NsGastroEvent::class, 'populateOrderDetails']);
        Event::listen(OrderProductAfterSavedEvent::class, [NsGastroEvent::class, 'storeModifiers']);
        Event::listen(OrderProductAfterComputedEvent::class, [NsGastroEvent::class, 'computeOrderProduct']);
        Event::listen(OrderAfterUpdatedEvent::class, [NsGastroEvent::class, 'updateOrderStatus']);
        Event::listen(OrderAfterPaymentStatusChangedEvent::class, [NsGastroEvent::class, 'freeTableIfNecessary']);
        Event::listen(GastroNewProductAddedToOrderEvent::class, [NsGastroEvent::class, 'setOrderAsPending']);

        $this->app->singleton(KitchenService::class, function () {
            return new KitchenService(
                app()->make(OrdersService::class),
                app()->make(ProductService::class),
            );
        });

        $this->app->singleton(TableService::class, function () {
            return new TableService();
        });

        $this->app->singleton(GastroOrderService::class, function () {
            return new GastroOrderService(
                app()->make(OrdersService::class),
                app()->make(ProductCategoryService::class),
            );
        });
    }
}
