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
        $form['variations'][0]['tabs']['identification']['fields'][] = [
            'type'  =>  'select',
            'label' =>  __m('Product Vendor Id', 'SmpVendor'),
            'name' => 'nexopos_products.vendor_id',
            'description' => __m('Product has a vendor', 'SmpVendor'),
            'validation' => 'required',
            'value' => $model->vendor_id ?? '',
            'options' => Helper::toJsOptions(Vendor::get(), [ 'id', 'vendor_id' ])
        ];

        return $form;
    }

    public static function addColumns($columns)
    {
        return array_insert_after($columns, 'name', [
            'vendor_id'  =>  [
                'label'         =>  __m('Vendor Id', 'SmpVendor'),
                '$direction'    =>  '',
                'width'         =>  '120px',
                '$sort'         =>  false,
            ],
        ]);
    }

    /**
     * Will get the vendor name and return it into vendor id column
     *
     * @param  object  $entry
     * @return object $entry
     */
    public static function addSetActions()
    {
        $vendors = Vendor::get();
        $entry->vendor_id = $vendors->map(fn)
    }
}
