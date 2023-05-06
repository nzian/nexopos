<?php

namespace Modules\SmpVendor\Http\Controllers;

use App\Http\Controllers\DashboardController;
use App\Jobs\ComputeYearlyReportJob;
use App\Models\AccountType;
use App\Models\CashFlow;
use App\Models\Customer;
use App\Services\OrdersService;
use Modules\SmpVendor\Services\VendorReportService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorReportsController extends DashboardController
{
    public function __construct(
        protected OrdersService $ordersService,
        protected VendorReportService $reportService
    ) {
        parent::__construct();
    }

    public function showVendorMonthlyStatement()
    {
        return $this->view('SmpVendor::dashboard.report.vendor-report', [
            'title' => __('Vendor Monthly Report'),
            'description' => __('Provides an overview of vendors sales during a specific period'),
        ]);
    }

    /**
     * get sales based on a specific time range
     *
     * @param Request $request
     * @return array
     */
    public function getVendorReport(Request $request)
    {
        return $this->reportService
            ->getVendorSaleReport(
                $request->input('startDate'),
                $request->input('endDate')
            );
    }

    /**
     * get sold stock on a specific time range
     *
     * @param Request $request
     * @return array
     */
    public function getSoldStockReport(Request $request)
    {
        $orders = $this->ordersService
            ->getSoldStock(
                $request->input('startDate'),
                $request->input('endDate')
            );

        return collect($orders)->mapToGroups(function ($product) {
            return [
                $product->product_id . '-' . $product->unit_id => $product,
            ];
        })->map(function ($groups) {
            return [
                'name' => $groups->first()->name,
                'unit_name' => $groups->first()->unit_name,
                'mode' => $groups->first()->mode,
                'unit_price' => $groups->sum('unit_price'),
                'quantity' => $groups->sum('quantity'),
                'total_price' => $groups->sum('total_price'),
                'tax_value' => $groups->sum('tax_value'),
            ];
        })->values();
    }

    public function getCashFlow(Request $request)
    {
        $rangeStarts = Carbon::parse($request->input('startDate'))
            ->toDateTimeString();

        $rangeEnds = Carbon::parse($request->input('endDate'))
            ->toDateTimeString();

        $entries = $this->reportService->getFromTimeRange($rangeStarts, $rangeEnds);
        $total = $entries->count() > 0 ? $entries->first()->toArray() : [];
        $creditCashFlow = AccountType::where('operation', CashFlow::OPERATION_CREDIT)->with([
            'cashFlowHistories' => function ($query) use ($rangeStarts, $rangeEnds) {
                $query->where('created_at', '>=', $rangeStarts)
                    ->where('created_at', '<=', $rangeEnds);
            },
        ])
        ->get()
        ->map(function ($accountType) {
            $accountType->total = $accountType->cashFlowHistories->count() > 0 ? $accountType->cashFlowHistories->sum('value') : 0;

            return $accountType;
        });

        $debitCashFlow = AccountType::where('operation', CashFlow::OPERATION_DEBIT)->with([
            'cashFlowHistories' => function ($query) use ($rangeStarts, $rangeEnds) {
                $query->where('created_at', '>=', $rangeStarts)
                    ->where('created_at', '<=', $rangeEnds);
            },
        ])
        ->get()
        ->map(function ($accountType) {
            $accountType->total = $accountType->cashFlowHistories->count() > 0 ? $accountType->cashFlowHistories->sum('value') : 0;

            return $accountType;
        });

        return [
            'summary' => collect($total)->mapWithKeys(function ($value, $key) use ($entries) {
                if (! in_array($key, [ 'range_starts', 'range_ends', 'day_of_year' ])) {
                    return [ $key => $entries->sum($key) ];
                }

                return [ $key => $value ];
            }),

            'total_debit' => collect([
                $debitCashFlow->sum('total'),
            ])->sum(),
            'total_credit' => collect([
                $creditCashFlow->sum('total'),
            ])->sum(),

            'creditCashFlow' => $creditCashFlow,
            'debitCashFlow' => $debitCashFlow,
        ];
    }

    /**
     * get sold stock on a specific time range
     *
     * @param Request $request
     *
     * @todo review
     *
     * @return array
     */
    public function getProfit(Request $request)
    {
        $orders = $this->ordersService
            ->getSoldStock(
                $request->input('startDate'),
                $request->input('endDate')
            );

        return $orders;

        return collect($orders)->mapToGroups(function ($product) {
            return [
                $product->product_id . '-' . $product->unit_id => $product,
            ];
        })->map(function ($groups) {
            return [
                'name' => $groups->first()->name,
                'unit_name' => $groups->first()->unit_name,
                'mode' => $groups->first()->mode,
                'unit_price' => $groups->sum('unit_price'),
                'total_purchase_price' => $groups->sum('total_purchase_price'),
                'quantity' => $groups->sum('quantity'),
                'total_price' => $groups->sum('total_price'),
                'tax_value' => $groups->sum('tax_value'),
            ];
        })->values();
    }

    public function getAnnualReport(Request $request)
    {
        return $this->reportService->getYearReportFor($request->input('year'));
    }

    public function annualReport(Request $request)
    {
        return $this->view('pages.dashboard.reports.annual-report', [
            'title' => __('Annual Report'),
            'description' => __('Provides an overview over the sales during a specific period'),
        ]);
    }

    public function salesByPaymentTypes(Request $request)
    {
        return $this->view('pages.dashboard.reports.payment-types', [
            'title' => __('Sales By Payment Types'),
            'description' => __('Provide a report of the sales by payment types, for a specific period.'),
        ]);
    }

    public function getPaymentTypes(Request $request)
    {
        return $this->ordersService->getPaymentTypesReport(
            $request->input('startDate'),
            $request->input('endDate'),
        );
    }

    public function computeReport(Request $request, $type)
    {
        if ($type === 'yearly') {
            ComputeYearlyReportJob::dispatch($request->input('year'));

            return [
                'stauts' => 'success',
                'message' => __('The report will be computed for the current year.'),
            ];
        }

        throw new Exception(__('Unknown report to refresh.'));
    }

    public function getProductsReport(Request $request)
    {
        return $this->reportService->getProductSalesDiff(
            $request->input('startDate'),
            $request->input('endDate'),
            $request->input('sort')
        );
    }

    public function getMyReport()
    {
        return $this->reportService->getCashierDashboard(Auth::id());
    }

    public function getLowStock()
    {
        return $this->reportService->getLowStockProducts();
    }

    public function getStockReport()
    {
        return $this->reportService->getStockReport();
    }

    public function showCustomerStatement()
    {
        return $this->view('pages.dashboard.reports.customers-statement', [
            'title' => __('Customers Statement'),
            'description' => __('Display the complete customer statement.'),
        ]);
    }

    public function getCustomerStatement(Customer $customer, Request $request)
    {
        return $this->reportService->getCustomerStatement(
            customer: $customer,
            rangeStarts: $request->input('rangeStarts'),
            rangeEnds: $request->input('rangeEnds')
        );
    }
}