<?php

namespace Modules\NsGastro\Events;

use App\Classes\Hook;
use App\Classes\Output;
use App\Events\OrderAfterCheckPerformedEvent;
use App\Events\OrderAfterCreatedEvent;
use App\Events\OrderAfterPaymentStatusChangedEvent;
use App\Events\OrderAfterProductStockCheckedEvent;
use App\Events\OrderAfterUpdatedEvent;
use App\Events\OrderProductAfterComputedEvent;
use App\Events\OrderProductAfterSavedEvent;
use App\Events\OrderProductBeforeSavedEvent;
use App\Exceptions\NotAllowedException;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductUnitQuantity;
use App\Models\Unit;
use App\Services\Helper;
use App\Services\ModulesService;
use App\Services\OrdersService;
use App\Services\ResetService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Modules\NsGastro\Crud\AreaCrud;
use Modules\NsGastro\Crud\CanceledProductCrud;
use Modules\NsGastro\Crud\KitchensCrud;
use Modules\NsGastro\Crud\ModifierGroupCrud;
use Modules\NsGastro\Crud\TableCrud;
use Modules\NsGastro\Models\Area;
use Modules\NsGastro\Models\ModifierGroup;
use Modules\NsGastro\Models\Order as GastroOrder;
use Modules\NsGastro\Models\OrderProduct;
use Modules\NsGastro\Models\OrderProduct as GastroOrderProduct;
use Modules\NsGastro\Models\OrderProductModifier;
use Modules\NsGastro\Models\OrderProductModifierGroup;
use Modules\NsGastro\Models\Table;
use Modules\NsGastro\Models\TableSession;
use Modules\NsGastro\Services\KitchenService;
use Modules\NsGastro\Services\RestaurantDemoService;
use Modules\NsGastro\Services\TableService;
use Modules\NsMultiStore\Events\MultiStoreWebRoutesLoadedEvent;
use Modules\NsMultiStore\Models\Store;

/**
 * Register Events
 **/
class NsGastroEvent
{
    protected $kitchenService;

    protected $orderService;

    public function __construct(
        KitchenService $kitchenService,
        OrdersService $orderService
    ) {
        $this->kitchenService = $kitchenService;
        $this->orderService = $orderService;
    }

    public static function loadWebRoutes(MultiStoreWebRoutesLoadedEvent $event)
    {
        ModulesService::loadModuleFile('NsGastro', 'Routes/web');
    }

    /**
     * registers a custom order
     * type to NexoPOS.
     *
     * @param  array  $types
     * @return array $types
     */
    public static function orderTypes($types)
    {
        $types['dine-in'] = [
            'identifier'    =>  'dine-in',
            'label'         =>  'Dine In',
            'icon'          =>  asset('modules/nsgastro/images/chair.png'),
            'selected'      =>  false,
        ];

        return $types;
    }

    public static function registerWipeableTables($tables)
    {
        return array_merge($tables, [
            'nexopos_gastro_areas',
            'nexopos_gastro_modifiers_groups',
            'nexopos_orders_products_modifiers',
            'nexopos_orders_products_modifiers_groups',
            'nexopos_gastro_tables',
            'nexopos_gastro_tables_history',
            'nexopos_gastro_kitchens',
            'nexopos_gastro_kitchens_categories',
            'nexopos_gastro_tables_booking_history',
        ]);
    }

    public static function registerCrud($identifier)
    {
        switch ($identifier) {
            case 'ns.gastro-areas':             return AreaCrud::class;
            case 'ns.gastro-tables':            return TableCrud::class;
            case 'ns.gastro-kitchens':          return KitchensCrud::class;
            case 'ns.gastro-modifiers-groups':  return ModifierGroupCrud::class;
            case 'ns.gastro-canceled-products': return CanceledProductCrud::class;
            default: return $identifier;
        }
    }

    public static function registerFooterScript(Output $output)
    {
        $output->addView('NsGastro::pos.footer');
    }

    /**
     * Ensure modifiers groups and modifiers are
     * correctly retreived with the form.
     *
     * @param  array  $form
     * @param  Product  $entity
     * @return array
     */
    public static function updateProductForm($form, $entity)
    {
        $options = Helper::toJsOptions(ModifierGroup::get(), ['id', 'name']);
        $form['variations'][0]['tabs']['restaurant'] = [
            'label'     =>  __m('Gastro', 'NsGastro'),
            'fields'    =>  [
                [
                    'label'         =>  __m('Skip Cooking', 'NsGastro'),
                    'type'          =>  'switch',
                    'name'          =>  'skip_cooking',
                    'options'       =>  Helper::kvToJsOptions([__m('No', 'NsGastro'), __m('Yes', 'NsGastro')]),
                    'value'         =>  $entity->skip_cooking ?? false,
                    'description'   =>  __m('Products with skip cooking option won\'t land at the kitchen and will be market as ready.', 'NsGastro'),
                ], [
                    'label'         =>  __m('Modifiers Groups', 'NsGastro'),
                    'type'          =>  'multiselect',
                    'name'          =>  'modifiers_groups',
                    'options'       =>  Helper::toJsOptions(ModifierGroup::get(), ['id', 'name']),
                    'value'         =>  $entity ? (json_decode($entity->modifiers_groups) ?? '') : '',
                    'description'   =>  __m('If the modifiers groups is provided, the current product shouldn\'t be saved as a modifiers. This means the modifier group field shouldn\'t be selected.', 'NsGastro'),
                ], [
                    'label'         =>  __m('Attached To Group', 'NsGastro'),
                    'type'          =>  'select',
                    'name'          =>  'modifiers_group_id',
                    'options'       =>  array_merge(
                        [
                            ['id'  =>  '', 'label' => __m('Choose...', 'NsGastro')],
                        ], $options
                    ),
                    'value'         =>  $entity->modifiers_group_id ?? '',
                    'description'   =>  __m('By defining the modifier group, the current product will be saved as a modifier.', 'NsGastro'),
                ],
            ],
        ];

        return $form;
    }

    /**
     * Make sure products can't be modifier
     * and product at the same time.
     *
     * @param  array  $input
     * @param  array  $source
     * @param  Product  $product
     * @return array $input
     */
    public static function filterProductPostInput($input, $source)
    {
        if (! empty($source['restaurant']['modifiers_groups']) && ! empty($source['restaurant']['modifiers_group_id'])) {
            throw new NotAllowedException(__m('A product cannot be a modifier and a product that has modifiers at the same time.', 'NsGastro'));
        }

        $input['skip_cooking'] = isset($source['restaurant']['skip_cooking']) ? (bool) $source['restaurant']['skip_cooking'] : false;
        $input['modifiers_groups'] = isset($source['restaurant']['modifiers_groups']) ? json_encode($source['restaurant']['modifiers_groups']) : '';
        $input['modifiers_group_id'] = $source['restaurant']['modifiers_group_id'] ?? 0;

        if (empty($input['modifiers_group_id'])) {
            $input['gastro_item_type'] = 'product';
        } else {
            $input['gastro_item_type'] = 'modifier';
        }

        return $input;
    }

    /**
     * Make sure products can't be modifier
     * and product at the same time.
     *
     * @param  array  $input
     * @param  array  $source
     * @param  Product  $product
     * @return array $input
     */
    public static function filterProductPutInputs($input, $source, $product)
    {
        if (! empty($source['restaurant']['modifiers_groups']) && ! empty($source['restaurant']['modifiers_group_id'])) {
            throw new NotAllowedException(__m('A product cannot be a modifier and a product that has modifiers at the same time.', 'NsGastro'));
        }

        $input['skip_cooking'] = isset($source['restaurant']['skip_cooking']) ? (bool) $source['restaurant']['skip_cooking'] : false;
        $input['modifiers_groups'] = isset($source['restaurant']['modifiers_groups']) ? json_encode($source['restaurant']['modifiers_groups']) : '';
        $input['modifiers_group_id'] = $source['restaurant']['modifiers_group_id'] ?? 0;

        if (empty($input['modifiers_group_id'])) {
            $input['gastro_item_type'] = 'product';
        } else {
            $input['gastro_item_type'] = 'modifier';
        }

        return $input;
    }

    /**
     * add a new columns to the product crud
     *
     * @param  array  $columns
     * @return array $columns
     */
    public static function addNewColumn($columns)
    {
        return array_insert_after($columns, 'name', [
            'gastro_item_type'  =>  [
                'label'         =>  __m('Item Type', 'NsGastro'),
                '$direction'    =>  '',
                'width'         =>  '120px',
                '$sort'         =>  false,
            ],
        ]);
    }

    /**
     * Provide a label that clearly makes products
     * and modifiers to stand out on the product list.
     *
     * @param  Product  $entry
     * @return Product
     */
    public static function setProductActions($entry)
    {
        $entry->gastro_item_type = $entry->gastro_item_type === 'modifier' ?
        '<strong class="rounded-full px-3 py-1 bg-purple-400 text-white">'.__m('Modifier', 'NsGastro').'</strong>' :
        '<strong class="rounded-full px-3 py-1 bg-green-400 text-white">'.__m('Product', 'NsGastro').'</strong>';

        return $entry;
    }

    /**
     * Store modifiers when a product is being saved.
     *
     * @param  OrderProductAfterSavedEvent  $event
     * @return void
     */
    public static function storeModifiers(OrderProductAfterSavedEvent $event)
    {
        $storedGroups = [];
        $storedModifiers = [];

        if (isset($event->postData['modifiersGroups'])) {
            foreach ($event->postData['modifiersGroups'] as $rawGroup) {
                $group = OrderProductModifierGroup::find($rawGroup['id'] ?? null);

                if (! $group instanceof OrderProductModifierGroup) {
                    $group = new OrderProductModifierGroup;
                }

                $group->forced = $rawGroup['forced'];
                $group->multiselect = $rawGroup['multiselect'];
                $group->name = $rawGroup['name'];
                $group->order_product_id = $event->product->id;
                $group->modifier_group_id = $rawGroup['modifier_group_id'];
                $group->countable = $rawGroup['countable'];
                $group->save();

                foreach ($rawGroup['modifiers'] as $rawModifier) {
                    $modifier = OrderProductModifier::find($rawModifier['id'] ?? null);

                    if (! $modifier instanceof OrderProductModifier) {
                        $modifier = new OrderProductModifier;
                    }

                    $modifier->unit_price = $rawModifier['unit_price'];
                    $modifier->quantity = $rawModifier['quantity'];
                    $modifier->name = $rawModifier['name'];
                    $modifier->order_product_id = $event->product->id;
                    $modifier->unit_quantity_id = $rawModifier['unit_quantity_id'];
                    $modifier->unit_id = $rawModifier['unit_id'];
                    $modifier->modifier_id = $rawModifier['modifier_id'];
                    $modifier->product_modifier_group_id = $group->id;
                    $modifier->tax_value = 0;
                    $modifier->total_price = $rawModifier['total_price'];
                    $modifier->save();

                    $storedModifiers[] = $modifier->id;

                    // we should normaly deplete the material from here.
                }

                $storedGroups[] = $group->id;
            }
        }

        $event->product->cooking_status = $event->postData['cooking_status'] ?? 'pending';
        $event->product->cooking_note = $event->postData['cooking_note'] ?? '';
        $event->product->meal_placed_by = Auth::id();
        $event->product->meal_placed_by_name = Auth::user()->username;
        $event->product->save();

        /**
         * delete ressource that has been ignored
         * from the POS (in case the order is edited)
         */
        OrderProductModifier::whereNotIn('id', $storedModifiers)
            ->where('order_product_id', $event->product->id)
            ->delete();

        OrderProductModifierGroup::whereNotIn('id', $storedGroups)
            ->where('order_product_id', $event->product->id)
            ->delete();
    }

    /**
     * Make sure loaded order inlcude the attached modifiers
     * for each product included
     *
     * @param  Order  $order
     * @return void
     */
    public static function loadProductsModifiers(Order $order)
    {
        $order->table = $order->table_id !== null ? Table::find($order->table_id) : [];

        /**
         * if the table is an instanceof table
         * we can bind the selected seats.
         */
        if ($order->table instanceof Table) {
            $order->table->selectedSeats = $order->seats;
        }

        $order->products->each(function (&$product) {
            $product->modifiersGroups = OrderProductModifierGroup::where('order_product_id', $product->id)
                ->with('modifiers')
                ->get();
        });
    }

    /**
     * This will save the table reference attached
     * to the order including the seat and the area
     *
     * @param  OrderAfterCreatedEvent  $event
     * @return void
     */
    public static function saveTable(OrderAfterCreatedEvent $event)
    {
        if (isset($event->fields['table']) && isset($event->fields['table']['selected']) && $event->fields['table']['selected'] === true) {
            $table = $event->fields['table'];
            $area = Area::find($table['area_id']);
            $event->order->table_id = $table['id'];
            $event->order->table_name = $table['name'];
            $event->order->area_name = $area instanceof Area ? $area->name : null;
            $event->order->area_id = $table['area_id'];
            $event->order->seats = $table['seats'] ?? 0;
            $event->order->gastro_order_status = $event->fields['gastro_order_status'] ?? GastroOrder::COOKING_PENDING;

            /**
             * We might check if all products
             * that has been submitted are premarked
             * as ready
             */
            $event->order->products->each(function ($orderProduct) {
                if ($orderProduct->product instanceof Product && (bool) $orderProduct->product->skip_cooking) {
                    $orderProduct->cooking_status = GastroOrderProduct::COOKING_READY;
                    $orderProduct->save();
                }
            });

            KitchenAfterUpdatedOrderEvent::dispatch($event->order);

            self::initializeSessionIfNecessary($event->order, $event->fields);

            $event->order->save();

            if ((bool) ns()->option->get('ns_gastro_table_availability', false)) {
                $table = Table::find($event->order->table_id);

                if ($table instanceof Table) {
                    $table->busy = true;
                    $table->save();
                }
            }
        }

        /**
         * We'll freed the assigned table if
         * the order is assigned to a table.
         */
        self::proceedFreedingTable($event->order);
    }

    /**
     * Will initialize a table session if
     * it's necessary
     *
     * @param  Order  $order
     * @param  array  $fields
     * @return void
     */
    private static function initializeSessionIfNecessary($order, $fields)
    {
        /**
         * @var TableService $tableService
         */
        $tableService = app()->make(TableService::class);

        if ((bool) ns()->option->get('ns_gastro_enable_table_sessions', false)) {
            $table = Table::find($fields['table']['id']);

            if ($table instanceof Table) {
                $session = $tableService->startTableSession($table, true);

                /**
                 * the session id is stored
                 */
                $order->gastro_table_session_id = $session->id;
            }
        }
    }

    public function checkOrderCookingStatus(KitchenAfterUpdatedOrderEvent $event)
    {
        $totalProducts = $event->order->products()->count();
        $totalPending = $event->order->products()->where('cooking_status', OrderProduct::COOKING_PENDING)->count();
        $totalOngoing = $event->order->products()->where('cooking_status', OrderProduct::COOKING_ONGOING)->count();
        $totalReady = $event->order->products()->where('cooking_status', OrderProduct::COOKING_READY)->count();
        $totalRequested = $event->order->products()->where('cooking_status', OrderProduct::COOKING_REQUESTED)->count();
        $totalServed = $event->order->products()->where('cooking_status', OrderProduct::COOKING_SERVED)->count();
        $totalCanceled = $event->order->products()->where('cooking_status', OrderProduct::COOKING_CANCELED)->count();
        $totalProcessed = $event->order->products()->where('cooking_status', OrderProduct::COOKING_PROCESSED)->count();

        /**
         * When all the order should be marked as pending
         */
        if ($totalPending === $totalProducts - $totalCanceled) {
            $event->order->gastro_order_status = GastroOrder::COOKING_PENDING;
        }

        /**
         * When the order should be marked as ongoing
         */
        if ($totalOngoing > 0) {
            $event->order->gastro_order_status = GastroOrder::COOKING_ONGOING;
        }

        /**
         * When the order should be marked as ready.
         */
        if ($totalReady === ($totalProducts - $totalCanceled)) {
            $event->order->gastro_order_status = GastroOrder::COOKING_READY;
        }

        /**
         * When the order should be marked as served.
         */
        if ($totalRequested === ($totalProducts - $totalCanceled)) {
            $event->order->gastro_order_status = GastroOrder::COOKING_REQUESTED;
        }

        /**
         * When the order should be marked as served.
         */
        if ($totalServed === ($totalProducts - $totalCanceled)) {
            $event->order->gastro_order_status = GastroOrder::COOKING_SERVED;
        }

        /**
         * The verification is performed
         * let's update the order
         */
        $event->order->save();
    }

    public function countReadyMeals()
    {
        $nexopos_orders = Hook::filter('ns-table-name', 'nexopos_orders');
        $nexopos_orders_products = Hook::filter('ns-table-name', 'nexopos_orders_products');

        $readyMeals = OrderProduct::cookingStatus(OrderProduct::COOKING_READY)
            ->join($nexopos_orders, $nexopos_orders.'.id', '=', $nexopos_orders_products.'.order_id')
            ->whereIn($nexopos_orders.'.type', [GastroOrder::TYPE_DINEIN])
            ->orderBy('updated_at', 'desc')
            ->count();

        ns()->option->set('gastro_ready_meals', $readyMeals);
    }

    /**
     * Will compute the order. This is necessary
     * if a product has been canceled.
     *
     * @param  KitchenAfterUpdatedOrderEvent  $event
     * @return void
     */
    public function computeOrder(KitchenAfterUpdatedOrderEvent $event)
    {
        $this->orderService->refreshOrder($event->order);
    }

    /**
     * Check wether stock is available
     * before proceeding
     *
     * @param  OrderAfterProductStockCheckedEvent  $event
     * @return void
     */
    public function checkStockAvailability(OrderAfterProductStockCheckedEvent $event)
    {
        collect($event->items)->each(function ($orderProduct) use ($event) {
            if (isset($orderProduct['modifiersGroups'])) {
                foreach ($orderProduct['modifiersGroups'] as $group) {
                    foreach ($group['modifiers'] as $modifier) {
                        $product = (object) $modifier;
                        $product->id = $product->modifier_id;

                        /**
                         * the unit needs to be attached
                         * to the unit quantity
                         */
                        $unitQuantity = ProductUnitQuantity::find($modifier['unit_quantity_id']);
                        $unitQuantity->unit = Unit::findOrFail($unitQuantity->unit_id);

                        $modifier['unit_quantity_id'] = $unitQuantity->id;
                        $modifier['product_id'] = $modifier['modifier_id'];

                        try {
                            $originalProduct = Product::find($product->id);
                            $this->orderService->checkQuantityAvailability(
                                $originalProduct,
                                $unitQuantity, // the modifier always use the first unit quantity
                                $modifier,  // as an OrderProduct
                                $event->session_identifier
                            );
                        } catch (Exception $exception) {
                            throw new Exception(
                                sprintf(
                                    __m('An error has occured with a modifier for the product "%s". Error : "%s"', 'NsGastro'),
                                    $orderProduct['name'],
                                    $exception->getMessage()
                                )
                            );
                        }
                    }
                }
            }
        });
    }

    public static function populateOrderDetails(OrderProductBeforeSavedEvent $event)
    {
        $event->orderProduct->modifiers_gross_total = $event->data['modifiers_gross_total'] ?? 0;
        $event->orderProduct->modifiers_total = $event->data['modifiers_total'] ?? 0;
        $event->orderProduct->modifiers_net_total = $event->data['modifiers_net_total'] ?? 0;
    }

    public static function injectModifiers($view, $orderProduct)
    {
        $modifiers = OrderProductModifier::where('order_product_id', $orderProduct->id)->get();

        return $view.View::make('NsGastro::receipts.modifiers', compact('modifiers'));
    }

    public static function computeOrderProduct(OrderProductAfterComputedEvent $event)
    {
        /**
         * IF the discount defined is based on
         * a percentage, we need to compute the discount
         * for the modifier provided.
         */
        $modifiersDiscounts = 0;

        if ($event->orderProduct->discount_type === 'percentage') {
            /**
             * @var OrdersService
             */
            $ordersService = app()->make(OrdersService::class);
            $modifiersDiscounts = $ordersService->computeDiscountValues(
                $event->orderProduct->discount_percentage,
                $event->orderProduct->modifiers_total
            );
        }

        $event->orderProduct->total_price = ns()->currency
            ->fresh($event->orderProduct->unit_price)
            ->multiplyBy($event->orderProduct->quantity)
            ->additionateBy($event->orderProduct->modifiers_total)
            ->subtractBy($event->orderProduct->discount + $modifiersDiscounts)
            ->get();
    }

    public static function updateOrderStatus(OrderAfterUpdatedEvent $event)
    {
        $skipCooking = $event->order->products
            ->filter(fn ($orderProduct) => $orderProduct->product === null || (bool) $orderProduct->product->skip_cooking)
            ->count();

        $pending = $event->order->products
            ->filter(fn ($product) => $product->cooking_status === GastroOrderProduct::COOKING_PENDING)
            ->count();

        $ongoing = $event->order->products
            ->filter(fn ($product) => $product->cooking_status === GastroOrderProduct::COOKING_ONGOING)
            ->count();

        $ready = $event->order->products
            ->filter(fn ($product) => $product->cooking_status === GastroOrderProduct::COOKING_READY)
            ->count();

        $totalProducts = $event->order->products->count();

        if (! in_array($event->order->gastro_order_status, [GastroOrder::COOKING_REQUESTED, GastroOrder::COOKING_PROCESSED])) {
            if ($pending === $totalProducts || $pending > 0 && $ongoing === 0) {
                $event->order->gastro_order_status = GastroOrder::COOKING_PENDING;
            } elseif ($ongoing > 0) {
                $event->order->gastro_order_status = GastroOrder::COOKING_ONGOING;
            } elseif ($ready === $totalProducts) {
                $event->order->gastro_order_status = GastroOrder::COOKING_READY;
            }
        }

        $event->order->save();

        /**
         * We'll freed the assigned table if
         * the order is assigned to a table.
         */
        self::proceedFreedingTable($event->order);
    }

    /**
     * Will mark an order as freed
     *
     * @param  Order  $order
     * @return void
     */
    public static function proceedFreedingTable($order)
    {
        /**
         * if no table id is provided
         * that means the order is probably not a
         * dinein order. In that case, let's skip.
         */
        if (empty($order->table_id) || $order->payment_status !== Order::PAYMENT_PAID) {
            return;
        }

        /**
         * in case the table assigned to the order
         * should be freed, let's check if the order is completely paid.
         */
        if (
            (bool) ns()->option->get('ns_gastro_freed_table_with_payment', false) &&
            (bool) ns()->option->get('ns_gastro_table_availability', false)
        ) {
            $session = TableSession::find($order->gastro_table_session_id);

            /**
             * If no table session is defined
             * let's break here.
             */
            if (! $session instanceof TableSession) {
                return;
            }

            $totalPaid = $session->orders
                ->filter(fn ($order) => $order->payment_status === Order::PAYMENT_PAID)
                ->count();

            /**
             * If all order that are on the current session are paid.
             * We'll make the table as freed.
             */
            if ($totalPaid === $session->orders->count()) {
                $session->table->busy = false;
                $session->table->save();

                if ((bool) ns()->option->get('ns_gastro_enable_table_sessions', false)) {
                    /**
                     * This will also close the session
                     * and update the duration of the session
                     */
                    $session->active = false;
                    $session->session_ends = ns()->date->getNow()->toDateTimeString();
                    $session->session_minutes_duration = Carbon::parse($session->session_starts)
                        ->diffInMinutes($session->session_ends);
                    $session->save();
                }
            }
        }
    }

    public static function orderTemplateMapping($arguments, Order $order)
    {
        $table = Table::find($order->table_id);
        $arguments['table_name'] = $table instanceof Table ? $table->name : __('N/A');

        return $arguments;
    }

    /**
     * we'll filter canceled products
     *
     * @param  Collection<OrderProduct>  $products
     * @return Collection<OrderProduct>
     */
    public static function filterCanceledProducts($products)
    {
        return $products->filter(function ($product) {
            return $product->cooking_status !== 'canceled';
        });
    }

    /**
     * Will add supported tags for printing
     *
     * @param array
     * @return array
     */
    public static function addSupportedTags($tags)
    {
        $tags[] = __m('{table_name} : Displays the table name', 'NsGastro');

        return $tags;
    }

    public static function handleResetActon($response, $data)
    {
        /**
         * @var string $mode
         * @var bool $create_sales
         * @var bool $create_procurements
         */
        extract($data);

        if ($mode === 'gastro_demo') {
            /**
             * @var ResetService
             */
            $resetService = app()->make(ResetService::class);
            $resetService->softReset();

            /**
             * @var RestaurantDemoService
             */
            $demoService = app()->make(RestaurantDemoService::class);
            $demoService->run($data);

            return [
                'status'    =>  'success',
                'message'   =>  __m('The restaurant demo has been enabled.', 'NsGastro'),
            ];
        }

        return $response;
    }

    public function freeTableIfNecessary(OrderAfterPaymentStatusChangedEvent $event)
    {
        self::proceedFreedingTable($event->order);
    }

    /**
     * We'll define the order type
     *
     * @param  array  $types code
     * @return array $type label
     */
    public static function setOrderType($types)
    {
        $types[GastroOrder::TYPE_DINEIN] = __m('Dine In', 'NsGastro');

        return $types;
    }

    public function checkOrderDetails(OrderAfterCheckPerformedEvent $event)
    {
        /**
         * @var TableService
         */
        $tableService = app()->make(TableService::class);
        $fields = $event->fields;

        /**
         * If the table doesn't
         * allow multiple clients
         */
        if (
            isset($fields['table']) &&
            ! empty($fields['table']) &&
            ! (bool) $fields['table']['allow_multi_clients'] &&
            (bool) ns()->option->get('ns_gastro_enable_table_sessions', false)) {
            $table = Table::find($fields['table']['id']);
            $orders = $tableService->getTableOrders($table);

            if ($orders->isNotEmpty()) {
                $customers_id = $orders->map(fn ($order) => $order->customer_id)->toArray();

                if (isset($fields['customer']) && ! in_array($fields['customer']['id'], $customers_id)) {
                    throw new NotAllowedException(sprintf(
                        __('The table is already busy with a different customer "%s". Assigning a new customer is disallowed by the "%s" settings.'),
                        $orders->first()->customer->name,
                        $table->name
                    ));
                }
            }
        }
    }

    public function setOrderAsPending(GastroNewProductAddedToOrderEvent $event)
    {
        $event->order->gastro_order_status = GastroOrder::COOKING_ONGOING;
        $event->order->save();
    }
}
