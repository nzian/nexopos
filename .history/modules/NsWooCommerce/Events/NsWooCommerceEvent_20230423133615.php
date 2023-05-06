<?php

namespace Modules\NsWooCommerce\Events;

use App\Events\ProductAfterCreatedEvent;
use App\Events\ProductAfterUpdatedEvent;
use App\Events\ProductBeforeDeleteEvent;
use App\Events\ProductCategoryAfterCreatedEvent;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Users;
use Illuminate\Http\Request;
use Modules\NsWooCommerce\Jobs\SyncCategoryToWooCommerceJob;
use Modules\NsWooCommerce\Jobs\SyncCustomerWooCommerceJob;
use Modules\NsWooCommerce\Jobs\SyncOrderToWooCommerceJob;
use Modules\NsWooCommerce\Jobs\SyncProductWooCommerceJob;
use Modules\NsWooCommerce\Services\WooCommerceService;
use Modules\NsWooCommerce\Settings\NsWooCommerceSettings;

/**
 * Register Events
 **/
class NsWooCommerceEvent
{
    public function __construct()
    {
        //
    }

    public static function registerSettings($settingsPage, $identifier)
    {
        switch ($identifier) {
            case 'nsw.settings-page' : return new NsWooCommerceSettings();
            default: return $settingsPage;
        }
    }

    /**
     * Sync the category to WooCommerce
     *
     * @param  ProductCategoryAfterCreatedEvent|ProductCategoryBeforeCreatedEvent  $event
     * @return void
     */
    public static function syncCategory($event)
    {
        SyncCategoryToWooCommerceJob::dispatch($event->category);
    }

    /**
     * Will delete the category on WooCommerce
     *
     * @param  ProductCategoryBeforeDeletedEvent  $event
     * @return void
     */
    public static function deleteCategory($event)
    {
        $service = app()->make(WooCommerceService::class);
        $service->deleteCategory($event->category);
    }

    public static function deleteProduct(ProductBeforeDeleteEvent $event)
    {
        /**
         * @var WooCommerceService
         */
        $service = app()->make(WooCommerceService::class);
        $service->deleteProduct($event->product);
    }

    /**
     * Sync the product to WooCommerce
     *
     * @param  ProductAfterCreatedEvent|ProductAfterUpdatedEvent  $event
     * @return void
     */
    public static function syncProduct($event)
    {
        SyncProductWooCommerceJob::dispatch($event->product);
    }

    /**
     * Will register a new form input
     *
     * @param  array  $form
     * @return array
     */
    public static function updateForm($form, $data)
    {
        extract($data);
        /**
         * @param  PaymentType  $model
         */
        $form['tabs']['general']['fields'][] = [
            'type'  =>  'text',
            'label' =>  __m('WooCommerce Payment ID', 'NsWooCommerce'),
            'name'  =>  'wc_payment_id',
            'value'         =>  $model->wc_payment_id ?? '',
            'description'   =>  __m('Define to which payment ID this payment should be linked to.', 'NsWooCommerce'),
        ];

        return $form;
    }

    public static function getColumns()
    {

    }

    /**
     * Will register a new menu for the settings
     *
     * @param  array  $menus
     * @return array $menus
     */
    public static function menus($menus)
    {
        if (isset($menus['settings'])) {
            $menus['settings']['childrens']['woocomerce-settings'] = [
                'label'     =>  __('WooCommerce Settings'),
                'href'      =>  route(ns()->routeName('nsw.settings-page')),
            ];
        }

        return $menus;
    }

    /**
     * Will append a label for the synchronization status for the categories
     *
     * @param  object  $entry
     * @return object $entry
     */
    public static function setCategoryActions($entry)
    {
        $notSynced = '<span class="ml-2 rounded-full px-2 text-white bg-gray-500" alt="'.__m('Not Synced', 'NsWooCommerce').'"><i class="las la-unlink"></i></span>';
        $synced = '<span class="ml-2 rounded-full px-2 bg-green-400 text-white" alt="'.__m('Synced', 'NsWooCommerce').'"><i class="las la-link"></i></span>';
        $entry->name = '<p>'.$entry->name.(empty($entry->wc_category_id) ? $notSynced : $synced).'</p>';

        return $entry;
    }

    public static function setCustomerActions($entry)
    {
        $notSynced = '<span class="ml-2 rounded-full px-2 text-white bg-gray-500" alt="'.__m('Not Synced', 'NsWooCommerce').'"><i class="las la-unlink"></i></span>';
        $synced = '<span class="ml-2 rounded-full px-2 bg-green-400 text-white" alt="'.__m('Synced', 'NsWooCommerce').'"><i class="las la-link"></i></span>';
        $entry->name = '<p>'.$entry->name.(empty($entry->wc_customer_id) ? $notSynced : $synced).'</p>';

        return $entry;
    }

    /**
     * Will append a label for the synchronization status for the products
     *
     * @param  object  $entry
     * @return object $entry
     */
    public static function setProductActions($entry)
    {
        $notSynced = '<span class="ml-2 rounded-full px-2 text-white bg-gray-500" alt="'.__m('Not Synced', 'NsWooCommerce').'"><i class="las la-unlink"></i></span>';
        $synced = '<span class="ml-2 rounded-full px-2 bg-green-400 text-white" alt="'.__m('Synced', 'NsWooCommerce').'"><i class="las la-link"></i></span>';
        $entry->name = '<p>'.$entry->name.(empty($entry->wc_product_id) ? $notSynced : $synced).'</p>';

        return $entry;
    }

    /**
     * Will append a label for the synchronization status for the orders
     *
     * @param  object  $entry
     * @return object $entry
     */
    public static function setOrdersActions($entry)
    {
        $notSynced = '<span class="ml-2 rounded-full px-2 text-white bg-gray-500" alt="'.__m('Not Synced', 'NsWooCommerce').'"><i class="las la-unlink"></i></span>';
        $synced = '<span class="ml-2 rounded-full px-2 bg-green-400 text-white" alt="'.__m('Synced', 'NsWooCommerce').'"><i class="las la-link"></i></span>';
        $entry->code = '<p>'.$entry->code.(empty($entry->wc_order_id) ? $notSynced : $synced).'</p>';

        $entry->addAction('sync.order', [
            'type'  =>  'GET',
            'label'     =>  '<i class="mr-2 las la-sync"></i>  ' . __m('Sync Order', 'NsWooCommerce'),
            'confirm'   =>  [
                'message'   =>  __m('Would you like to sync this order?', 'NsWooComerce'),
            ],
            'url'   =>  ns()->url('/api/nexopos/v4/nsw/sync-order/' . $entry->id)
        ]);

        $domain     =   parse_url(ns()->option->get('nsw_woocommerce_endpoint'));

        /**
         * Only if the hosts exists
         * we'll add a shortcut to see the order receipt
         */
        if (! empty($domain[ 'host' ]) && ! empty($entry->wc_order_id)) {
            $entry->addAction('woocommerce.order', [
                'type'  =>  'GOTO',
                'target'    =>  '_blank',
                'label'     =>  '<i class="mr-2 lab la-wordpress"></i>  ' . __m('Store Order', 'NsWooCommerce'),
                'url'   =>  $domain[ 'scheme' ] . '://' . $domain[ 'host' ] . '/wp-admin/post.php?post=' . $entry->wc_order_id . '&action=edit'
            ]);
        }

        return $entry;
    }

    public static function syncCustomer($event)
    {
        SyncCustomerWooCommerceJob::dispatch($event->customer);
    }

    public static function deleteCustomer($event)
    {
        /**
         * @var WooCommerceService
         */
        $service = app()->make(WooCommerceService::class);
        $service->deleteCustomer($event->customer);
    }

    public static function syncOrder($event)
    {
        SyncOrderToWooCommerceJob::dispatch($event->order);
    }

    public static function deleteOrder($event)
    {
        /**
         * @var WooCommerceService
         */
        $service = app()->make(WooCommerceService::class);
        $service->deleteOrder($event->order);
    }

    public static function addCustomSyncActionOnOrders($actions)
    {
        $actions[] = [
            'label'         =>  __('Sync Selected Orders'),
            'identifier'    =>  'sync_selected',
            'url'           =>  ns()->route('nsw.orders-sync-selected'),
        ];

        return $actions;
    }

    public static function addCustomBulkAction($actions)
    {
        $actions[] = [
            'label'         =>  __('Sync Selected'),
            'identifier'    =>  'sync_selected',
            'url'           =>  ns()->route('nsw.categories-sync-selected'),
        ];

        return $actions;
    }

    /**
     * Will launch synchronization for the selected product
     *
     * @param  array  $response
     * @param  Request  $request
     * @return array
     */
    public static function handleProductBulkAction($response, Request $request)
    {
        /**
         * Deleting licence is only allowed for admin
         * and supervisor.
         */
        $user = app()->make(Users::class);

        if (! $user->is(['admin', 'supervisor'])) {
            return response()->json([
                'status'    =>  'failed',
                'message'   =>  __('You\'re not allowed to do this operation.'),
            ], 403);
        }

        if ($request->input('action') === 'sync_selected') {
            $status = [
                'success'   =>  0,
                'failed'    =>  0,
            ];

            foreach ($request->input('entries') as $id) {
                $product = Product::find($id);
                if ($product instanceof Product) {
                    SyncProductWooCommerceJob::dispatch($product);
                    $status['success']++;
                } else {
                    $status['failed']++;
                }
            }

            return $status;
        }

        return $response;
    }

    public static function handleBulkAction($response, Request $request)
    {
        /**
         * Deleting licence is only allowed for admin
         * and supervisor.
         */
        $user = app()->make(Users::class);

        if (! $user->is(['admin', 'supervisor'])) {
            return response()->json([
                'status'    =>  'failed',
                'message'   =>  __('You\'re not allowed to do this operation.'),
            ], 403);
        }

        if ($request->input('action') === 'sync_selected') {
            $status = [
                'success'   =>  0,
                'failed'    =>  0,
            ];

            foreach ($request->input('entries') as $id) {
                $category = ProductCategory::find($id);
                if ($category instanceof ProductCategory) {
                    SyncCategoryToWooCommerceJob::dispatch($category);
                    $status['success']++;
                } else {
                    $status['failed']++;
                }
            }

            return $status;
        }

        return $response;
    }

    public static function handleCustomerBulkAction($response, Request $request)
    {
        /**
         * Deleting licence is only allowed for admin
         * and supervisor.
         */
        $user = app()->make(Users::class);

        if (! $user->is(['admin', 'supervisor'])) {
            return response()->json([
                'status'    =>  'failed',
                'message'   =>  __('You\'re not allowed to do this operation.'),
            ], 403);
        }

        if ($request->input('action') === 'sync_selected') {
            $status = [
                'success'   =>  0,
                'failed'    =>  0,
            ];

            foreach ($request->input('entries') as $id) {
                $customer = Customer::find($id);
                if ($customer instanceof Customer) {
                    SyncCustomerWooCommerceJob::dispatch($customer);
                    $status['success']++;
                } else {
                    $status['failed']++;
                }
            }

            return $status;
        }

        return $response;
    }
}
