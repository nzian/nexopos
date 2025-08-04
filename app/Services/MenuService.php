<?php

namespace App\Services;

use Illuminate\Support\Facades\Gate;
use TorMorten\Eventy\Facades\Eventy as Hook;

class MenuService
{
    protected $menus;

    public function buildMenus()
    {
        $this->menus = [
            'dashboard' => [
                'label' => __( 'Dashboard' ),
                'permissions' => [ 'read.dashboard' ],
                'icon' => 'la-tachometer-alt',
                'childrens' => [
                    'index' => [
                        'icon' => 'la-home',
                        'label' => __( 'Home' ),
                        'permissions' => [ 'read.dashboard' ],
                        'href' => ns()->url( '/dashboard' ),
                    ],
                ],
            ],
            'pos' => [
                'label' => __( 'POS' ),
                'icon' => 'la-cash-register',
                'permissions' => [ 'nexopos.create.orders' ],
                'href' => ns()->url( '/dashboard/pos' ),
            ],
            'orders' => [
                'label' => __( 'Orders' ),
                'permissions' => [ 'nexopos.read.orders' ],
                'icon' => 'la-list-ol',
                'childrens' => [
                    'order-list' => [
                        'icon' => 'la-receipt',
                        'label' => __( 'Orders List' ),
                        'href' => ns()->url( '/dashboard/orders' ),
                        'permissions' => [ 'nexopos.read.orders' ],
                    ],
                    'payment-type' => [
                        'icon' => 'la-wallet', 
                        'label' => __( 'Payment Types' ),
                        'href' => ns()->url( '/dashboard/orders/payments-types' ),
                        'permissions' => [ 'nexopos.manage-payments-types' ],
                    ],
                ],
            ],
            'medias' => [
                'label' => __( 'Medias' ),
                'permissions' => [ 'nexopos.upload.medias', 'nexopos.see.medias' ],
                'icon' => 'la-photo-video',
                'href' => ns()->url( '/dashboard/medias' ),
            ],
            'customers' => [
                'label' => __( 'Customers' ),
                'permissions' => [
                    'nexopos.read.customers',
                    'nexopos.create.customers',
                    'nexopos.read.customers-groups',
                    'nexopos.create.customers-groups',
                    'nexopos.import.customers',
                    'nexopos.read.rewards',
                    'nexopos.create.rewards',
                    'nexopos.read.coupons',
                    'nexopos.create.coupons',
                ],
                'icon' => 'la-user-friends',
                'childrens' => [
                    'customers' => [
                        'icon' => 'la-list',
                        'label' => __( 'List' ),
                        'permissions' => [ 'nexopos.read.customers' ],
                        'href' => ns()->url( '/dashboard/customers' ),
                    ],
                    'create-customer' => [
                        'icon' => 'la-user-plus',
                        'label' => __( 'Create Customer' ),
                        'permissions' => [ 'nexopos.create.customers' ],
                        'href' => ns()->url( '/dashboard/customers/create' ),
                    ],
                    'customers-groups' => [
                        'icon' => 'la-users-cog',
                        'label' => __( 'Customers Groups' ),
                        'permissions' => [ 'nexopos.read.customers-groups' ],
                        'href' => ns()->url( '/dashboard/customers/groups' ),
                    ],
                    'create-customers-group' => [
                        'icon' => 'la-user-plus',
                        'label' => __( 'Create Group' ),
                        'permissions' => [ 'nexopos.create.customers-groups' ],
                        'href' => ns()->url( '/dashboard/customers/groups/create' ),
                    ],
                    'list-reward-system' => [
                        'icon' => 'la-gift',
                        'label' => __( 'Reward Systems' ),
                        'permissions' => [ 'nexopos.read.rewards' ],
                        'href' => ns()->url( '/dashboard/customers/rewards-system' ),
                    ],
                    'create-reward-system' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Reward' ),
                        'permissions' => [ 'nexopos.create.rewards' ],
                        'href' => ns()->url( '/dashboard/customers/rewards-system/create' ),
                    ],
                    'list-coupons' => [
                        'icon' => 'la-ticket-alt',
                        'label' => __( 'List Coupons' ),
                        'permissions' => [ 'nexopos.read.coupons' ],
                        'href' => ns()->url( '/dashboard/customers/coupons' ),
                    ],
                    'create-coupons' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Coupon' ),
                        'permissions' => [ 'nexopos.create.coupons' ],
                        'href' => ns()->url( '/dashboard/customers/coupons/create' ),
                    ],
                ],
            ],
            'providers' => [
                'label' => __( 'Providers' ),
                'icon' => 'la-user-tie',
                'permissions' => [
                    'nexopos.read.providers',
                    'nexopos.create.providers',
                ],
                'childrens' => [
                    'providers' => [
                        'icon' => 'la-list',
                        'label' => __( 'List' ),
                        'permissions' => [ 'nexopos.read.providers' ],
                        'href' => ns()->url( '/dashboard/providers' ),
                    ],
                    'create-provider' => [
                        'icon' => 'la-user-plus',
                        'label' => __( 'Create A Provider' ),
                        'permissions' => [ 'nexopos.create.providers' ],
                        'href' => ns()->url( '/dashboard/providers/create' ),
                    ],
                ],
            ],
            'accounting' => [
                'label' => __( 'Accounting' ),
                'icon' => 'la-stream',
                'permissions' => [
                    'nexopos.read.transactions',
                    'nexopos.create.transactions',
                    'nexopos.read.transactions-account',
                    'nexopos.create.transactions-account',
                ],
                'childrens' => [
                    'transactions' => [
                        'icon' => 'la-exchange-alt',
                        'label' => __( 'Expenses' ),
                        'permissions' => [ 'nexopos.read.transactions' ],
                        'href' => ns()->url( '/dashboard/accounting/transactions' ),
                    ],
                    'create-transaction' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Expense' ),
                        'permissions' => [ 'nexopos.create.transactions' ],
                        'href' => ns()->url( '/dashboard/accounting/transactions/create' ),
                    ],
                    'transactions-history' => [
                        'icon' => 'la-history',
                        'label' => __( 'Transaction History' ),
                        'permissions' => [ 'nexopos.read.transactions-history' ],
                        'href' => ns()->url( '/dashboard/accounting/transactions/history' ),
                    ],
                    'transacations-rules' => [
                        'icon' => 'la-gavel',
                        'label' => __( 'Rules' ),
                        'permissions' => [ 'nexopos.create.transactions' ],
                        'href' => ns()->url( '/dashboard/accounting/rules' ),
                    ],
                    'transactions-account' => [
                        'icon' => 'la-wallet',
                        'label' => __( 'Accounts' ),
                        'permissions' => [ 'nexopos.read.transactions-account' ],
                        'href' => ns()->url( '/dashboard/accounting/accounts' ),
                    ],
                    'create-transactions-account' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Account' ),
                        'permissions' => [ 'nexopos.create.transactions-account' ],
                        'href' => ns()->url( '/dashboard/accounting/accounts/create' ),
                    ],
                ],
            ],
            'inventory' => [
                'label' => __( 'Inventory' ),
                'icon' => 'la-boxes',
                'permissions' => [
                    'nexopos.read.products',
                    'nexopos.create.products',
                    'nexopos.read.categories',
                    'nexopos.create.categories',
                    'nexopos.read.products-units',
                    'nexopos.create.products-units',
                    'nexopos.read.products-units',
                    'nexopos.create.products-units',
                    'nexopos.make.products-adjustments',
                ],
                'childrens' => [
                    'products' => [
                        'icon' => 'la-box',
                        'label' => __( 'Products' ),
                        'permissions' => [ 'nexopos.read.products' ],
                        'href' => ns()->url( '/dashboard/products' ),
                    ],
                    'create-products' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Product' ),
                        'permissions' => [ 'nexopos.create.products' ],
                        'href' => ns()->url( '/dashboard/products/create' ),
                    ],
                    'labels-printing' => [
                        'icon' => 'la-print',
                        'label' => __( 'Print Labels' ),
                        'href' => ns()->url( '/dashboard/products/print-labels' ),
                        'permissions' => [ 'nexopos.create.products-labels' ],
                    ],
                    'categories' => [
                        'icon' => 'la-th-list',
                        'label' => __( 'Categories' ),
                        'permissions' => [ 'nexopos.read.categories' ],
                        'href' => ns()->url( '/dashboard/products/categories' ),
                    ],
                    'create-categories' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Category' ),
                        'permissions' => [ 'nexopos.create.categories' ],
                        'href' => ns()->url( '/dashboard/products/categories/create' ),
                    ],
                    'units' => [
                        'icon' => 'la-ruler',
                        'label' => __( 'Units' ),
                        'permissions' => [ 'nexopos.read.products-units' ],
                        'href' => ns()->url( '/dashboard/units' ),
                    ],
                    'create-units' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Unit' ),
                        'permissions' => [ 'nexopos.create.products-units' ],
                        'href' => ns()->url( '/dashboard/units/create' ),
                    ],
                    'unit-groups' => [
                        'icon' => 'la-object-group',
                        'label' => __( 'Unit Groups' ),
                        'permissions' => [ 'nexopos.read.products-units' ],
                        'href' => ns()->url( '/dashboard/units/groups' ),
                    ],
                    'create-unit-groups' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Unit Groups' ),
                        'permissions' => [ 'nexopos.create.products-units' ],
                        'href' => ns()->url( '/dashboard/units/groups/create' ),
                    ],
                    'stock-adjustment' => [
                        'icon' => 'la-warehouse',
                        'label' => __( 'Stock Adjustment' ),
                        'permissions' => [ 'nexopos.make.products-adjustments' ],
                        'href' => ns()->url( '/dashboard/products/stock-adjustment' ),
                    ],
                    'product-history' => [
                        'icon' => 'la-history',
                        'label' => __( 'Stock Flow Records' ),
                        'permissions' => [ 'nexopos.read.products' ],
                        'href' => ns()->url( '/dashboard/products/stock-flow-records' ),
                    ],
                ],
            ],
            'taxes' => [
                'label' => __( 'Taxes' ),
                'icon' => 'la-balance-scale-left',
                'permissions' => [
                    'nexopos.create.taxes',
                    'nexopos.read.taxes',
                    'nexopos.update.taxes',
                    'nexopos.delete.taxes',
                ],
                'childrens' => [
                    'taxes-groups' => [
                        'icon' => 'la-file-invoice-dollar',
                        'label' => __( 'Taxes Groups' ),
                        'permissions' => [ 'nexopos.read.taxes' ],
                        'href' => ns()->url( '/dashboard/taxes/groups' ),
                    ],
                    'create-taxes-group' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Tax Groups' ),
                        'permissions' => [ 'nexopos.create.taxes' ],
                        'href' => ns()->url( '/dashboard/taxes/groups/create' ),
                    ],
                    'taxes' => [
                        'icon' => 'la-file-invoice-dollar',
                        'label' => __( 'Taxes' ),
                        'permissions' => [ 'nexopos.read.taxes' ],
                        'href' => ns()->url( '/dashboard/taxes' ),
                    ],
                    'create-tax' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Tax' ),
                        'permissions' => [ 'nexopos.create.taxes' ],
                        'href' => ns()->url( '/dashboard/taxes/create' ),
                    ],
                ],
            ],
            'modules' => [
                'label' => __( 'Modules' ),
                'icon' => 'la-plug',
                'permissions' => [ 'manage.modules' ],
                'childrens' => [
                    'modules' => [
                        'icon' => 'la-th-large',
                        'label' => __( 'List' ),
                        'href' => ns()->url( '/dashboard/modules' ),
                    ],
                    'upload-module' => [
                        'icon' => 'la-upload',
                        'label' => __( 'Upload Module' ),
                        'href' => ns()->url( '/dashboard/modules/upload' ),
                    ],
                ],
            ],
            'users' => [
                'label' => __( 'Users' ),
                'icon' => 'la-users',
                'childrens' => [
                    'profile' => [
                        'icon' => 'la-user',
                        'label' => __( 'My Profile' ),
                        'permissions' => [ 'manage.profile' ],
                        'href' => ns()->url( '/dashboard/users/profile' ),
                    ],
                    'users' => [
                        'icon' => 'la-user-circle',
                        'label' => __( 'Users List' ),
                        'permissions' => [ 'read.users' ],
                        'href' => ns()->url( '/dashboard/users' ),
                    ],
                    'create-user' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create User' ),
                        'permissions' => [ 'create.users' ],
                        'href' => ns()->url( '/dashboard/users/create' ),
                    ],
                ],
            ],
            'roles' => [
                'label' => __( 'Roles' ),
                'icon' => 'la-shield-alt',
                'permissions' => [ 'read.roles', 'create.roles', 'update.roles' ],
                'childrens' => [
                    'all-roles' => [
                        'icon' => 'la-user-tag',
                        'label' => __( 'Roles' ),
                        'permissions' => [ 'read.roles' ],
                        'href' => ns()->url( '/dashboard/users/roles' ),
                    ],
                    'create-role' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'Create Roles' ),
                        'permissions' => [ 'create.roles' ],
                        'href' => ns()->url( '/dashboard/users/roles/create' ),
                    ],
                    'permissions' => [
                        'icon' => 'la-lock',
                        'label' => __( 'Permissions Manager' ),
                        'permissions' => [ 'update.roles' ],
                        'href' => ns()->url( '/dashboard/users/roles/permissions-manager' ),
                    ],
                ],
            ],
            'procurements' => [
                'label' => __( 'Procurements' ),
                'icon' => 'la-truck-loading',
                'permissions' => [ 'nexopos.read.procurements', 'nexopos.create.procurements' ],
                'childrens' => [
                    'procurements' => [
                        'icon' => 'la-file-invoice',
                        'label' => __( 'Procurements List' ),
                        'permissions' => [ 'nexopos.read.procurements' ],
                        'href' => ns()->url( '/dashboard/procurements' ),
                    ],
                    'procurements-create' => [
                        'icon' => 'la-plus-circle',
                        'label' => __( 'New Procurement' ),
                        'permissions' => [ 'nexopos.create.procurements' ],
                        'href' => ns()->url( '/dashboard/procurements/create' ),
                    ],
                    'procurements-products' => [
                        'icon' => 'la-box',
                        'label' => __( 'Products' ),
                        'permissions' => [ 'nexopos.update.procurements' ],
                        'href' => ns()->url( '/dashboard/procurements/products' ),
                    ],
                ],
            ],
            'reports' => [
                'label' => __( 'Reports' ),
                'icon' => 'la-chart-pie',
                'permissions' => [
                    'nexopos.reports.sales',
                    'nexopos.reports.best_sales',
                    'nexopos.reports.transactions',
                    'nexopos.reports.yearly',
                    'nexopos.reports.customers',
                    'nexopos.reports.inventory',
                    'nexopos.reports.payment-types',
                ],
                'childrens' => [
                    'sales' => [
                        'icon' => 'la-chart-line',
                        'label' => __( 'Sale Report' ),
                        'permissions' => [ 'nexopos.reports.sales' ],
                        'href' => ns()->url( '/dashboard/reports/sales' ),
                    ],
                    'products-report' => [
                        'icon' => 'la-clipboard-list',
                        'label' => __( 'Sales Progress' ),
                        'permissions' => [ 'nexopos.reports.products-report' ],
                        'href' => ns()->url( '/dashboard/reports/sales-progress' ),
                    ],
                    'customers-statement' => [
                        'label' => __( 'Customers Statement' ),
                        'permissions' => [ 'nexopos.reports.customers-statement' ],
                        'href' => ns()->url( '/dashboard/reports/customers-statement' ),
                    ],
                    'low-stock' => [
                        'icon' => 'la-clipboard-list',
                        'label' => __( 'Stock Report' ),
                        'permissions' => [ 'nexopos.reports.low-stock' ],
                        'href' => ns()->url( '/dashboard/reports/low-stock' ),
                    ],
                    'stock-history' => [
                        'icon' => 'la-clipboard-list',
                        'label' => __( 'Stock History' ),
                        'permissions' => [ 'nexopos.reports.stock-history' ],
                        'href' => ns()->url( '/dashboard/reports/stock-history' ),
                    ],
                    'sold-stock' => [
                        'icon' => 'la-clipboard-list',
                        'label' => __( 'Sold Stock' ),
                        'href' => ns()->url( '/dashboard/reports/sold-stock' ),
                    ],
                    'profit' => [
                        'icon' => 'la-clipboard-list',
                        'label' => __( 'Incomes & Loosses' ),
                        'href' => ns()->url( '/dashboard/reports/profit' ),
                    ],
                    'transactions' => [
                        'icon' => 'la-clipboard-list',
                        'label' => __( 'Transactions' ),
                        'permissions' => [ 'nexopos.reports.transactions' ],
                        'href' => ns()->url( '/dashboard/reports/transactions' ),
                    ],
                    'annulal-sales' => [
                        'icon' => 'la-clipboard-list',
                        'label' => __( 'Annual Report' ),
                        'permissions' => [ 'nexopos.reports.yearly' ],
                        'href' => ns()->url( '/dashboard/reports/annual-report' ),
                    ],
                    'payment-types' => [
                        'icon' => 'la-clipboard-list',
                        'label' => __( 'Sales By Payments' ),
                        'permissions' => [ 'nexopos.reports.payment-types' ],
                        'href' => ns()->url( '/dashboard/reports/payment-types' ),
                    ],
                ],
            ],
            'settings' => [
                'label' => __( 'Settings' ),
                'icon' => 'la-cogs',
                'permissions' => [ 'manage.options' ],
                'childrens' => [
                    'general' => [
                        'icon' => 'la-th-large',
                        'label' => __( 'General' ),
                        'href' => ns()->url( '/dashboard/settings/general' ),
                    ],
                    'pos' => [
                        'icon' => 'la-cash-register',
                        'label' => __( 'POS' ),
                        'href' => ns()->url( '/dashboard/settings/pos' ),
                    ],
                    'customers' => [
                        'icon' => 'la-user',
                        'label' => __( 'Customers' ),
                        'href' => ns()->url( '/dashboard/settings/customers' ),
                    ],
                    'orders' => [
                        'icon' => 'la-shopping-basket',
                        'label' => __( 'Orders' ),
                        'href' => ns()->url( '/dashboard/settings/orders' ),
                    ],
                    'accounting' => [
                        'icon' => 'la-calculator',
                        'label' => __( 'Accounting' ),
                        'href' => ns()->url( '/dashboard/settings/accounting' ),
                    ],
                    'reports' => [
                        'icon' => 'la-file-alt',
                        'label' => __( 'Reports' ),
                        'href' => ns()->url( '/dashboard/settings/reports' ),
                    ],
                    'invoices' => [
                        'icon' => 'la-file-invoice',
                        'label' => __( 'Invoices' ),
                        'href' => ns()->url( '/dashboard/settings/invoices' ),
                    ],
                    'workers' => [
                        'icon' => 'la-user',
                        'label' => __( 'Workers' ),
                        'href' => ns()->url( '/dashboard/settings/workers' ),
                    ],
                    'reset' => [
                        'icon' => 'la-sync',
                        'label' => __( 'Reset' ),
                        'href' => ns()->url( '/dashboard/settings/reset' ),
                    ],
                    'about' => [
                        'icon' => 'la-info-circle',
                        'label' => __( 'About' ),
                        'href' => ns()->url( '/dashboard/settings/about' ),
                    ],
                ],
            ],
        ];
    }

    /**
     * returns the list of available menus
     *
     * @return array of menus
     */
    public function getMenus()
    {
        $this->buildMenus();
        $this->menus = Hook::filter( 'ns-dashboard-menus', $this->menus );
        $this->toggleActive();

        return collect( $this->menus )->filter( function ( $menu ) {
            return ! isset( $menu[ 'permissions' ] ) || Gate::any( $menu[ 'permissions' ] );
        } )->map( function ( $menu ) {
            $menu[ 'childrens' ] = collect( $menu[ 'childrens' ] ?? [] )->filter( function ( $submenu ) {
                return ! isset( $submenu[ 'permissions' ] ) || Gate::any( $submenu[ 'permissions' ] );
            } )->toArray();

            return $menu;
        } );
    }

    /**
     * Will make sure active menu
     * is toggled
     *
     * @return void
     */
    public function toggleActive()
    {
        foreach ( $this->menus as $identifier => &$menu ) {
            if ( isset( $menu[ 'href' ] ) && $menu[ 'href' ] === url()->current() ) {
                $menu[ 'toggled' ] = true;
            }

            if ( isset( $menu[ 'childrens' ] ) ) {
                foreach ( $menu[ 'childrens' ] as $subidentifier => &$submenu ) {
                    if ( $submenu[ 'href' ] === url()->current() ) {
                        $menu[ 'toggled' ] = true;
                        $submenu[ 'active' ] = true;
                    }
                }
            }
        }
    }
}
