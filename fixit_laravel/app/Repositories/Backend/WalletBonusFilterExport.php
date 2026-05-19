<?php

namespace App\Exports;

use App\Models\WalletBonus;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class WalletBonusFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = WalletBonus::query();

        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $status = request()->status;
        $price = request()->price;
        $typeIds = request()->type;

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        if ($price) {
            [$min, $max] = explode(';', $price);
            $query->whereBetween('bonus', [(float) $min, (float) $max]);
        }

        if ($typeIds) { 
            $query->where('type', $typeIds);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
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
            'id',
            'name',
            'description',
            'type',
            'bonus',
            'min_top_up_amount',
            'max_bonus',
            'status',
            'created_by_id',
            'created_at',
            'updated_at',
            'deleted_at',

        ];
    }

    public function map($walletBonus): array
    {
        return [
                $walletBonus->id ?? 'N/A',
                $walletBonus->name ?? 'N/A',
                $walletBonus->description ?? 'N/A',
                $walletBonus->type ?? 'N/A',
                $walletBonus->bonus ?? 'N/A',
                $walletBonus->min_top_up_amount  ?? 'N/A',
                $walletBonus->max_bonus ?? 'N/A',
                $walletBonus->status ?? 'N/A',
                $walletBonus->created_by_id ?? 'N/A',
                $walletBonus->created_at ?? 'N/A',
                $walletBonus->updated_at ?? 'N/A',
                $walletBonus->deleted_at ?? 'N/A',

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
            'ID',
            'Name',
            'Description',
            'Type',
            'Bonus',
            'Min Top Up Amount',
            'Max Bonus',
            'Status',
            'Created By ID',
            'Created At',
            'Updated At',
            'Deleted At',
        ];
    }
}