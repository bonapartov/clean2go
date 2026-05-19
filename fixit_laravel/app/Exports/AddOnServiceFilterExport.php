<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class AddOnServiceFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $serviceIds = request()->services ? explode(',', request()->services) : [];
        $status = request()->status;
        
        $services = Service::query()->whereNotNull('parent_id');

        if ($startDate && $endDate) {
            $services->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
        }

        if ($serviceIds) {
            $services->whereIn('id', $serviceIds);        
        }

        if ($status !== null && $status !== '') {
            $services->where('status', $status);
        }

        return $services->get();
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'title',
            'price',
            'parent_id',
            'user_id',
            'image'
        ];
    }

    public function map($service): array
    {

        return [
            $service->title,
            $service->price,
            $service->parent_id,
            $service?->user_id,
            $service?->media?->first()->original_url
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
            'title',
            'price',
            'parent_id',
            'user_id',
            'image'
        ];
    }


}
