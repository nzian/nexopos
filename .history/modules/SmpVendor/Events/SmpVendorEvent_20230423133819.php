<?php

namespace Modules\SmpVendor\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\SmpVendor\Crud\VendorCrud;
use App\Services\Helper;
use Modules\SmpVendor\Models\Vendor;

/**
 * Register Event
**/
class SmpVendorEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct()
    {
        // ...
    }

    public static function registerCrud($identifier)
    {
        switch ($identifier) {
            case 'smp.vendors':             return VendorCrud::class;
            default: return $identifier;
        }
    }

      /**
     * Will register a new form input
     *
     * @param  array  $form
     * @return array
     */
    public static function updateProductForm($form, $data)
    {
        extract($data);
        /**
         * @param  Product  $model
         */
        $form['variations']['tabs']['identification']['fields'][] = [
            'type'  =>  'select',
            'label' =>  __m('Product Vendor Id', 'SmpVendor'),
            'name' => 'nexopos_products.vendor_id',
            'description' => __m('Product has a vendor', 'SmpVendor'),
            'value' => $model->vendor_id ?? '',
            'options' => Helper::toJsOptions(Vendor::all(), [ 'id', 'vendor_id' ])
        ];

        return $form;
    }

    public static function getColumns()
    {

    }
}