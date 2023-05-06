<?php

namespace Modules\SmpVendor\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\SmpVendor\Crud\VendorCrud;

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
        $form['tabs']['general']['fields'][] = [
            'type'  =>  'select',
            'label' =>  __m('WooCommerce Payment ID', 'NsWooCommerce'),
            'name'  =>  'wc_payment_id',
            'value'         =>  $model->wc_payment_id ?? '',
            'description'   =>  __m('Define to which payment ID this payment should be linked to.', 'NsWooCommerce'),
        ];

        return $form;
    }
}