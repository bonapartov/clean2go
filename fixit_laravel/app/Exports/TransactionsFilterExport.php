<?php

namespace App\Exports;

use App\Models\PaymentTransactions;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransactionsFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = PaymentTransactions::query()->latest('created_at');

        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $types = request()->types ? explode(',', request()->types) : [];
        $paymentStatuses = request()->payment_statuses ? explode(',', request()->payment_statuses) : [];
        $paymentMethods = request()->payment_methods ? explode(',', request()->payment_methods) : [];

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);
        }

        if ($paymentStatuses) {
            $query->whereIn('payment_status', $paymentStatuses);
        }

        if ($paymentMethods) {
            $query->whereIn('payment_method', $paymentMethods);
        }

        if ($types) {
            $query->whereIn('type', $types);
        }

        return $query->get();
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
           'item_id',
           'payment_method',
           'payment_status',
           'type',
           'amount',
           'transaction_id',                                            
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->item_id,
            $transaction->payment_method,
            $transaction->payment_status,
            $transaction->type,
            $transaction->amount,
            $transaction->transaction_id,
        ];
    }

     /**
     * Get the headings for the export file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Item Id',
            'Payment Method',
            'Payment Status',
            'Type',
            'Amount',
            'Transaction Id', 
        ];
    }
}
