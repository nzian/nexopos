@extends( 'layout.dashboard' )

@section( 'layout.dashboard.body' )
<div class="flex-auto flex flex-col">
    @include( Hook::filter( 'ns-dashboard-header', '../common/dashboard-header' ) )
    <div class="flex-auto flex flex-col" id="dashboard-content">
        <div class="px-4">
            @include( '../common/dashboard/title' )
        </div>
        <smp-vendor-sales-report inline-template v-cloak>
            <div id="report-section" class="px-4">
                <div class="flex -mx-2">
                    <div class="px-2">
                        <ns-date-time-picker :date="startDate" @change="setStartDate( $event )"></ns-date-time-picker>
                    </div>
                    <div class="px-2">
                        <ns-date-time-picker :date="endDate" @change="setEndDate( $event )"></ns-date-time-picker>
                    </div>
                    <div class="px-2">
                        <button @click="loadVendorReport()" class="rounded flex justify-between bg-input-button shadow py-1 items-center text-primary px-2">
                            <i class="las la-sync-alt text-xl"></i>
                            <span class="pl-2">{{ __m( 'Load' ) }}</span>
                        </button>
                    </div>
                </div>
                <div class="flex -mx-2">
                    <div class="px-2">
                        <button @click="printVendorSaleReport()" class="rounded flex justify-between bg-input-button shadow py-1 items-center text-primary px-2">
                            <i class="las la-print text-xl"></i>
                            <span class="pl-2">{{ __m( 'Print' ) }}</span>
                        </button>
                    </div>
                </div>
                <div id="sale-report" class="anim-duration-500 fade-in-entrance">
                    <div class="flex w-full">
                        <div class="my-4 flex justify-between w-full">
                            <div class="text-secondary">
                                <ul>
                                    <li class="pb-1 border-b border-dashed">{{ sprintf( __m( 'Date : %s' ), ns()->date->getNowFormatted() ) }}</li>
                                    <li class="pb-1 border-b border-dashed">{{ __m( 'Document : Vendor Monthly Sale Report' ) }}</li>
                                    
                                </ul>
                            </div>
                            <div>
                                <img class="w-72" src="{{ ns()->option->get( 'ns_store_rectangle_logo' ) }}" alt="{{ ns()->option->get( 'ns_store_name' ) }}">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="-mx-4 flex md:flex-row flex-col">
                            <div class="w-full md:w-1/2 px-4">
                                <div class="shadow rounded my-4 ns-box">
                                    <div class="border-b ns-box-body">
                                        <table class="table ns-table w-full">
                                            <tbody class="text-primary">
                                                <tr class="">
                                                    <td width="200" class="font-semibold p-2 border text-left bg-success-secondary border-success-secondary text-white">{{ __m( 'Total vendor' ) }}</td>
                                                    <td class="p-2 border text-right border-success-primary">@{{ summary.total_vendor }}</td>
                                                </tr>
                                                <tr class="">
                                                    <td width="200" class="font-semibold p-2 border text-left bg-info-secondary border-info-primary text-white">{{ __m( 'Sub Total' ) }}</td>
                                                    <td class="p-2 border text-right border-info-primary">@{{ summary.subtotal | currency }}</td>
                                                </tr>
                                                <tr class="">
                                                    <td width="200" class="font-semibold p-2 border text-left bg-error-secondary border-error-primary text-white">{{ __m( 'Sales Discounts' ) }}</td>
                                                    <td class="p-2 border text-right border-error-primary">@{{ summary.sales_commission | currency }}</td>
                                                </tr>
                                                <tr class="">
                                                    <td width="200" class="font-semibold p-2 border text-left bg-error-secondary border-error-primary text-white">{{ __m( 'Sales Taxes' ) }}</td>
                                                    <td class="p-2 border text-right border-error-primary">@{{ summary.monthly_rent | currency }}</td>
                                                </tr>
                                                <tr class="">
                                                    <td width="200" class="font-semibold p-2 border text-left text-white">{{ __m( 'Shipping' ) }}</td>
                                                    <td class="p-2 border text-right border-success-primary">@{{ summary.total_payable | currency  }}</td>
                                                </tr>
                                            
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full md:w-1/2 px-4">
                            </div>
                        </div>
                    </div>
                    <div class="bg-box-background shadow rounded my-4"">
                        <div class="border-b border-box-edge">
                            <table class="table ns-table w-full">
                                <thead class="text-primary">
                                    <tr>
                                        <th class="border p-2 text-left">{{ __m( 'Vendor Id' ) }}</th>
                                        <th width="150" class="border p-2">{{ __m( 'Total Sales' ) }}</th>
                                        <th width="150" class="border p-2">{{ __m( 'Sales Commission' ) }}</th>
                                        <th width="150" class="border p-2">{{ __m( 'Monthly Rent' ) }}</th>
                                        <th width="150" class="border p-2">{{ __m( 'Total Payable' ) }}</th>
                                    </tr>
                                </thead>
                                <tbody class="text-primary">
                                    <tr v-for="vendor of result" :key="vendor.id">
                                        <td class="p-2 border">@{{ vendor.vendor_id }}</td>
                                        <td class="p-2 border text-right">@{{ vendor.total_sales | currency }}</td>
                                        <td class="p-2 border text-right">@{{ vendor.commission | currency }}</td>
                                        <td class="p-2 border text-right">@{{ vendor.monthly_rent | currency }}</td>
                                        <td class="p-2 border text-right">@{{ vendor.(total_sales - vendor.commission - vendor.monthly_rent) | currency }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="text-primary font-semibold">
                                    <tr>
                                        <td class="p-2 border text-primary"></td>
                                        <td class="p-2 border text-right text-primary">@{{ computeTotal( result, 'total_sales' ) | currency }}</td>
                                        <td class="p-2 border text-right text-primary">@{{ computeTotal( result, 'commission' ) | currency }}</td>
                                        <td class="p-2 border text-right text-primary">@{{ computeTotal( result, 'monthly_rent' ) | currency }}</td>
                                        <td class="p-2 border text-right text-primary">@{{ (computePayableTotal( result, 'total_price' ) - computeTotal( result, 'commission' ) - computeTotal( result, 'monthly_rent' ))  | currency }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </smp-vendor-sales-report>
    </div>
</div>
@endsection