<?php

namespace Modules\SmpVendor\Http\Controllers;

use App\Http\Controllers\DashboardController;

use App\Services\OrdersService;
use Modules\SmpVendor\Services\VendorReportsService;
use Illuminate\Http\Request;

class VendorReportsController extends DashboardController
{
    public function __construct(
        protected OrdersService $ordersService,
        protected VendorReportsService $reportService
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
}
