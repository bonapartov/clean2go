<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BookingDataTable;
use App\DataTables\cashBookingDataTable;
use Exception;
use Illuminate\Http\Request;
use App\Models\PaymentTransactions;
use App\Http\Controllers\Controller;
use App\DataTables\TransactionsDataTable;
use App\Repositories\Backend\TaxRepository;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exceptions\ExceptionHandler;
use App\Exports\BookingFilterExport;
use App\Exports\CashBookingFilterExport;
use App\Exports\TransactionsFilterExport;
use App\Models\User;

class TransactionController extends Controller
{
    public $repository;

    public $user;

    public function __construct(TaxRepository $repository)
    {
        $this->user = new User();
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(TransactionsDataTable $dataTable)
    {
        return $dataTable->render('backend.transaction.index');
    }

    public function cashBookings(cashBookingDataTable $dataTable)
    {
        return $dataTable->render('backend.transaction.cash-bookings',[
             'consumers' => $this->getConsumers() ,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentTransactions $tax) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentTransactions $tax) {}

    public function status(Request $request, $id) {}

    public function deleteRows(Request $request) {}

    public function export(Request $request)
    {
        try {

            $format = $request->get('format', 'csv');
            switch ($format) {
                case 'excel':
                    return $this->exportExcel();
                case 'csv':
                default:
                    return $this->exportCsv();
            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public  function exportExcel()
    {
        return Excel::download(new TransactionsExport, 'transactions.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new TransactionsExport, 'transactions.csv');
    }

    public function transactionsFilterExport(Request $request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new TransactionsFilterExport, 'add-on-services.csv');
            }
            return Excel::download(new TransactionsFilterExport, 'add-on-services.xlsx');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    protected function getConsumers()
    {
       return User::role('user')->where('status', true)->get();
    }

    public function cashBookingsExport(Request $request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new CashBookingFilterExport, 'CashBooking.csv');
            }
            return Excel::download(new CashBookingFilterExport, 'CashBooking.xlsx');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
